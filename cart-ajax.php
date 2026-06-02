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
                // [STOCK VALIDATION] Backend check to prevent bypassing frontend limits via AJAX
                $validationPassed = true;
                if (empty($product['is_combo'])) {
                    $variants = $productService->getProductVariants((int)$product['id']);
                    $maxStock = (int)($product['stock_quantity'] ?? 0);
                    $foundVariantStock = 0;
                    $isValidVariant = false;

                    foreach ($variants as $v) {
                        if (($v['weight'] ?? '') === $weight) {
                            $isValidVariant = true;
                            $foundVariantStock = (int)($v['stock'] ?? 0);
                            if (!isset($v['id'])) {
                                $foundVariantStock = min($foundVariantStock, $maxStock);
                            }
                            break;
                        }
                    }

                    if (!$isValidVariant) {
                        $response['message'] = 'Invalid product weight selected.';
                        $validationPassed = false;
                    } elseif ($quantity > $foundVariantStock) {
                        $response['message'] = 'Requested quantity exceeds available stock.';
                        $validationPassed = false;
                    }
                }

                if ($validationPassed && $cartService->addItem($product, $quantity, $weight)) {
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
            
            $cartItems = $cartService->getItems();
            if (isset($_SESSION['cart'][$cartId])) {
                $item = $_SESSION['cart'][$cartId];
                $availableStock = $cartService->getAvailableStockForItem($item);
                
                if ($quantity <= 0) {
                    if ($cartService->updateQuantity($cartId, 0)) {
                        $response = [
                            'success' => true,
                            'cartCount' => $cartService->getItemCount(),
                            'subtotal' => $cartService->getSubtotal(),
                            'clamped' => false
                        ];
                    }
                } else if ($availableStock !== null && $quantity > $availableStock) {
                    $clampedQty = max(1, $availableStock);
                    $cartService->updateQuantity($cartId, $clampedQty);
                    $response = [
                        'success' => false,
                        'clamped' => true,
                        'message' => "Only $clampedQty unit(s) available in stock. Cart updated to max limit.",
                        'clampedQty' => $clampedQty,
                        'cartCount' => $cartService->getItemCount(),
                        'subtotal' => $cartService->getSubtotal()
                    ];
                } else {
                    if ($cartService->updateQuantity($cartId, $quantity)) {
                        $response = [
                            'success' => true,
                            'cartCount' => $cartService->getItemCount(),
                            'subtotal' => $cartService->getSubtotal(),
                            'clamped' => false
                        ];
                    }
                }
            } else {
                $response['message'] = 'Item not found in cart';
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
