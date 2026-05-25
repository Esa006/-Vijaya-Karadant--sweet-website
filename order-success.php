<?php
/**
 * Sweets Website
 * =============================================================
 * File: order-success.php
 * Description: Premium Order Confirmation Page
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/OrderService.php';

$orderService = new OrderService();
$orderId = $_GET['order_id'] ?? $_GET['id'] ?? null;
$order = $orderId ? $orderService->getOrderDetails((int)$orderId) : null;

if ($order) {
    $addressParts = array_filter([
        $order['shipping_line1'] ?? '',
        $order['shipping_line2'] ?? '',
        $order['shipping_city'] ?? '',
        $order['shipping_state'] ?? '',
        $order['shipping_zip'] ?? '',
        $order['shipping_country'] ?? ''
    ], static function ($value) {
        return trim((string)$value) !== '';
    });

    $order['full_name'] = $order['shipping_recipient']
        ?? $order['customer_name']
        ?? $order['full_name']
        ?? 'Customer';
    $order['address'] = implode(', ', $addressParts);
}

// Safe fallback when order_id is invalid/missing
if (!$order) {
    $order = [
        'order_number' => '#',
        'full_name' => 'Customer',
        'address' => 'No address provided'
    ];
}

require_once 'includes/header.php';
?>

<!-- Order Success Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/order-success.css?v=<?php echo SITE_VERSION; ?>">

<main class="c-order-success py-5">
    <div class="container py-lg-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                
                <!-- ── Success Hero ────────────────────────── -->
                <div class="text-center mb-5">
                    <div class="c-success-icon-wrap mb-4">
                        <div class="c-success-icon">
                            <i class="bi bi-check-lg"></i>
                        </div>
                    </div>
                    <h1 class="c-order-success__title">Order Confirmed!</h1>
                    <p class="c-order-success__subtitle px-md-5">
                        Thank you for your order, <?php echo htmlspecialchars($order['full_name'] ?? 'Customer'); ?>. Your delicious sweets are being prepared with love and tradition.
                    </p>
                </div>

                <!-- ── Order Reference Card ──────────────────── -->
                <div class="c-order-ref-card mb-5">
                    <div class="c-order-ref-header d-flex justify-content-between align-items-center mb-4">
                        <div class="c-order-ref-title">
                            <p class="text-muted small mb-1">Order Reference</p>
                            <h2 class="h3 fw-bold mb-0"><?php echo htmlspecialchars($order['order_number']); ?></h2>
                        </div>
                        <span class="c-badge-confirmed">Confirmed</span>
                    </div>

                    <div class="c-order-info-group">
                        <!-- Estimated Delivery -->
                        <div class="c-order-info-row d-flex gap-3 mb-4">
                            <div class="c-order-info-icon">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Estimated Delivery</h6>
                                <p class="text-muted small mb-0">Your order will be delivered within 7 days.</p>
                            </div>
                        </div>

                        <!-- Delivery Address -->
                        <div class="c-order-info-row d-flex gap-3">
                            <div class="c-order-info-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Delivery Address</h6>
                                <p class="fw-bold small mb-1"><?php echo htmlspecialchars($order['full_name'] ?? 'Customer'); ?></p>
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars($order['address'] ?? 'No address provided'); ?></p>
                                <?php if (!empty($order['phone'])): ?>
                                    <p class="fw-bold small mb-0"><i class="bi bi-telephone-fill me-1"></i> <?php echo htmlspecialchars($order['phone']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Action Buttons ───────────────────────── -->
                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <a href="order-tracking.php?order_id=<?php echo $orderId; ?>" class="btn c-order-btn c-order-btn--primary w-100">Track Order</a>
                    </div>
                    <div class="col-md-6">
                        <a href="index.php" class="btn c-order-btn c-order-btn--outline w-100">Continue Shopping</a>
                    </div>
                </div>

                <!-- ── Need Help ────────────────────────────── -->
                <div class="text-center">
                    <p class="text-muted small">
                        Need help with your order? 
                        <a href="help.php" class="text-maroon fw-bold text-decoration-none ms-2">
                            <i class="bi bi-telephone-fill me-2"></i>Contact Support
                        </a>
                    </p>
                </div>

            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
