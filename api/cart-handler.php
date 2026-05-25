<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/cart-handler.php
 * Description: AJAX handler for cart actions
 * =============================================================
 */

require_once '../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';
require_once SERVICES_PATH . '/CartService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// CSRF Validation
$token = $_POST['csrf_token'] ?? '';
if (!$token || $token !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF token validation failed']);
    exit;
}

$action = trim((string)($_POST['action'] ?? ''));
$slug = trim((string)($_POST['slug'] ?? ''));
$quantityInput = $_POST['quantity'] ?? 1;
$quantity = filter_var($quantityInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
$weight = trim((string)($_POST['weight'] ?? '500g'));

if ($action !== 'add_to_cart' || $slug === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$productService = new ProductService();
$cartService = new CartService();

// Try finding a product first
$product = $productService->getProductBySlug($slug);
if ($product) {
    $success = $cartService->addItem($product, $quantity, $weight);
    if (!$success) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product is out of stock']);
        exit;
    }
} else {
    // If not a product, try finding a combo
    require_once SERVICES_PATH . '/ComboService.php';
    $comboService = new ComboService();
    $combo = $comboService->getComboBySlug($slug);

    if ($combo) {
        $success = $cartService->addCombo($combo, $quantity);
        if (!$success) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'One or more items in this combo are out of stock']);
            exit;
        }
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit;
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Added to cart',
    'itemCount' => $cartService->getItemCount()
]);
