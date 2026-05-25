<?php
/**
 * Sweets Website
 * =============================================================
 * File: InventoryRepository.php
 * Description: Atomic data access layer for Product Inventory
 * Author: Antigravity - Senior Backend Engineer
 * Version: 2.1.0
 * =============================================================
 */

require_once 'BaseRepository.php';

class InventoryRepository extends BaseRepository {
    private array $tableCache = [];
    private array $columnCache = [];

    private function hasTable(string $table): bool {
        if (isset($this->tableCache[$table])) {
            return $this->tableCache[$table];
        }

        try {
            $stmt = $this->db->query("SHOW TABLES LIKE " . $this->db->quote($table));
            $exists = (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            $exists = false;
        }

        $this->tableCache[$table] = $exists;
        return $exists;
    }

    private function hasColumn(string $table, string $column): bool {
        $cacheKey = $table . '.' . $column;
        if (isset($this->columnCache[$cacheKey])) {
            return $this->columnCache[$cacheKey];
        }

        if (!$this->hasTable($table)) {
            $this->columnCache[$cacheKey] = false;
            return false;
        }

        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM {$table} LIKE " . $this->db->quote($column));
            $exists = (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            $exists = false;
        }

        $this->columnCache[$cacheKey] = $exists;
        return $exists;
    }

    /**
     * Get inventory record for a product (with fallback to products table)
     */
    public function getByProductId(int $productId): ?array {
        $inv = $this->fetchOne(
            "SELECT * FROM inventory WHERE product_id = :pid", 
            ['pid' => $productId]
        );
        
        if ($inv) {
            return $inv;
        }

        // Fallback to products table
        if ($this->hasColumn('products', 'stock_quantity')) {
            $prod = $this->fetchOne("SELECT stock_quantity as stock FROM products WHERE id = :pid", ['pid' => $productId]);
            if ($prod) {
                return ['product_id' => $productId, 'stock' => (int)$prod['stock'], 'reserved_stock' => 0];
            }
        } elseif ($this->hasColumn('products', 'stock_qty')) {
            $prod = $this->fetchOne("SELECT stock_qty as stock FROM products WHERE id = :pid", ['pid' => $productId]);
            if ($prod) {
                return ['product_id' => $productId, 'stock' => (int)$prod['stock'], 'reserved_stock' => 0];
            }
        }
        
        return null;
    }

    public function getVariantById(int $variantId): ?array {
        if (!$this->hasTable('product_variants')) {
            return null;
        }

        return $this->fetchOne(
            "SELECT * FROM product_variants WHERE id = :id",
            [':id' => $variantId]
        );
    }

    /**
     * ATOMIC: Increase physical stock
     */
    public function increaseStock(int $productId, int $qty): bool {
        if ($this->hasTable('inventory')) {
            $stmt = $this->db->prepare("UPDATE inventory SET stock = stock + :qty WHERE product_id = :pid");
            $stmt->execute(['qty' => $qty, 'pid' => $productId]);
            if ($stmt->rowCount() > 0) {
                return true;
            }
        }

        if ($this->hasColumn('products', 'stock_quantity')) {
            $stmt = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity + :qty WHERE id = :pid");
            $stmt->execute(['qty' => $qty, 'pid' => $productId]);
            return $stmt->rowCount() > 0;
        }

        if ($this->hasColumn('products', 'stock_qty')) {
            $stmt = $this->db->prepare("UPDATE products SET stock_qty = stock_qty + :qty WHERE id = :pid");
            $stmt->execute(['qty' => $qty, 'pid' => $productId]);
            return $stmt->rowCount() > 0;
        }

        return false;
    }

    public function increaseVariantStock(int $variantId, int $qty): bool {
        if (!$this->hasTable('product_variants')) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE product_variants SET stock = stock + :qty WHERE id = :id");
        $stmt->execute(['qty' => $qty, 'id' => $variantId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * ATOMIC: Decrease physical stock (Prevent Negative)
     */
    public function decreaseStock(int $productId, int $qty): bool {
        if ($this->hasTable('inventory')) {
            $stmt = $this->db->prepare("UPDATE inventory SET stock = stock - :qty WHERE product_id = :pid AND stock >= :qty_check");
            $stmt->execute(['qty' => $qty, 'pid' => $productId, 'qty_check' => $qty]);
            if ($stmt->rowCount() > 0) {
                return true;
            }
        }

        if ($this->hasColumn('products', 'stock_quantity')) {
            $stmt = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid AND stock_quantity >= :qty_check");
            $stmt->execute(['qty' => $qty, 'pid' => $productId, 'qty_check' => $qty]);
            return $stmt->rowCount() > 0;
        }

        if ($this->hasColumn('products', 'stock_qty')) {
            $stmt = $this->db->prepare("UPDATE products SET stock_qty = stock_qty - :qty WHERE id = :pid AND stock_qty >= :qty_check");
            $stmt->execute(['qty' => $qty, 'pid' => $productId, 'qty_check' => $qty]);
            return $stmt->rowCount() > 0;
        }

        return false;
    }

    public function decreaseVariantStock(int $variantId, int $qty): bool {
        if (!$this->hasTable('product_variants')) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE product_variants SET stock = stock - :qty WHERE id = :id AND stock >= :qty_check");
        $stmt->execute(['qty' => $qty, 'id' => $variantId, 'qty_check' => $qty]);
        return $stmt->rowCount() > 0;
    }

    public function getVariantStockOverview(int $variantId): ?array {
        if (!$this->hasTable('product_variants')) {
            return null;
        }

        return $this->fetchOne(
            "SELECT id, product_id, stock FROM product_variants WHERE id = :id",
            [':id' => $variantId]
        );
    }

    public function getTotalVariantStockByProductId(int $productId): int {
        if (!$this->hasTable('product_variants')) {
            return 0;
        }

        $stmt = $this->db->prepare("SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_id = :pid");
        $stmt->execute([':pid' => $productId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * ATOMIC: Move stock from physical to reserved
     */
    public function reserveStock(int $productId, int $qty): bool {
        $stmt = $this->db->prepare("UPDATE inventory SET stock = stock - :qty, reserved_stock = reserved_stock + :qty_res WHERE product_id = :pid AND stock >= :qty_check");
        $stmt->execute(['qty' => $qty, 'qty_res' => $qty, 'pid' => $productId, 'qty_check' => $qty]);
        if ($stmt->rowCount() > 0) {
            return true;
        }

        if ($this->hasColumn('products', 'stock_quantity')) {
            $stmt = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid AND stock_quantity >= :qty_check");
            $stmt->execute(['qty' => $qty, 'pid' => $productId, 'qty_check' => $qty]);
            return $stmt->rowCount() > 0;
        }

        if ($this->hasColumn('products', 'stock_qty')) {
            $stmt = $this->db->prepare("UPDATE products SET stock_qty = stock_qty - :qty WHERE id = :pid AND stock_qty >= :qty_check");
            $stmt->execute(['qty' => $qty, 'pid' => $productId, 'qty_check' => $qty]);
            return $stmt->rowCount() > 0;
        }

        return false;
    }

    /**
     * ATOMIC: Move stock from reserved back to physical (Release)
     */
    public function releaseStock(int $productId, int $qty): bool {
        $stmt = $this->db->prepare("UPDATE inventory SET stock = stock + :qty, reserved_stock = reserved_stock - :qty_rel WHERE product_id = :pid AND reserved_stock >= :qty_check");
        $stmt->execute(['qty' => $qty, 'qty_rel' => $qty, 'pid' => $productId, 'qty_check' => $qty]);
        if ($stmt->rowCount() > 0) {
            return true;
        }

        if ($this->hasColumn('products', 'stock_quantity')) {
            $stmt = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity + :qty WHERE id = :pid");
            $stmt->execute(['qty' => $qty, 'pid' => $productId]);
            return $stmt->rowCount() > 0;
        }

        if ($this->hasColumn('products', 'stock_qty')) {
            $stmt = $this->db->prepare("UPDATE products SET stock_qty = stock_qty + :qty WHERE id = :pid");
            $stmt->execute(['qty' => $qty, 'pid' => $productId]);
            return $stmt->rowCount() > 0;
        }

        return false;
    }

    /**
     * ATOMIC: Finalize stock (Deduct from reserved permanently)
     */
    public function finalizeStock(int $productId, int $qty): bool {
        $stmt = $this->db->prepare("UPDATE inventory SET reserved_stock = reserved_stock - :qty WHERE product_id = :pid AND reserved_stock >= :qty_check");
        $stmt->execute(['qty' => $qty, 'pid' => $productId, 'qty_check' => $qty]);
        if ($stmt->rowCount() > 0) {
            return true;
        }

        return $this->hasColumn('products', 'stock_quantity') || $this->hasColumn('products', 'stock_qty');
    }

    /**
     * Get low stock alerts
     */
    public function getLowStockAlerts(int $threshold = 10): array {
        return $this->fetchAll(
            "SELECT p.name, p.sku, i.stock FROM inventory i JOIN products p ON i.product_id = p.id WHERE i.stock <= :threshold", 
            ['threshold' => $threshold]
        );
    }

    /**
     * Log a stock activity entry
     */
    public function logActivity(int $productId, string $actionType, int $quantityChange, ?int $previousStock, ?int $newStock, string $performedBy = 'System', ?int $performedById = null, ?string $notes = null, ?string $referenceType = null, ?int $referenceId = null): bool {
        $stmt = $this->db->prepare("
            INSERT INTO stock_activity (product_id, action_type, quantity_change, previous_stock, new_stock, performed_by, performed_by_id, notes, reference_type, reference_id)
            VALUES (:product_id, :action_type, :quantity_change, :previous_stock, :new_stock, :performed_by, :performed_by_id, :notes, :reference_type, :reference_id)
        ");
        return $stmt->execute([
            ':product_id' => $productId,
            ':action_type' => $actionType,
            ':quantity_change' => $quantityChange,
            ':previous_stock' => $previousStock,
            ':new_stock' => $newStock,
            ':performed_by' => $performedBy,
            ':performed_by_id' => $performedById,
            ':notes' => $notes,
            ':reference_type' => $referenceType,
            ':reference_id' => $referenceId,
        ]);
    }

    /**
     * Get stock activity history for a product with pagination
     */
    public function getActivityHistory(int $productId, int $limit = 8, int $offset = 0): array {
        $stmt = $this->db->prepare("
            SELECT sa.id, sa.action_type, sa.quantity_change, sa.previous_stock, sa.new_stock,
                   sa.performed_by, sa.performed_by_id, sa.notes, sa.reference_type, sa.reference_id,
                   sa.created_at
            FROM stock_activity sa
            WHERE sa.product_id = :product_id
            ORDER BY sa.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count stock activity entries for a product
     */
    public function countActivityHistory(int $productId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM stock_activity WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $productId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get total stock added in last N days
     */
    public function getTotalAdded(int $productId, int $days = 30): int {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(quantity_change), 0) FROM stock_activity
            WHERE product_id = :product_id AND action_type = 'added'
            AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get total stock removed in last N days
     */
    public function getTotalRemoved(int $productId, int $days = 30): int {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(ABS(quantity_change)), 0) FROM stock_activity
            WHERE product_id = :product_id AND action_type = 'reduced'
            AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get full inventory report for all products.
     * Returns: product name, sku, stock, reorder price,
     *          units sold in last 30 days, estimated days left, and status.
     */
    public function getInventoryReport(string $search = '', string $status = ''): array {
        $where = ['1=1'];
        $params = [];

        if ($search !== '') {
            $where[] = '(p.name LIKE :search OR p.sku LIKE :search_sku)';
            $params['search']     = '%' . $search . '%';
            $params['search_sku'] = '%' . $search . '%';
        }

        $whereStr = implode(' AND ', $where);

        $sql = "
            SELECT
                p.id,
                p.name,
                p.sku,
                p.base_price                                    AS reorder_price,
                COALESCE(i.stock, p.stock_quantity, 0)         AS in_stock,
                COALESCE(
                    (SELECT SUM(oi.quantity)
                     FROM order_items oi
                     JOIN orders o ON oi.order_id = o.id
                     WHERE oi.product_id = p.id
                       AND o.status != 'cancelled'
                       AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ), 0
                )                                               AS sold_30d,
                CASE
                    WHEN COALESCE(i.stock, p.stock_quantity, 0) <= 0  THEN 'out_of_stock'
                    WHEN COALESCE(i.stock, p.stock_quantity, 0) <= 20 THEN 'low'
                    ELSE 'healthy'
                END                                             AS status_key
            FROM products p
            LEFT JOIN inventory i ON i.product_id = p.id
            WHERE p.deleted_at IS NULL AND $whereStr
            ORDER BY in_stock ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate days_left in PHP (avoids division-by-zero in SQL)
        foreach ($rows as &$row) {
            $sold = (int)$row['sold_30d'];
            $stock = (int)$row['in_stock'];
            $dailyRate = $sold > 0 ? $sold / 30 : 0;
            $row['days_left'] = ($dailyRate > 0) ? (int)round($stock / $dailyRate) : null;
        }
        unset($row);

        // Apply status filter in PHP (simpler than SQL CASE in WHERE)
        if ($status !== '') {
            $rows = array_values(array_filter($rows, fn($r) => $r['status_key'] === $status));
        }

        return $rows;
    }

    /**
     * Get weekly stock movement (In vs Out) for the last 6 weeks
     */
    public function getWeeklyMovement(): array {
        $sql = "
            SELECT 
                DATE_FORMAT(MIN(created_at), '%d %b') as week_label,
                YEARWEEK(created_at, 1) as week_key,
                SUM(CASE WHEN action_type = 'added' THEN quantity_change ELSE 0 END) as stock_in,
                SUM(CASE WHEN action_type = 'reduced' THEN ABS(quantity_change) ELSE 0 END) as stock_out
            FROM stock_activity
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 WEEK)
            GROUP BY week_key
            ORDER BY week_key ASC
            LIMIT 6
        ";
        return $this->fetchAll($sql);
    }
}
