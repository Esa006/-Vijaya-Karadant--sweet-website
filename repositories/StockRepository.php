<?php
declare(strict_types=1);
/**
 * Sweets Website
 * =============================================================
 * File: StockRepository.php
 * Description: Atomic stock management — source of truth for inventory
 * Author: Antigravity
 * Version: 1.0.0
 * =============================================================
 */

require_once __DIR__ . '/BaseRepository.php';

class StockRepository extends BaseRepository {

    // ── Thresholds ──────────────────────────────────────────────
    private const LOW_STOCK_THRESHOLD = 5;

    // ── Schema helpers ──────────────────────────────────────────

    private function usesInventoryTable(): bool {
        static $result = null;
        if ($result === null) {
            try {
                $r = $this->db->query("SHOW TABLES LIKE 'inventory'");
                $result = (bool)$r->fetchColumn();
            } catch (\Exception $e) {
                $result = false;
            }
        }
        return $result;
    }

    private function hasColumn(string $table, string $col): bool {
        try {
            $r = $this->db->query("SHOW COLUMNS FROM `{$table}` LIKE " . $this->db->quote($col));
            return (bool)$r->fetchColumn();
        } catch (\Exception $e) {
            return false;
        }
    }

    // ── Public API ──────────────────────────────────────────────

    /**
     * Return stock quantity for a product (reads from inventory or products table).
     */
    public function getRawStock(int $productId): int {
        if ($this->usesInventoryTable()) {
            $stmt = $this->db->prepare(
                "SELECT stock FROM inventory WHERE product_id = :pid LIMIT 1"
            );
            $stmt->execute([':pid' => $productId]);
            $val = $stmt->fetchColumn();
            if ($val !== false) {
                return max(0, (int)$val);
            }
        }

        foreach (['stock_quantity', 'stock_qty'] as $col) {
            if ($this->hasColumn('products', $col)) {
                $stmt = $this->db->prepare(
                    "SELECT `{$col}` FROM products WHERE id = :pid LIMIT 1"
                );
                $stmt->execute([':pid' => $productId]);
                $val = $stmt->fetchColumn();
                if ($val !== false) {
                    return max(0, (int)$val);
                }
            }
        }

        return 0;
    }

    /**
     * Compute stock_status string from raw quantity.
     * Never exposes raw logic to the frontend directly.
     */
    public static function computeStatus(int $qty): string {
        if ($qty <= 0)                        return 'out_of_stock';
        if ($qty <= self::LOW_STOCK_THRESHOLD) return 'low_stock';
        return 'in_stock';
    }

    /**
     * Return structured stock response (used by API endpoint).
     */
    public function getStockPayload(int $productId): array {
        $qty    = $this->getRawStock($productId);
        $status = self::computeStatus($qty);

        return [
            'product_id'   => $productId,
            'stock_status' => $status,
            'stock_quantity' => $qty,
        ];
    }

    /**
     * Atomically decrement stock by $qty.
     * Uses SELECT ... FOR UPDATE inside a transaction to prevent overselling.
     *
     * Returns true on success, false when out of stock or DB error.
     */
    public function decrementStock(int $productId, int $qty = 1): bool {
        $qty = max(1, $qty);

        try {
            $this->db->beginTransaction();

            if ($this->usesInventoryTable()) {
                // Lock the row
                $stmt = $this->db->prepare(
                    "SELECT stock FROM inventory
                     WHERE product_id = :pid
                     LIMIT 1
                     FOR UPDATE"
                );
                $stmt->execute([':pid' => $productId]);
                $current = (int)$stmt->fetchColumn();

                if ($current < $qty) {
                    $this->db->rollBack();
                    return false;
                }

                $upd = $this->db->prepare(
                    "UPDATE inventory
                     SET stock = stock - :qty
                     WHERE product_id = :pid AND stock >= :qty"
                );
                $upd->execute([':qty' => $qty, ':pid' => $productId]);
                $affected = $upd->rowCount();

            } else {
                $col = $this->hasColumn('products', 'stock_quantity')
                    ? 'stock_quantity'
                    : ($this->hasColumn('products', 'stock_qty') ? 'stock_qty' : null);

                if (!$col) {
                    $this->db->rollBack();
                    return false;
                }

                // Atomic conditional update — never goes below 0
                $upd = $this->db->prepare(
                    "UPDATE products
                     SET `{$col}` = `{$col}` - :qty
                     WHERE id = :pid AND `{$col}` >= :qty"
                );
                $upd->execute([':qty' => $qty, ':pid' => $productId]);
                $affected = $upd->rowCount();
            }

            if ($affected === 0) {
                $this->db->rollBack();
                return false;  // race condition caught — stock was insufficient
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[StockRepository] decrementStock error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate that all cart items still have sufficient stock.
     * Returns an array of product IDs that failed, or empty array if all OK.
     */
    public function validateCartStock(array $cartItems): array {
        $failed = [];
        foreach ($cartItems as $item) {
            $id  = (int)($item['id'] ?? 0);
            $qty = (int)($item['quantity'] ?? 1);
            if ($id <= 0) continue;
            if ($this->getRawStock($id) < $qty) {
                $failed[] = $id;
            }
        }
        return $failed;
    }

    /**
     * Save a "Notify Me" request for an out-of-stock product.
     */
    public function saveNotifyRequest(int $productId, string $email): bool {
        try {
            // Create table on first use (self-healing schema)
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS stock_notify (
                    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    product_id  INT UNSIGNED NOT NULL,
                    email       VARCHAR(255) NOT NULL,
                    notified    TINYINT(1) NOT NULL DEFAULT 0,
                    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_product_email (product_id, email)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO stock_notify (product_id, email)
                 VALUES (:pid, :email)"
            );
            $stmt->execute([':pid' => $productId, ':email' => $email]);
            return true;
        } catch (\Exception $e) {
            error_log('[StockRepository] saveNotifyRequest error: ' . $e->getMessage());
            return false;
        }
    }
}
