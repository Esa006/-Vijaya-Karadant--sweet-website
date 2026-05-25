<?php
/**
 * Sweets Website
 * =============================================================
 * File: InventoryService.php
 * Description: Business logic for Product Inventory
 * Author: Antigravity - Senior Backend Engineer
 * Version: 2.1.0
 * =============================================================
 */

require_once REPOS_PATH . '/InventoryRepository.php';
require_once __DIR__ . '/../src/Service/EmailService.php';

use App\Service\EmailService;

class InventoryService {
    private InventoryRepository $repo;

    public function __construct() {
        $this->repo = new InventoryRepository();
    }

    /**
     * Increase physical stock
     */
    public function increaseStock(int $productId, int $qty): bool {
        $result = $this->repo->increaseStock($productId, $qty);
        if ($result) {
            $this->processStockNotifications($productId);
        }
        return $result;
    }

    /**
     * Decrease physical stock (Absolute)
     */
    public function decreaseStock(int $productId, int $qty): bool {
        return $this->repo->decreaseStock($productId, $qty);
    }

    /**
     * Reserve stock for a pending order
     */
    public function reserveStock(int $productId, int $qty): bool {
        return $this->repo->reserveStock($productId, $qty);
    }

    /**
     * Release reserved stock (Order cancelled/expired)
     */
    public function releaseStock(int $productId, int $qty): bool {
        return $this->repo->releaseStock($productId, $qty);
    }

    /**
     * Finalize stock (Move from reserved to sold)
     */
    public function finalizeStock(int $productId, int $qty): bool {
        return $this->repo->finalizeStock($productId, $qty);
    }

    /**
     * Get inventory details for a product
     */
    public function getStock(int $productId): ?array {
        return $this->repo->getByProductId($productId);
    }

    /**
     * Add stock with activity logging
     */
    public function addStock(int $productId, int $qty, string $notes = '', string $performedBy = 'Admin', ?int $performedById = null): array {
        $current = $this->repo->getByProductId($productId);
        $previousStock = $current ? (int)$current['stock'] : 0;

        $success = $this->repo->increaseStock($productId, $qty);

        if ($success) {
            $this->repo->logActivity(
                $productId, 'added', $qty, $previousStock, $previousStock + $qty,
                $performedBy, $performedById, $notes ?: 'Manual stock addition'
            );
            $this->processStockNotifications($productId);
        }

        return ['success' => $success, 'new_stock' => $previousStock + $qty];
    }

    /**
     * Remove/reduce stock with activity logging
     */
    public function removeStock(int $productId, int $qty, string $notes = '', string $performedBy = 'Admin', ?int $performedById = null): array {
        $current = $this->repo->getByProductId($productId);
        $previousStock = $current ? (int)$current['stock'] : 0;

        if ($qty > $previousStock) {
            return ['success' => false, 'message' => 'Insufficient stock'];
        }

        $success = $this->repo->decreaseStock($productId, $qty);

        if ($success) {
            $this->repo->logActivity(
                $productId, 'reduced', -$qty, $previousStock, $previousStock - $qty,
                $performedBy, $performedById, $notes ?: 'Manual stock reduction'
            );
        }

        return ['success' => $success, 'new_stock' => $previousStock - $qty];
    }

    /**
     * Get stock overview for a product
     */
    public function getStockOverview(int $productId): array {
        $inv = $this->repo->getByProductId($productId);
        if (!$inv) {
            return ['stock' => 0, 'reserved' => 0];
        }
        return [
            'stock' => (int)$inv['stock'],
            'reserved' => (int)$inv['reserved_stock'],
        ];
    }

