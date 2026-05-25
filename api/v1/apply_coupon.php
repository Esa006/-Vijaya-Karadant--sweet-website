<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/apply_coupon.php
 * Description: Validate and apply a coupon code to the cart session
 * =============================================================
 */

// Suppress warnings from corrupting JSON — all errors go to log only
error_reporting(0);

require_once '../../config/config.php';
require_once ROOT_PATH . '/services/CartService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// Safe string sanitize (FILTER_SANITIZE_STRING deprecated in PHP 8)
$code = strtoupper(trim((string)($_POST['code'] ?? '')));
$code = preg_replace('/[^A-Z0-9\-_]/', '', $code); // Only alphanumeric, dash, underscore

if (empty($code)) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a coupon code.']);
    exit;
}

try {
    $db = Database::getInstance();

    // Fetch the coupon
    $stmt = $db->prepare("
        SELECT * FROM coupons 
        WHERE code = :code 
          AND is_active = 1 
          AND (expires_at IS NULL OR expires_at = '' OR expires_at > NOW())
        LIMIT 1
    ");
    $stmt->execute([':code' => $code]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coupon) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired coupon code.']);
        exit;
    }

    // Cart subtotal check
    $cartService = new CartService();
    $subtotal = $cartService->getSubtotal();

    if ($subtotal <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Your cart is empty.']);
        exit;
    }

    $minOrder = (float)($coupon['min_cart_total'] ?? 0);
    if ($minOrder > 0 && $subtotal < $minOrder) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'This coupon needs a minimum order of ₹' . number_format($minOrder) . '. Add more items!'
        ]);
        exit;
    }

    // Usage limit check (total uses)
    if (!empty($coupon['usage_limit'])) {
        $usageStmt = $db->prepare("SELECT COUNT(*) FROM coupon_usages WHERE coupon_id = :cid");
        $usageStmt->execute([':cid' => $coupon['id']]);
        $totalUses = (int)$usageStmt->fetchColumn();
        if ($totalUses >= (int)$coupon['usage_limit']) {
            echo json_encode(['status' => 'error', 'message' => 'This coupon has reached its usage limit.']);
            exit;
        }
    }

    // Per-user limit check
    if (!empty($coupon['limit_per_user']) && !empty($_SESSION['user_id'])) {
        $userUsageStmt = $db->prepare("SELECT COUNT(*) FROM coupon_usages WHERE coupon_id = :cid AND user_id = :uid");
        $userUsageStmt->execute([':cid' => $coupon['id'], ':uid' => (int)$_SESSION['user_id']]);
        $userUses = (int)$userUsageStmt->fetchColumn();
        if ($userUses >= (int)$coupon['limit_per_user']) {
            echo json_encode(['status' => 'error', 'message' => 'You have already used this coupon.']);
            exit;
        }
    }

    // Calculate discount
    $discountAmount = 0;
    if ($coupon['type'] === 'percentage') {
        $discountAmount = round($subtotal * ((float)$coupon['value'] / 100), 2);
    } else {
        $discountAmount = min((float)$coupon['value'], $subtotal);
    }

    // Store in session
    $cartService->setCouponDiscount($discountAmount, $coupon['code']);
    $_SESSION['applied_coupon_id'] = (int)$coupon['id'];

    $newTotal = max(0, $cartService->getSubtotal() + $cartService->getShippingCharges() - $discountAmount);

    echo json_encode([
        'status'          => 'success',
        'message'         => 'Coupon applied! You saved ₹' . number_format($discountAmount, 2),
        'discount_amount' => $discountAmount,
        'coupon_code'     => $coupon['code'],
        'coupon_type'     => $coupon['type'],
        'coupon_value'    => (float)$coupon['value'],
        'new_total'       => $newTotal
    ]);

} catch (Throwable $e) {
    error_log('[apply_coupon] Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error. Please try again.']);
}
