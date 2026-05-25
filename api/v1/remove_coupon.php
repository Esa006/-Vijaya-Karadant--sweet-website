<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/remove_coupon.php
 * Description: Remove an applied coupon from cart session
 * =============================================================
 */

require_once '../../config/config.php';
require_once ROOT_PATH . '/services/CartService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$cartService = new CartService();
$cartService->clearCoupon();
unset($_SESSION['applied_coupon_id']);

echo json_encode([
    'status'    => 'success',
    'message'   => 'Coupon removed.',
    'new_total' => $cartService->getSubtotal() + $cartService->getShippingCharges()
]);
