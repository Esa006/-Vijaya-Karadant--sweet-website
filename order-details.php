<?php
/**
 * Sweets Website
 * =============================================================
 * File: order-details.php
 * Description: Premium Order Details Page (High-Fidelity)
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/OrderService.php';

if (!isset($_SESSION['user_id'])) {
    $id = (int)($_GET['id'] ?? 0);
    header('Location: login.php?redirect=' . urlencode('order-details.php?id=' . $id));
    exit;
}

$userId = (int)$_SESSION['user_id'];
$orderId = (int)($_GET['id'] ?? 0);
$orderService = new OrderService();

$order = $orderId ? $orderService->getOrderDetails($orderId) : null;

if (!$order || (int)($order['user_id'] ?? 0) !== $userId) {
    header('Location: my-orders.php');
    exit;
}

$items = $order['items'] ?? [];
$totalAmount = (float)($order['total_amount'] ?? 0);
$shippingCharges = (float)($order['shipping_charges'] ?? 0);
$discountAmount = (float)($order['discount_amount'] ?? 0);
$taxAmount = (float)($order['tax_amount'] ?? ($totalAmount * 0.18)); 
$subtotal = (float)($order['subtotal'] ?? ($totalAmount - $shippingCharges + $discountAmount - $taxAmount));

$orderNumber = $order['order_number'] ?? '#VK-' . str_pad((string)$order['id'], 8, '0', STR_PAD_LEFT);
$orderDate = date('d M Y, h:i A', strtotime($order['created_at'] ?? 'now'));
$status = strtolower($order['status'] ?? 'pending');
$paymentStatus = strtolower($order['payment_status'] ?? 'unpaid');
$paymentMethod = $order['payment_method'] ?? 'Razorpay (UPI)';
$trackingId = $order['tracking_id'] ?? '-';
$estimatedDelivery = !empty($order['estimated_delivery_date']) 
    ? date('d - d M Y', strtotime($order['estimated_delivery_date'] . ' - 2 days')) . ' - ' . date('d M Y', strtotime($order['estimated_delivery_date']))
    : '18 - 20 May 2025';

$placeholderImage = 'assets/images/homepage/Best Sellers (1).png';

// Tracking helper
$steps = [
    ['key' => 'pending', 'label' => 'Order Placed', 'icon' => 'bi-journal-check'],
    ['key' => 'processing', 'label' => 'Packed', 'icon' => 'bi-box-seam'],
    ['key' => 'shipped', 'label' => 'Shipped', 'icon' => 'bi-truck'],
    ['key' => 'out_for_delivery', 'label' => 'Out for Delivery', 'icon' => 'bi-bicycle'],
    ['key' => 'delivered', 'label' => 'Delivered', 'icon' => 'bi-house-check']
];

$displayStatus = $status;
if ($status === 'paid') $displayStatus = 'processing';

$currentStepIndex = 0;
foreach ($steps as $index => $step) {
    if ($displayStatus === $step['key']) {
        $currentStepIndex = $index;
        break;
    }
}
if ($status === 'shipped') $currentStepIndex = 2;
if ($status === 'out_for_delivery') $currentStepIndex = 3;
if ($status === 'delivered') $currentStepIndex = 4;

require_once 'includes/header.php';
?>

<!-- Reuse Order Tracking Styles for Consistency -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/order-tracking.css?v=<?php echo SITE_VERSION; ?>">

<main class="ot-page py-4 py-md-5">
    <div class="container">
        
        <!-- Breadcrumbs -->
        <nav class="ot-breadcrumb mb-4" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="my-orders.php">My Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">Order Details</li>
            </ol>
        </nav>

        <!-- Header Row -->
        <div class="row align-items-center mb-4 sc-reveal">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <h1 class="ot-page-title mb-0">Order Details</h1>
                    <span class="ot-badge ot-badge--confirmed"><?php echo ucfirst($status); ?></span>
                </div>
                <p class="ot-meta text-muted mb-0">
                    Order Placed on <?php echo $orderDate; ?> <span class="mx-2">•</span> Order ID: <?php echo htmlspecialchars($orderNumber); ?>
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a href="invoice.php?id=<?php echo $orderId; ?>" target="_blank" class="btn ot-btn-outline"><i class="bi bi-download me-2"></i>Download Invoice</a>
                    <a href="contact.php" class="btn ot-btn-solid"><i class="bi bi-chat-dots me-2"></i>Need Help? Contact Support</a>
                </div>
            </div>
        </div>

        <!-- Tracking Card -->
        <div class="ot-card mb-4 sc-reveal delay-100">
            <div class="ot-card-body p-4">
                <h3 class="ot-card-title mb-5">Order Tracking</h3>
                
                <div class="row align-items-center">
                    <div class="col-lg-9">
                        <div class="ot-tracker">
                            <div class="ot-tracker-line">
                                <div class="ot-tracker-line-fill" style="width: <?php echo ($currentStepIndex / (count($steps) - 1)) * 100; ?>%;"></div>
                            </div>
                            <div class="ot-tracker-steps">
                                <?php foreach ($steps as $index => $step): ?>
                                    <?php 
                                        $isCompleted = $index <= $currentStepIndex;
                                        $isActive = $index === $currentStepIndex;
                                    ?>
                                    <div class="ot-step <?php echo $isCompleted ? 'is-completed' : ''; ?> <?php echo $isActive ? 'is-active' : ''; ?>">
                                        <div class="ot-step-icon">
                                            <i class="bi <?php echo $step['icon']; ?>"></i>
                                        </div>
                                        <div class="ot-step-label"><?php echo $step['label']; ?></div>
                                        <div class="ot-step-date small text-muted">
                                            <?php echo $isCompleted ? date('d M Y, h:i A', strtotime($order['created_at'])) : '-'; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 mt-4 mt-lg-0">
                        <div class="ot-delivery-box">
                            <p class="mb-1 text-muted small">Estimated Delivery</p>
                            <h4 class="mb-1"><?php echo $estimatedDelivery; ?></h4>
                            <p class="mb-0 text-muted small text-nowrap">(3 - 5 business days)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="row g-4 mb-4 sc-reveal delay-200">
            <div class="col-lg-4">
                <div class="ot-card h-100">
                    <div class="ot-card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <h4 class="ot-info-title"><i class="bi bi-person me-2"></i>Customer Information</h4>
                            <a href="profile.php" class="ot-edit-link">Edit</a>
                        </div>
                        <h5 class="ot-user-name mb-1"><?php echo htmlspecialchars($order['full_name'] ?? 'Customer'); ?></h5>
                        <p class="mb-1 text-muted"><?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></p>
                        <p class="mb-4 text-muted"><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></p>
                        
                        <div class="ot-address-box">
                            <i class="bi bi-geo-alt"></i>
                            <div>
                                <?php echo nl2br(htmlspecialchars($order['address'] ?? "No address provided")); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ot-card h-100">
                    <div class="ot-card-body p-4">
                        <h4 class="ot-info-title mb-4"><i class="bi bi-card-text me-2"></i>Order Information</h4>
                        <div class="ot-info-list">
                            <div class="ot-info-item">
                                <span>Order ID</span>
                                <strong><?php echo htmlspecialchars($orderNumber); ?></strong>
                            </div>
                            <div class="ot-info-item">
                                <span>Order Date</span>
                                <strong><?php echo $orderDate; ?></strong>
                            </div>
                            <div class="ot-info-item">
                                <span>Payment Method</span>
                                <strong><?php echo htmlspecialchars($paymentMethod); ?></strong>
                            </div>
                            <div class="ot-info-item">
                                <span>Payment Status</span>
                                <strong class="<?php echo $paymentStatus === 'paid' ? 'text-success' : 'text-warning'; ?>"><?php echo ucfirst($paymentStatus); ?></strong>
                            </div>
                            <div class="ot-info-item">
                                <span>Order Status</span>
                                <strong class="text-success"><?php echo ucfirst($status); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ot-card h-100">
                    <div class="ot-card-body p-4">
                        <h4 class="ot-info-title mb-4"><i class="bi bi-truck me-2"></i>Shipping Information</h4>
                        <h5 class="ot-user-name mb-1"><?php echo htmlspecialchars($order['full_name'] ?? 'Customer'); ?></h5>
                        <div class="text-muted mb-4">
                            <?php echo nl2br(htmlspecialchars($order['address'] ?? "No address provided")); ?>
                        </div>
                        
                        <div class="ot-info-list mt-auto">
                            <div class="ot-info-item">
                                <span>Delivery Method</span>
                                <strong>Standard Shipping</strong>
                            </div>
                            <div class="ot-info-item">
                                <span>Tracking ID</span>
                                <strong><?php echo htmlspecialchars($trackingId); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items and Summary -->
        <div class="row g-4 sc-reveal delay-300">
            <div class="col-lg-8">
                <!-- Items Card -->
                <div class="ot-card mb-4">
                    <div class="ot-card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="ot-card-title mb-0">Ordered Items (<?php echo count($items); ?>)</h4>
                            <a href="combos.php" class="btn btn-sm ot-btn-outline"><i class="bi bi-arrow-repeat me-1"></i>Buy Again</a>
                        </div>
                        
                        <div class="ot-items-list">
                            <?php foreach($items as $item): ?>
                            <?php 
                                $image = !empty($item['image']) ? $item['image'] : $placeholderImage;
                                $price = (float)$item['price_at_time'];
                                $qty = (int)$item['quantity'];
                                $sku = !empty($item['sku']) && $item['sku'] !== 'N/A' ? $item['sku'] : 'VK-PRD-' . str_pad($item['product_id'] ?? '0', 3, '0', STR_PAD_LEFT);
                            ?>
                            <div class="ot-item-row p-3 rounded-3 mb-3 border d-flex align-items-center">
                                <div class="ot-item-img me-3">
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="rounded" width="80" height="80" style="object-fit: cover;">
                                </div>
                                <div class="ot-item-details flex-grow-1">
                                    <h5 class="mb-1 fs-6 fw-bold"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="text-muted small mb-1">SKU: <?php echo htmlspecialchars($sku); ?></p>
                                    <p class="mb-0 small">Price: ₹<?php echo number_format($price, 2); ?></p>
                                </div>
                                <div class="ot-item-qty text-center mx-4">
                                    <p class="text-muted small mb-0">Qty: <?php echo $qty; ?></p>
                                </div>
                                <div class="ot-item-total text-end" style="min-width: 120px;">
                                    <span class="fw-bold fs-5">₹<?php echo number_format($price * $qty, 2); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Footer Features Bar -->
                <div class="ot-features-bar p-3 rounded-4 d-flex justify-content-between flex-wrap gap-4 mt-4">
                    <div class="ot-feature-item d-flex align-items-center gap-3">
                        <div class="ot-feature-icon"><i class="bi bi-shield-lock"></i></div>
                        <div>
                            <h6 class="mb-0 fw-bold">Secure Payments</h6>
                            <p class="mb-0 text-muted small">100% secure payment gateway</p>
                        </div>
                    </div>
                    <div class="ot-feature-item d-flex align-items-center gap-3 border-start ps-4">
                        <div class="ot-feature-icon"><i class="bi bi-arrow-repeat"></i></div>
                        <div>
                            <h6 class="mb-0 fw-bold">Easy Returns</h6>
                            <p class="mb-0 text-muted small">7 days easy return policy</p>
                        </div>
                    </div>
                    <div class="ot-feature-item d-flex align-items-center gap-3 border-start ps-4">
                        <div class="ot-feature-icon"><i class="bi bi-truck"></i></div>
                        <div>
                            <h6 class="mb-0 fw-bold">Fast Delivery</h6>
                            <p class="mb-0 text-muted small">Quick delivery across India</p>
                        </div>
                    </div>
                    <div class="ot-feature-item d-flex align-items-center gap-3 border-start ps-4">
                        <div class="ot-feature-icon"><i class="bi bi-headset"></i></div>
                        <div>
                            <h6 class="mb-0 fw-bold">Customer Support</h6>
                            <p class="mb-0 text-muted small">We're here to help anytime</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <!-- Price Summary Card -->
                <div class="ot-card mb-4 shadow-sm border-0" style="background: #fffdfb;">
                    <div class="ot-card-body p-4">
                        <h4 class="ot-card-title mb-4">Price Summary</h4>
                        <div class="ot-price-list">
                            <div class="ot-price-item">
                                <span>Subtotal (<?php echo count($items); ?> Items)</span>
                                <span>₹<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="ot-price-item">
                                <span>Shipping Charges</span>
                                <span class="<?php echo $shippingCharges > 0 ? '' : 'text-success'; ?>"><?php echo $shippingCharges > 0 ? '₹' . number_format($shippingCharges, 2) : 'FREE'; ?></span>
                            </div>
                            <div class="ot-price-item">
                                <span>Discount</span>
                                <span class="text-success">-₹<?php echo number_format($discountAmount, 2); ?></span>
                            </div>
                            <div class="ot-price-item">
                                <span>Tax (18% GST)</span>
                                <span>₹<?php echo number_format($taxAmount, 2); ?></span>
                            </div>
                            <div class="ot-price-total mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                <h4 class="mb-0 fw-bold">Total Amount</h4>
                                <h4 class="mb-0 fw-bold" style="color: #8a2c22;">₹<?php echo number_format($totalAmount, 2); ?></h4>
                            </div>
                        </div>
                        <?php if ($discountAmount > 0): ?>
                        <div class="mt-3 p-2 rounded-3 text-center" style="background: rgba(40, 167, 69, 0.05); border: 1px dashed #28a745;">
                            <span class="text-success small fw-bold"><i class="bi bi-check-circle-fill me-1"></i> You saved ₹<?php echo number_format($discountAmount, 2); ?> on this order</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- More Actions Card -->
                <div class="ot-card shadow-sm border-0" style="background: #fffdfb;">
                    <div class="ot-card-body p-4">
                        <h4 class="ot-card-title mb-4">More Actions</h4>
                        <div class="d-grid gap-2">
                            <a href="invoice.php?id=<?php echo $orderId; ?>" target="_blank" class="btn btn-outline-secondary text-start p-2"><i class="bi bi-download me-2"></i>Download Invoice</a>
                            <a href="order-tracking.php?id=<?php echo $orderId; ?>" class="btn btn-outline-secondary text-start p-2"><i class="bi bi-box-seam me-2"></i>Track Shipment</a>
                            <a href="#" class="btn btn-outline-secondary text-start p-2"><i class="bi bi-arrow-return-left me-2"></i>Return / Replace Item</a>
                            <a href="contact.php" class="btn btn-outline-secondary text-start p-2"><i class="bi bi-headset me-2"></i>Contact Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
