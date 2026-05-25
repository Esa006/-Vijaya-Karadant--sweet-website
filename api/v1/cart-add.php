<?php
declare(strict_types=1);
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/cart-add.php
 * Description: Add-to-cart endpoint with real-time stock revalidation
 *
 * POST  product_id, quantity, weight, variant_id
 * =============================================================
 */

require_once __DIR__ . '/../../config/config.php';
require_once SERVICES_PATH . '/CartService.php';
require_once REPOS_PATH   . '/StockRepository.php';
require_once REPOS_PATH   . '/ProductRepository.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$quantity  = max(1, (int)($_POST['quantity']  ?? 1));
$weight    = trim((string)($_POST['weight']   ?? '500g'));
$variantId = (int)($_POST['variant_id'] ?? 0);

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product_id']);
    exit;
}

// ── 1. Revalidate stock from backend (never trust frontend) ──
$stockRepo = new StockRepository();
$payload   = $stockRepo->getStockPayload($productId);

if ($payload['stock_status'] === 'out_of_stock') {
    echo json_encode([
        'success'      => false,
        'stock_status' => 'out_of_stock',
        'message'      => 'Sorry, this product is out of stock.',
    ]);
    exit;
}

if ($payload['stock_quantity'] < $quantity) {
    echo json_encode([
        'success'          => false,
        'stock_status'     => $payload['stock_status'],
        'stock_quantity'   => $payload['stock_quantity'],
        'message'          => "Only {$payload['stock_quantity']} left in stock.",
    ]);
    exit;
}

// ── 2. Load product and add to session cart ──────────────────
$productRepo = new ProductRepository();
$product     = $productRepo->getById($productId);

if (!$product) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$cartService = new CartService();
$added       = $cartService->addItem($product, $quantity, $weight, $variantId);

if (!$added) {
    echo json_encode([
        'success'      => false,
        'stock_status' => 'out_of_stock',
        'message'      => 'Could not add to cart — stock unavailable.',
    ]);
    exit;
}

echo json_encode([
    'success'      => true,
    'stock_status' => $payload['stock_status'],
    'cart_count'   => $cartService->getItemCount(),
    'message'      => 'Added to cart successfully!',
]);
