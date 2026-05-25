<?php
/**
 * Sweets Website
 * =============================================================
 * File: inventory.php
 * Description: Controller for inventory actions (POST)
 * Author: Antigravity - Senior Backend Engineer
 * Version: 2.1.0
 * =============================================================
 */

require_once 'BaseController.php';
require_once SERVICES_PATH . '/InventoryService.php';
require_once SERVICES_PATH . '/ProductService.php';

class InventoryController extends BaseController {
    private InventoryService $service;
    private ProductService $productService;

    public function __construct() {
        parent::__construct();
        $this->service = new InventoryService();
        $this->productService = new ProductService();
    }

    /**
     * Handle stock update request (supports JSON and FormData)
     */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('method_not_allowed', 'Allow: POST', 405);
        }

        $isJson = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
        $data = $isJson ? $this->getJsonInput() : $_POST;

        $productId = (int)($data['product_id'] ?? 0);
        $variantId = (int)($data['variant_id'] ?? 0);
        $qty       = (int)($data['quantity'] ?? 0);
        $action    = (string)($data['action'] ?? '');
        $notes     = (string)($data['notes'] ?? '');

        if ($productId <= 0 || $qty <= 0) {
            $this->error('invalid_input', 'Product ID and positive quantity required.');
        }

        try {
            $result = null;
            if ($action === 'add') {
                if ($variantId > 0) {
                    $result = $this->service->addVariantStock($productId, $variantId, $qty, $notes);
                } else {
                    $result = $this->service->addStock($productId, $qty, $notes);
                }
            } elseif ($action === 'reduce' || $action === 'remove') {
                if ($variantId > 0) {
                    $result = $this->service->removeVariantStock($productId, $variantId, $qty, $notes);
                } else {
                    $result = $this->service->removeStock($productId, $qty, $notes);
                }
            } else {
                $this->error('invalid_action', 'Action must be "add" or "reduce".');
            }

            if ($result && $result['success']) {
                $statusInfo = $this->calculateStatus($result['new_stock']);
                
                if ($variantId > 0) {
                    $payload = $this->service->getVariantStockOverview($variantId);
                    if (!$payload) {
                        $this->error('inventory_failure', 'Variant stock overview unavailable.');
                    }
                    
                    // Add status info for the product (based on total stock)
                    $productStatus = $this->calculateStatus($payload['total_stock']);
                    $payload['status_label'] = $productStatus['label'];
                    $payload['status_class'] = $productStatus['class'];
                    
                    $payload['stats'] = $this->productService->getProductStats();
                    $this->success($payload, 'Variant stock updated successfully.');
                }

                $payload = $this->service->getStockOverview($productId);
                $payload['status_label'] = $statusInfo['label'];
                $payload['status_class'] = $statusInfo['class'];
                $payload['stats'] = $this->productService->getProductStats();
                $this->success($payload, 'Stock updated successfully.');
            } else {
                $this->error('inventory_failure', $result['message'] ?? 'Update failed (insufficient stock?).');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->error('server_error', 'An unexpected error occurred.', 500);
        }
    }

    /**
     * Helper to calculate status label and class based on stock level
     */
    private function calculateStatus(int $stock): array {
        if ($stock > 10) {
            return ['label' => 'In Stock', 'class' => 'products-status-in'];
        } elseif ($stock > 0) {
            return ['label' => 'Low Stock', 'class' => 'products-status-low'];
        } else {
            return ['label' => 'Out of Stock', 'class' => 'products-status-out'];
        }
    }
}

// ROUTE: /admin/api/v1/inventory.php
$controller = new InventoryController();
$controller->update();
