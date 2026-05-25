<?php
/**
 * Sweets Website
 * =============================================================
 * File: cart-ajax.php
 * Description: AJAX handler for cart operations
 * Author: Sweets Website Team
 * Version: 1.0.0
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';
require_once SERVICES_PATH . '/CartService.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cartService = new CartService();
    $productService = new ProductService();

    switch ($action) {
        case 'add':
            $slug = $_POST['slug'] ?? '';
            $quantity = (int)($_POST['quantity'] ?? 1);
            $weight = $_POST['weight'] ?? '500g';

            $product = $productService->getProductBySlug($slug);
            // Fallback for demo products handled in CartService potentially, 
            // but let's be explicitly safe here for now.
            if ($product) {
                if ($cartService->addItem($product, $quantity, $weight)) {
                    $response = [
                        'success' => true,
                        'message' => 'Product added to cart!',
                        'cartCount' => $cartService->getItemCount(),
                        'cartItems' => $cartService->getItems(),
                        'subtotal' => $cartService->getSubtotal()
                    ];
                }
            } else {
                $response['message'] = 'Product not found: ' . $slug;
            }
            break;

        case 'update':
            $cartId = $_POST['id'] ?? '';
            $quantity = (int)($_POST['qty'] ?? 1);
            if ($cartService->updateQuantity($cartId, $quantity)) {
                $response = [
                    'success' => true,
                    'cartCount' => $cartService->getItemCount(),
                    'subtotal' => $cartService->getSubtotal()
                ];
            }
            break;

        case 'remove':
            $cartId = $_POST['id'] ?? '';
            if ($cartService->removeItem($cartId)) {
                $response = [
                    'success' => true,
                    'cartCount' => $cartService->getItemCount(),
                    'subtotal' => $cartService->getSubtotal()
                ];
            }
            break;

        case 'fetch':
            $response = [
                'success' => true,
                'cartCount' => $cartService->getItemCount(),
                'cartItems' => $cartService->getItems(),
                'subtotal' => $cartService->getSubtotal()
            ];
            break;
    }
}

echo json_encode($response);
exit;