    public function addVariantStock(int $productId, int $variantId, int $qty, string $notes = '', string $performedBy = 'Admin', ?int $performedById = null): array {
        $variant = $this->repo->getVariantById($variantId);
        if (!$variant || (int)$variant['product_id'] !== $productId) {
            return ['success' => false, 'message' => 'Variant not found for product'];
        }

        $previousStock = (int)($variant['stock'] ?? 0);
        $success = $this->repo->increaseVariantStock($variantId, $qty);

        if ($success) {
            $variantLabel = (string)($variant['label'] ?? ('Variant #' . $variantId));
            $this->repo->logActivity(
                $productId,
                'added',
                $qty,
                $previousStock,
                $previousStock + $qty,
                $performedBy,
                $performedById,
                trim(($notes ?: 'Manual variant stock addition') . ' [Variant: ' . $variantLabel . ']')
            );
        }

        return ['success' => $success, 'new_stock' => $previousStock + $qty];
    }

    public function removeVariantStock(int $productId, int $variantId, int $qty, string $notes = '', string $performedBy = 'Admin', ?int $performedById = null): array {
        $variant = $this->repo->getVariantById($variantId);
        if (!$variant || (int)$variant['product_id'] !== $productId) {
            return ['success' => false, 'message' => 'Variant not found for product'];
        }

        $previousStock = (int)($variant['stock'] ?? 0);
        if ($qty > $previousStock) {
            return ['success' => false, 'message' => 'Insufficient stock'];
        }

        $success = $this->repo->decreaseVariantStock($variantId, $qty);

        if ($success) {
            $variantLabel = (string)($variant['label'] ?? ('Variant #' . $variantId));
            $this->repo->logActivity(
                $productId,
                'reduced',
                -$qty,
                $previousStock,
                $previousStock - $qty,
                $performedBy,
                $performedById,
                trim(($notes ?: 'Manual variant stock reduction') . ' [Variant: ' . $variantLabel . ']')
            );
        }

        return ['success' => $success, 'new_stock' => $previousStock - $qty];
    }

    public function getVariantStockOverview(int $variantId): ?array {
        $variant = $this->repo->getVariantStockOverview($variantId);
        if (!$variant) {
            return null;
        }

        $productId = (int)($variant['product_id'] ?? 0);
        return [
            'variant_id' => (int)$variant['id'],
            'product_id' => $productId,
            'stock' => (int)$variant['stock'],
            'total_stock' => $this->repo->getTotalVariantStockByProductId($productId)
        ];
    }

    /**
     * Automatically send emails to users who requested 'Notify Me' for a product
     */
    public function processStockNotifications(int $productId): void {
        try {
            $db = Database::getInstance();
            
            // Get product info
            $stmt = $db->prepare("SELECT name FROM products WHERE id = :id");
            $stmt->execute([':id' => $productId]);
            $productName = $stmt->fetchColumn();
            if (!$productName) return;

            // Find all pending requests
            $stmt = $db->prepare("SELECT id, email FROM stock_notifications WHERE product_id = :pid AND product_type = 'product' AND status = 'pending'");
            $stmt->execute([':pid' => $productId]);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($requests)) return;

            $loginUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/sweet-website/';
            $subject = "Good News! {$productName} is Back in Stock";
            $html = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                    <h2 style='color: #8C3333; border-bottom: 2px solid #8C3333; padding-bottom: 10px;'>Back in Stock Alert!</h2>
                    <p>Hello,</p>
                    <p>You recently asked us to notify you when <strong>{$productName}</strong> is back in stock.</p>
                    <p>Good news! It's available right now. Hurry before it sells out again!</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$loginUrl}product-detail.php?id={$productId}' style='background-color: #8C3333; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;'>Shop Now</a>
                    </div>
                </div>
            ";

            $updateStmt = $db->prepare("UPDATE stock_notifications SET status = 'notified', notified_at = NOW() WHERE id = :id");

            $emailService = new \App\Service\EmailService();
            foreach ($requests as $req) {
                if ($emailService->sendHtmlEmail($req['email'], $subject, $html)) {
                    $updateStmt->execute([':id' => $req['id']]);
                }
            }
        } catch (\Throwable $e) {
            error_log('[InventoryService] processStockNotifications failed: ' . $e->getMessage());
        }
    }
}
