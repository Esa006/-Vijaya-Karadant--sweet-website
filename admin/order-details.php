<?php
/**
 * Sweets Website
 * =============================================================
 * File: order-details.php
 * Description: Premium Order Detail View with Dynamic Data Binding
 * Author: Antigravity - Senior Backend Engineer
 * Version: 3.0.0 (VeloCart Design)
 * =============================================================
 */

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once SERVICES_PATH . '/OrderService.php';

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    header('Location: orders.php');
    exit;
}

$service = new OrderService();
$order   = $service->getOrderDetails($orderId);

if (!$order) {
    ?>
    <style>
        .error-wrapper { min-height: calc(100vh - 70px); display: flex; align-items: center; justify-content: center; background-color: #f4f1ec; }
        .error-card { background: #fff; border-radius: 16px; padding: 40px; text-align: center; max-width: 420px; width: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #f0ebe5; margin: 0 auto; }
        .error-icon { font-size: 56px; color: #b5572e; margin-bottom: 16px; line-height: 1; }
        .error-title { font-size: 24px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
        .error-desc { font-size: 14.5px; color: #6b7280; margin-bottom: 24px; line-height: 1.5; }
        .btn-accent { padding: 10px 24px; border: 1.5px solid #b5572e; border-radius: 8px; background: #b5572e; color: #fff; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; text-decoration: none; }
        .btn-accent:hover { background: #9a4a25; border-color: #9a4a25; color: #fff; }
    </style>
    <div class="main-content p-0" style="background-color: #f4f1ec; min-height: 100vh;">
        <?php require_once 'includes/topbar.php'; ?>
        <div class="error-wrapper p-4">
            <div class="error-card">
                <div class="error-icon"><i class="bi bi-search"></i></div>
                <div class="error-title">Order Not Found</div>
                <div class="error-desc">We couldn't find the order you're looking for. It may have been deleted or the ID is incorrect.</div>
                <a href="orders.php" class="btn-accent">
                    <i class="bi bi-arrow-left"></i> Back to Orders
                </a>
            </div>
        </div>
    </div>
    <?php
    require_once 'includes/footer.php';
    exit;
}

// Prepare data
$status     = strtolower($order['status']);
$payStatus  = strtolower($order['payment_status'] ?? 'unpaid');
$items      = $order['items'] ?? [];
$subtotal   = 0;
foreach ($items as $item) { $subtotal += $item['price_at_time'] * $item['quantity']; }
$total      = (float)($order['total_amount'] ?? 0);
$shippingFee = (float)($order['shipping_charges'] ?? 0);
$taxAmount   = (float)($order['tax_amount'] ?? 0);
$discountAmount = (float)($order['discount_amount'] ?? 0);

// Status Transition Logic
$transitionMap = [
    'pending'   => ['paid', 'cancelled'],
    'paid'      => ['shipped', 'cancelled'],
    'shipped'   => ['delivered'],
    'delivered' => [],
    'cancelled' => [],
];
$nextActions = $transitionMap[$status] ?? [];
$statusActionLabels = [
    'paid'      => ['label' => 'Mark as Paid',     'cls' => 'btn-accent'],
    'shipped'   => ['label' => 'Mark as Shipped',  'cls' => 'btn-accent'],
    'delivered' => ['label' => 'Mark as Delivered','cls' => 'btn-accent'],
    'cancelled' => ['label' => 'Cancel Order',     'cls' => 'btn-outline-custom'],
];

// Formatting initials
$initials = '';
$parts = explode(' ', $order['customer_name']);
foreach($parts as $p) $initials .= !empty($p) ? strtoupper($p[0]) : '';
$initials = substr($initials, 0, 2);

// Addresses
$formatAddr = function($prefix, $data) {
    $line1 = trim($data[$prefix . '_line1'] ?? '');
    $city  = trim($data[$prefix . '_city'] ?? '');
    $state = trim($data[$prefix . '_state'] ?? '');
    $zip   = trim($data[$prefix . '_zip'] ?? '');
    
    // Check if we have ANY meaningful address data
    $hasData = (!empty($line1) && $line1 !== 'undefined') || 
               (!empty($city) && $city !== 'undefined') || 
               (!empty($state) && $state !== 'undefined');

    if (!$hasData) return "No address provided.";
    
    $recipient = trim($data[$prefix . '_recipient'] ?? $data['customer_name'] ?? '');
    if ($recipient === 'undefined' || empty($recipient)) $recipient = $data['customer_name'] ?? 'Customer';

    // Filter out 'undefined' strings from all components
    $line1 = ($line1 === 'undefined') ? '' : $line1;
    $city  = ($city === 'undefined') ? '' : $city;
    $state = ($state === 'undefined') ? '' : $state;
    $zip   = ($zip === 'undefined') ? '' : $zip;

    $lines = [
        $recipient,
        $line1,
        trim($data[$prefix . '_line2'] ?? ''),
        (($city || $state) ? ($city . ($state ? ', ' . $state : '')) : '') . ($zip ? ' ' . $zip : ''),
        trim($data[$prefix . '_country'] ?? 'India')
    ];
    
    // Remove empty values and join with <br>
    $filteredLines = array_filter(array_map('trim', $lines), function($l) {
        return !empty($l) && $l !== 'undefined';
    });
    
    return implode("<br>", $filteredLines);
};
$shippingAddr = $formatAddr('shipping', $order);
$billingAddr  = ($order['billing_address_id'] === $order['shipping_address_id']) 
                ? "Same as Shipping Address" 
                : $formatAddr('billing', $order);
?>

<style>
    :root {
      --bg-body: #f4f1ec;
      --bg-card: #ffffff;
      --accent-primary: #b5572e;
      --accent-secondary: #d4845a;
      --accent-warm: #e8a87c;
      --accent-light: #fdf0e9;
      --text-primary: #1a1a1a;
      --text-secondary: #6b7280;
      --text-muted: #9ca3af;
      --border-color: #e8e0d8;
      --border-light: #f0ebe5;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
      --shadow-md: 0 4px 12px rgba(0,0,0,0.06);
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --badge-paid: #16a34a;
      --badge-paid-bg: #dcfce7;
      --badge-processing: #2563eb;
      --badge-processing-bg: #dbeafe;
      --badge-failed: #dc2626;
      --badge-failed-bg: #fee2e2;
    }

    .main-wrapper-custom { background-color: var(--bg-body); min-height: 100vh; }
    .breadcrumb-custom { display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 4px; }
    .breadcrumb-custom a { color: var(--accent-primary); font-weight: 500; text-decoration: none; }
    .breadcrumb-custom .current { color: var(--text-primary); font-weight: 600; }
    
    .page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
    .page-header h1 { font-size: 26px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; }
    .page-header .subtitle { font-size: 13px; color: var(--text-secondary); }

    .btn-outline-custom { padding: 9px 20px; border: 1.5px solid var(--border-color); border-radius: var(--radius-sm); background: #fff; color: var(--text-primary); font-size: 13px; font-weight: 550; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s; text-decoration: none; }
    .btn-outline-custom:hover { border-color: var(--accent-primary); color: var(--accent-primary); background: var(--accent-light); }
    .btn-accent { padding: 9px 20px; border: 1.5px solid var(--accent-primary); border-radius: var(--radius-sm); background: var(--accent-primary); color: #fff; font-size: 13px; font-weight: 550; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s; text-decoration: none; }
    .btn-accent:hover { background: #9a4a25; border-color: #9a4a25; color: #fff; }

    .card-custom { background: var(--bg-card); border-radius: var(--radius-lg); border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 24px; }
    .card-header-custom { padding: 18px 22px; border-bottom: 1px solid var(--border-light); display: flex; align-items: center; justify-content: space-between; }
    .card-header-custom h3 { font-size: 15px; font-weight: 700; color: var(--accent-primary); margin: 0; }
    .card-body-custom { padding: 22px; }

    .products-table { width: 100%; border-collapse: collapse; }
    .products-table th { padding: 12px 16px; font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-light); text-align: left; }
    .products-table td { padding: 14px 16px; border-bottom: 1px solid var(--border-light); font-size: 13.5px; }
    .product-cell { display: flex; align-items: center; gap: 12px; }
    .product-thumb { width: 48px; height: 48px; border-radius: var(--radius-sm); object-fit: cover; border: 1px solid var(--border-light); }
    .product-name { font-weight: 600; color: var(--text-primary); }
    .product-sku { font-size: 11.5px; color: var(--text-muted); }

    .badge-custom { padding: 4px 12px; border-radius: 50px; font-size: 11.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
    .badge-paid { background: var(--badge-paid-bg); color: var(--badge-paid); }
    .badge-processing { background: var(--badge-processing-bg); color: var(--badge-processing); }
    .badge-failed { background: var(--badge-failed-bg); color: var(--badge-failed); }

    .info-row { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-light); gap: 12px; }
    .info-row:last-child { border-bottom: none; }
    .info-label { font-size: 13px; color: var(--text-secondary); }
    .info-value { font-size: 13px; font-weight: 600; color: var(--text-primary); text-align: right; }
    .info-row-total { background: var(--accent-light); margin: 0 -22px; padding: 16px 22px; border-radius: 0 0 var(--radius-lg) var(--radius-lg); }
    .info-row-total .info-value { font-size: 20px; font-weight: 800; color: var(--accent-primary); }

    .customer-profile { display: flex; align-items: center; gap: 14px; padding-bottom: 16px; border-bottom: 1px solid var(--border-light); margin-bottom: 4px; }
    .customer-avatar { width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-primary), var(--accent-warm)); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; font-weight: 700; }
    .address-section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--accent-primary); margin: 16px 0 8px; }
    .address-block { background: var(--bg-body); border-radius: var(--radius-sm); padding: 12px 14px; font-size: 13px; color: var(--text-secondary); border: 1px solid var(--border-light); line-height: 1.6; }

    .order-action-btn:disabled { opacity: 0.7; cursor: not-allowed; }

    @media print {
        body { background: #fff !important; }
        .sidebar, .topbar, .admin-topbar, .breadcrumb-custom, .page-header-actions { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; }
        .main-wrapper-custom { background: #fff !important; }
        .card-custom { box-shadow: none !important; border: 1px solid var(--border-light) !important; break-inside: avoid; }
        .page-header { margin-top: 0; padding-top: 20px; }
    }
</style>

<div class="main-content p-0 main-wrapper-custom">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="page-content px-4 py-4">
      <!-- Breadcrumb -->
      <div class="breadcrumb-custom">
        <a href="orders.php">Orders</a>
        <span class="separator"><i class="bi bi-chevron-right"></i></span>
        <span class="current">Order Details</span>
      </div>

      <!-- Page Header -->
      <div class="page-header">
        <div class="page-header-left">
          <h1>Order #<?php echo htmlspecialchars($order['order_number'] ?? $orderId); ?></h1>
          <p class="subtitle">Placed on <?php echo date('M d, Y \a\t h:i A', strtotime($order['created_at'])); ?></p>
        </div>
        <div class="page-header-actions d-flex gap-2">
          <?php foreach ($nextActions as $next):
              $btnCfg = $statusActionLabels[$next] ?? ['label' => ucfirst($next), 'cls' => 'btn-accent'];
          ?>
          <button type="button" 
                  class="order-action-btn <?php echo $btnCfg['cls']; ?>"
                  data-order-id="<?php echo $orderId; ?>"
                  data-order-num="<?php echo htmlspecialchars($order['order_number'] ?? $orderId); ?>"
                  data-new-status="<?php echo $next; ?>">
              <?php echo $btnCfg['label']; ?>
          </button>
          <?php endforeach; ?>

          <button class="btn-outline-custom" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Invoice
          </button>
          <a href="invoice.php?id=<?php echo $orderId; ?>" target="_blank" class="btn-accent">
            <i class="bi bi-receipt"></i> Premium Invoice
          </a>
        </div>
      </div>

      <div class="row g-4">
        <!-- LEFT COLUMN -->
        <div class="col-lg-8">
          <!-- Ordered Products Card -->
          <div class="card-custom">
            <div class="card-header-custom">
              <h3><i class="bi bi-bag-check me-2"></i>Ordered Products</h3>
              <span class="badge-custom" style="background:var(--accent-light); color:var(--accent-primary);">
                <?php echo count($items); ?> Items
              </span>
            </div>
            <div class="card-body-custom p-0">
              <div class="table-responsive">
                <table class="products-table">
                  <thead>
                    <tr>
                      <th>Product Details</th>
                      <th>Price</th>
                      <th class="text-center">Quantity</th>
                      <th class="text-end">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($items as $item): 
                        $price = (float)$item['price_at_time'];
                        $qty   = (int)$item['quantity'];
                        $line  = $price * $qty;
                        $imgPath = !empty($item['image']) 
                                   ? (BASE_URL . $item['image']) 
                                   : (BASE_URL . 'assets/images/placeholders/product-placeholder.png');
                    ?>
                    <tr>
                      <td>
                        <div class="product-cell">
                          <img src="<?php echo htmlspecialchars($imgPath); ?>" class="product-thumb" />
                          <div>
                            <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="product-sku">SKU: <?php echo strtoupper($item['slug']); ?></div>
                          </div>
                        </div>
                      </td>
                      <td>₹ <?php echo number_format($price, 2); ?></td>
                      <td class="text-center"><?php echo $qty; ?></td>
                      <td class="text-end"><strong>₹ <?php echo number_format($line, 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Info Grid Row -->
          <div class="row g-4">
            <div class="col-md-6">
              <div class="card-custom h-100">
                <div class="card-header-custom">
                  <h3><i class="bi bi-truck me-2"></i>Shipping Status</h3>
                </div>
                <div class="card-body-custom">
                  <div class="info-row">
                    <span class="info-label">Courier Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['delivery_partner'] ?? 'Standard Delivery'); ?></span>
                  </div>
                  <div class="info-row">
                    <span class="info-label">Tracking ID</span>
                    <span class="info-value">
                      <?php if (!empty($order['tracking_id'])): ?>
                        <span class="fw-bold" style="color:var(--accent-primary)"><?php echo htmlspecialchars($order['tracking_id']); ?></span>
                      <?php else: ?>
                        <span class="text-muted">TBA</span>
                      <?php endif; ?>
                    </span>
                  </div>
                  <div class="info-row">
                    <span class="info-label">Est. Delivery</span>
                    <span class="info-value">
                      <?php 
                        echo !empty($order['estimated_delivery_date']) 
                             ? date('M d, Y', strtotime($order['estimated_delivery_date'])) 
                             : date('M d, Y', strtotime($order['created_at'] . ' + 4 days')); 
                      ?>
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card-custom h-100">
                <div class="card-header-custom">
                  <h3><i class="bi bi-credit-card me-2"></i>Payment Attribute</h3>
                </div>
                <div class="card-body-custom">
                  <div class="info-row">
                    <span class="info-label">Method</span>
                    <span class="info-value"><?php echo strtoupper($order['payment_method'] ?? 'ONLINE'); ?></span>
                  </div>
                  <div class="info-row">
                    <span class="info-label">Transaction ID</span>
                    <span class="info-value">
                        <?php if (!empty($order['payment_id'])): ?>
                            <code style="color:var(--accent-primary)"><?php echo htmlspecialchars($order['payment_id']); ?></code>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </span>
                  </div>
                  <div class="info-row">
                    <span class="info-label">Payment Status</span>
                    <span class="info-value">
                      <span class="badge-custom <?php echo ($payStatus === 'paid') ? 'badge-paid' : 'badge-failed'; ?>">
                        <?php echo strtoupper($payStatus); ?>
                      </span>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-lg-4">
          <!-- Order Summary Card -->
          <div class="card-custom">
            <div class="card-header-custom">
              <h3><i class="bi bi-receipt me-2"></i>Order Summary</h3>
            </div>
            <div class="card-body-custom">
              <div class="info-row">
                <span class="info-label">Order Number</span>
                <span class="info-value">#<?php echo htmlspecialchars($order['order_number']); ?></span>
              </div>
              <div class="info-row">
                <span class="info-label">Order Status</span>
                <span class="info-value">
                  <span class="badge-custom <?php 
                      echo ($status === 'delivered') ? 'badge-paid' : (($status === 'cancelled') ? 'badge-failed' : 'badge-processing'); 
                    ?>">
                    <?php echo ucfirst($status); ?>
                  </span>
                </span>
              </div>
              <div class="info-row">
                <span class="info-label">Subtotal</span>
                <span class="info-value">₹ <?php echo number_format($subtotal, 2); ?></span>
              </div>
              <div class="info-row">
                <span class="info-label">Shipping Charges</span>
                <span class="info-value">₹ <?php echo number_format($shippingFee, 2); ?></span>
              </div>
              <div class="info-row">
                <span class="info-label">Tax Amount</span>
                <span class="info-value">₹ <?php echo number_format($taxAmount, 2); ?></span>
              </div>
              <?php if ($discountAmount > 0): ?>
              <div class="info-row text-success">
                <span class="info-label" style="color: #16a34a;">Discount</span>
                <span class="info-value" style="color: #16a34a;">-₹ <?php echo number_format($discountAmount, 2); ?></span>
              </div>
              <?php endif; ?>
              <div class="info-row info-row-total">
                <span class="info-label">Total Amount</span>
                <span class="info-value">₹ <?php echo number_format($total, 2); ?></span>
              </div>
            </div>
          </div>

          <!-- Customer Info Card -->
          <div class="card-custom">
            <div class="card-header-custom">
              <h3><i class="bi bi-person me-2"></i>Customer Profile</h3>
            </div>
            <div class="card-body-custom">
              <div class="customer-profile">
                <div class="customer-avatar"><?php echo $initials; ?></div>
                <div>
                  <div class="customer-name">
                    <a href="customer-details.php?id=<?php echo $order['user_id']; ?>" class="text-decoration-none text-dark fw-bold">
                        <?php echo htmlspecialchars($order['customer_name']); ?>
                    </a>
                  </div>
                  <div class="customer-since">Member since <?php echo date('M Y', strtotime($order['customer_since'])); ?></div>
                </div>
              </div>
              <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
              </div>
              <div class="info-row">
                <span class="info-label">Phone</span>
                <span class="info-value"><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></span>
              </div>

              <div class="address-section">
                <div class="address-section-title">Shipping Address</div>
                <div class="address-block"><?php echo $shippingAddr; ?></div>
              </div>
              <div class="address-section">
                <div class="address-section-title">Billing Address</div>
                <div class="address-block"><?php echo $billingAddr; ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div id="ordersToastContainer" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Toast Utility
    function showToast(msg, type = 'success') {
        const container = document.getElementById('ordersToastContainer');
        const el  = document.createElement('div');
        const bg   = type === 'error' ? '#dc2626' : '#16a34a';
        el.style.cssText = `background:${bg};color:#fff;padding:12px 18px;border-radius:10px;
                             box-shadow:0 4px 15px rgba(0,0,0,.15);display:flex;align-items:center;
                             gap:10px;font-size:.9rem;font-weight:600;min-width:280px; transition: opacity 0.4s;`;
        el.innerHTML = `<span>${msg}</span>`;
        container.appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 3500);
    }

    // 2. Status Updates (AJAX)
    document.querySelectorAll('.order-action-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const orderId   = this.dataset.orderId;
            const orderNum  = this.dataset.orderNum;
            const newStatus = this.dataset.newStatus;

            if (!confirm(`Switch Order #${orderNum} state to "${newStatus}"?`)) return;

            this.disabled = true;
            const originalHtml = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

            try {
                const fd = new FormData();
                fd.append('action', 'update_status');
                fd.append('order_id', orderId);
                fd.append('status', newStatus);
                fd.append('csrf_token', '<?php echo $_SESSION["csrf_token"] ?? ""; ?>');

                const res    = await fetch('api/v1/orders.php', { method: 'POST', body: fd });
                const result = await res.json();

                if (result.status === 'success') {
                    showToast(result.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(result.message || 'Transmission failed.', 'error');
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                }
            } catch (err) {
                showToast('Infrastructure error.', 'error');
                this.disabled = false;
                this.innerHTML = originalHtml;
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
