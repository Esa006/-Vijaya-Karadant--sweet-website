<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/cart-sync.php
 * Description: Lightweight endpoint to return the current cart state for cross-tab sync
 * =============================================================
 */

require_once '../../config/config.php';
require_once SERVICES_PATH . '/CartService.php';

header('Content-Type: application/json');

$cartService = new CartService();

echo json_encode([
    'success' => true,
    'cartCount' => $cartService->getItemCount(),
    'cartItems' => $cartService->getItems(),
    'subtotal' => $cartService->getSubtotal()
]);
