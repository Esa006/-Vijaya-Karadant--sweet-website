<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/cart/apply-coupon.php
 * Description: API to apply a coupon and update cart totals
 * =============================================================
 */

require_once '../../config/config.php';
require_once SERVICES_PATH . '/CartService.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$couponTitle = $input['coupon'] ?? '';

if (empty($couponTitle)) {
    echo json_encode(['success' => false, 'message' => 'Invalid coupon']);
    exit;
}

$cartService = new CartService();
$subtotal = $cartService->getSubtotal();

if ($subtotal <= 0) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

$discount = 0;
$message = '';

// Simple hardcoded logic matching the UI offers
if (stripos($couponTitle, 'Flat ₹100') !== false) {
    if ($subtotal > 999) {
        $discount = 100;
        $message = '₹100 discount applied!';
    } else {
        echo json_encode(['success' => false, 'message' => 'Minimum order value ₹999 required for this coupon.']);
        exit;
    }
} elseif (stripos($couponTitle, '15% Off') !== false) {
    $discount = floor($subtotal * 0.15);
    if ($discount > 150) $discount = 150; // Cap at ₹150 as per UI desc
    $message = '15% discount applied!';
} else {
    echo json_encode(['success' => false, 'message' => 'Coupon not found']);
    exit;
}

$cartService->setCouponDiscount((float)$discount, $couponTitle);

echo json_encode([
    'success' => true,
    'message' => $message,
    'discount' => $discount,
    'subtotal' => $subtotal,
    'shipping' => $cartService->getShippingCharges(),
    'total' => $cartService->getTotal()
]);
