<?php
/**
 * Sweets Website
 * =============================================================
 * File: invoice.php
 * Description: Premium Invoice View for Administrative Order Management
 * Design: High-Fidelity, Design-Token based with Status Management
 * =============================================================
 */

require_once '../config/config.php';
require_once 'includes/auth.php';
require_once SERVICES_PATH . '/InvoiceService.php';

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    die("Error: Invalid Order ID provided.");
}

$invoiceService = new InvoiceService();
$data = $invoiceService->getInvoiceDataByOrder($orderId);

if (!$data || !$data['order']) {
    die("Error: Order #{$orderId} not found.");
}

// Populate missing totals if needed (for old orders)
$invoiceService->calculateTotals($data);

$order    = $data['order'];
$invoice  = $data['invoice'];
$items    = $data['items'];
$company  = $data['company'];

// Format data
$orderDate   = date('M d, Y', strtotime($order['created_at']));
$invoiceDate = date('M d, Y', strtotime($invoice['invoice_date'] ?? $order['created_at']));
$status      = ucfirst($order['status']);
$payStatus   = ucfirst($order['payment_status']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title>Invoice <?php echo htmlspecialchars((string)($invoice['invoice_number'] ?? '')); ?> - Vijaya Karadant Sweets</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    /* ============================================
       CSS VARIABLES — DESIGN TOKENS
       ============================================ */
    :root {
      --color-brand-primary: #4A1A04;
      --color-brand-secondary: #8B4513;
      --color-brand-accent: #E8890C;
      --color-brand-accent-light: #F5A623;
      --color-white: #FFFFFF;
      --color-gray-50: #F9FAFB;
      --color-gray-100: #F3F4F6;
      --color-gray-200: #E5E7EB;
      --color-gray-300: #D1D5DB;
      --color-gray-400: #9CA3AF;
      --color-gray-500: #6B7280;
      --color-gray-600: #4B5563;
      --color-gray-700: #374151;
      --color-gray-800: #1F2937;
      --color-gray-900: #111827;
      --color-success: #16A34A;
      --color-success-bg: #DCFCE7;
      --color-success-border: #BBF7D0;
      --color-info: #2563EB;
      --color-info-bg: #DBEAFE;
      --color-info-border: #BFDBFE;
      --color-danger: #DC2626;
      --color-danger-bg: #FEE2E2;
      --color-danger-border: #FECACA;
      --color-warning: #D97706;
      --color-warning-bg: #FEF3C7;
      --color-warning-border: #FDE68A;
      --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      --font-xs: 0.6875rem;
      --font-sm: 0.8125rem;
      --font-base: 0.875rem;
      --font-md: 1rem;
      --font-lg: 1.125rem;
      --font-xl: 1.25rem;
      --font-2xl: 1.5rem;
      --font-3xl: 1.75rem;
      --space-1: 4px;
      --space-2: 8px;
      --space-3: 12px;
      --space-4: 16px;
      --space-5: 20px;
      --space-6: 24px;
      --space-8: 32px;
      --space-10: 40px;
      --radius-sm: 4px;
      --radius-md: 8px;
      --radius-lg: 12px;
      --radius-xl: 16px;
      --radius-full: 9999px;
      --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
      --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
      --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.04);
      --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05);
      --transition-fast: 150ms ease;
      --transition-base: 250ms ease;
      --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ============================================
       RESET & BASE
       ============================================ */
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
    html { -webkit-text-size-adjust: 100%; scroll-behavior: smooth; }
    body {
      font-family: var(--font-family);
      font-size: var(--font-base);
      line-height: 1.6;
      color: var(--color-gray-800);
      background: var(--color-gray-100);
      overflow-x: hidden;
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding: var(--space-8) var(--space-4);
    }

    /* ============================================
       INVOICE CARD (MODAL-LIKE)
       ============================================ */
    #invoiceCard {
      background: var(--color-white);
      border-radius: var(--radius-xl);
      width: 100%;
      max-width: 850px;
      position: relative;
      box-shadow: var(--shadow-xl);
      overflow: hidden;
    }

    #invoiceCard__accent {
      position: absolute;
      top: var(--space-8);
      right: 0;
      bottom: var(--space-8);
      width: 5px;
      background: linear-gradient(to bottom, var(--color-brand-accent), var(--color-brand-accent-light));
      border-radius: var(--radius-full) 0 0 var(--radius-full);
      z-index: 2;
    }

    .card-body {
      padding: var(--space-8);
      padding-right: var(--space-10);
    }

    /* ============================================
       HEADER
       ============================================ */
    .header-row {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: var(--space-4);
      margin-bottom: var(--space-6);
    }
    .header-row__title { font-size: var(--font-2xl); font-weight: 800; color: var(--color-gray-900); letter-spacing: -0.02em; }
    .header-row__subtitle { font-size: var(--font-base); color: var(--color-gray-500); margin-top: 2px; font-weight: 500; }
    .header-actions { display: flex; align-items: center; gap: var(--space-3); }

    /* ============================================
       BUTTONS
       ============================================ */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: var(--space-2);
      padding: var(--space-2) var(--space-4);
      font-family: var(--font-family);
      font-size: var(--font-sm);
      font-weight: 600;
      border-radius: var(--radius-md);
      border: 1.5px solid transparent;
      cursor: pointer;
      transition: all var(--transition-fast);
      white-space: nowrap;
      text-decoration: none;
    }
    .btn:active { transform: scale(0.96); }
    .btn svg { width: 16px; height: 16px; }
    .btn--outline-primary { background: var(--color-white); color: var(--color-brand-primary); border-color: var(--color-brand-secondary); }
    .btn--outline-primary:hover { background: #FDF6EE; border-color: var(--color-brand-primary); box-shadow: var(--shadow-sm); }
    .btn--outline-gray { background: var(--color-white); color: var(--color-gray-600); border-color: var(--color-gray-300); }
    .btn--solid-primary { background: var(--color-brand-primary); color: var(--color-white); }
    .btn--solid-primary:hover { background: #5a2008; box-shadow: 0 4px 12px rgba(74, 26, 4, 0.3); }

    /* ============================================
       BRAND SECTION
       ============================================ */
    .brand-section {
      display: flex;
      align-items: flex-start; justify-content: space-between; gap: var(--space-6);
      padding: var(--space-6); background: var(--color-gray-50);
      border-radius: var(--radius-lg); margin-bottom: var(--space-8);
      border: 1px solid var(--color-gray-200);
    }
    .brand-logo {
      width: 56px; height: 56px;
      background: linear-gradient(135deg, var(--color-brand-secondary), var(--color-brand-primary));
      border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center;
      color: var(--color-white); font-weight: 800; font-size: var(--font-xl);
    }
    .brand-section__company { text-align: right; }
    .brand-section__company h2 { font-size: var(--font-lg); font-weight: 700; color: var(--color-gray-900); margin-bottom: 2px; }
    .brand-section__company p { font-size: var(--font-sm); color: var(--color-gray-500); line-height: 1.5; }

    /* ============================================
       INVOICE META
       ============================================ */
    .invoice-meta {
      display: flex; align-items: flex-end; justify-content: space-between;
      margin-bottom: var(--space-8); padding-bottom: var(--space-6);
      border-bottom: 2px solid var(--color-gray-100);
    }
    .invoice-meta h2 { font-size: var(--font-3xl); font-weight: 800; color: var(--color-brand-primary); text-transform: uppercase; }
    .invoice-meta__data { text-align: right; font-size: var(--font-base); }
    .invoice-meta__data .label { color: var(--color-brand-accent); font-weight: 600; }
    .invoice-meta__data .value { font-weight: 700; color: var(--color-gray-900); }

    /* ============================================
       ADDRESS GRID
       ============================================ */
    .address-grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-8); margin-bottom: var(--space-8); }
    .address-block__label { font-size: var(--font-sm); font-weight: 700; color: var(--color-brand-accent); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--space-2); }
    .address-block__name { font-size: var(--font-md); font-weight: 700; color: var(--color-gray-900); margin-bottom: 2px; }
    .address-block p { font-size: var(--font-sm); color: var(--color-gray-600); line-height: 1.6; }

    /* ============================================
       ORDER INFO GRID
       ============================================ */
    .order-info { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-4); padding: var(--space-5); background: var(--color-white); border: 1.5px solid var(--color-gray-200); border-radius: var(--radius-lg); margin-bottom: var(--space-8); }
    .order-info__label { font-size: var(--font-xs); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: var(--space-2); }
    .order-info__value { font-size: var(--font-base); font-weight: 600; color: var(--color-gray-800); }

    /* ============================================
       ITEMS TABLE (Custom Integration)
       ============================================ */
    .items-table-container { margin-bottom: var(--space-8); }
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table th { text-align: left; padding: var(--space-4) var(--space-2); font-size: var(--font-xs); font-weight: 700; text-transform: uppercase; color: var(--color-gray-400); border-bottom: 2px solid var(--color-gray-100); }
    .items-table td { padding: var(--space-4) var(--space-2); border-bottom: 1px solid var(--color-gray-50); }
    .items-table__name { font-weight: 700; color: var(--color-gray-900); }
    .items-table__sku { font-size: var(--font-xs); color: var(--color-gray-400); }

    /* ============================================
       BADGES & STATUS
       ============================================ */
    .badge { display: inline-flex; align-items: center; padding: 4px 10px; font-size: var(--font-xs); font-weight: 700; border-radius: var(--radius-sm); border: 1px solid transparent; }
    .badge--success { background: var(--color-success-bg); color: var(--color-success); border-color: var(--color-success-border); }
    .badge--info { background: var(--color-info-bg); color: var(--color-info); border-color: var(--color-info-border); }
    .badge--warning { background: var(--color-warning-bg); color: var(--color-warning); border-color: var(--color-warning-border); }
    .badge--danger { background: var(--color-danger-bg); color: var(--color-danger); border-color: var(--color-danger-border); }

    .status-update { position: relative; }
    .status-dropdown { position: absolute; top: calc(100% + 8px); right: 0; background: var(--color-white); border: 1px solid var(--color-gray-200); border-radius: var(--radius-md); box-shadow: var(--shadow-lg); padding: 8px; min-width: 160px; z-index: 10; opacity: 0; visibility: hidden; transform: translateY(-8px); transition: all 0.2s; }
    .status-dropdown.is-open { opacity: 1; visibility: visible; transform: translateY(0); }
    .status-option { display: block; width: 100%; padding: 8px 12px; font-size: var(--font-sm); text-align: left; border: none; background: none; cursor: pointer; border-radius: 4px; color: var(--color-gray-700); }
    .status-option:hover { background: var(--color-gray-50); }

    /* ============================================
       PRICING
       ============================================ */
    .pricing-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: var(--space-8); margin-bottom: var(--space-8); }
    .pricing-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--color-gray-100); }
    .pricing-total { border-top: 2px solid var(--color-brand-accent); padding-top: 12px; margin-top: 8px; }

    /* ============================================
       TOAST
       ============================================ */
    #toastContainer { position: fixed; top: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 12px; }
    .toast { display: flex; align-items: center; gap: 12px; padding: 14px 20px; background: var(--color-white); border-radius: 8px; box-shadow: var(--shadow-xl); border-left: 4px solid var(--color-success); font-weight: 600; color: var(--color-gray-800); animation: slideIn 0.35s ease forwards; }
    @keyframes slideIn { from { opacity:0; transform:translateX(30px); } to { opacity:1; transform:translateX(0); } }

    /* ============================================
       PRINT
       ============================================ */
    @media print {
      body { background: white; padding: 0; }
      #invoiceCard { box-shadow: none; border-radius: 0; max-width: 100%; }
      .header-actions, .no-print, #invoiceCard__accent { display: none !important; }
      .brand-section, .order-info { background: white; border: 1px solid #eee; }
    }
  </style>
</head>
<body>

  <!-- TOAST CONTAINER -->
  <div id="toastContainer"></div>

  <!-- INVOICE CARD -->
  <article id="invoiceCard">
    <div id="invoiceCard__accent"></div>
    <div class="card-body">

      <!-- HEADER -->
      <header class="header-row">
        <div>
          <h1 class="header-row__title">Official Invoice</h1>
          <p class="header-row__subtitle">Order #<?php echo htmlspecialchars((string)($order['order_number'] ?? '')); ?></p>
        </div>
        <div class="header-actions no-print">
          <button class="btn btn--outline-primary" onclick="window.print()">
            <i data-lucide="printer"></i> <span>Print</span>
          </button>
          <button class="btn btn--solid-primary" id="downloadPdfBtn">
            <i data-lucide="download"></i> <span>Download PDF</span>
          </button>
          <button class="btn btn--outline-gray" onclick="window.location.href='order-details.php?id=<?php echo $orderId; ?>'">
            <i data-lucide="arrow-left"></i> <span>Back</span>
          </button>
        </div>
      </header>

      <!-- BRAND SECTION -->
      <section class="brand-section">
        <div style="display: flex; align-items: center; gap: 16px;">
          <div class="brand-logo">VK</div>
          <div>
            <h3 style="font-weight:700; color:var(--color-brand-primary);">Vijaya Karadant Sweets</h3>
            <p style="font-size:var(--font-xs); color:var(--color-gray-500);">Premium Quality Since 1907</p>
          </div>
        </div>
        <div class="brand-section__company">
          <h2><?php echo htmlspecialchars((string)($company['company_name'] ?? '')); ?></h2>
          <p>
            <?php echo htmlspecialchars((string)($company['address_line1'] ?? '')); ?><br>
            <?php echo htmlspecialchars((string)($company['city'] ?? '')); ?>, <?php echo htmlspecialchars((string)($company['state'] ?? '')); ?><br>
            <?php echo htmlspecialchars((string)($company['phone'] ?? '')); ?><br>
            <?php echo htmlspecialchars((string)($company['email'] ?? '')); ?>
          </p>
        </div>
      </section>

      <!-- INVOICE META -->
      <section class="invoice-meta">
        <h2>INVOICE</h2>
        <div class="invoice-meta__data">
          <p><span class="label">Invoice No :</span> <span class="value"><?php echo htmlspecialchars((string)($invoice['invoice_number'] ?? '')); ?></span></p>
          <p><span class="label">Date :</span> <span class="value"><?php echo $invoiceDate; ?></span></p>
        </div>
      </section>

      <!-- ADDRESS GRID -->
      <section class="address-grid">
        <div class="address-block">
          <div class="address-block__label">Billed To</div>
          <div class="address-block__name"><?php echo htmlspecialchars((string)($order['billing_recipient'] ?? $order['customer_name'] ?? '')); ?></div>
          <p>
            <?php echo htmlspecialchars((string)($order['billing_line1'] ?? $order['shipping_line1'] ?? '')); ?><br>
            <?php echo htmlspecialchars((string)($order['billing_city'] ?? $order['shipping_city'] ?? '')); ?>, <?php echo htmlspecialchars((string)($order['billing_state'] ?? $order['shipping_state'] ?? '')); ?> <?php echo htmlspecialchars((string)($order['billing_zip'] ?? $order['shipping_zip'] ?? '')); ?><br>
            <?php echo htmlspecialchars((string)($order['customer_phone'] ?? '')); ?>
          </p>
        </div>
        <div class="address-block">
          <div class="address-block__label">Shipped To</div>
          <div class="address-block__name"><?php echo htmlspecialchars((string)($order['shipping_recipient'] ?? $order['customer_name'] ?? '')); ?></div>
          <p>
            <?php echo htmlspecialchars((string)($order['shipping_line1'] ?? '')); ?><br>
            <?php echo htmlspecialchars((string)($order['shipping_city'] ?? '')); ?>, <?php echo htmlspecialchars((string)($order['shipping_state'] ?? '')); ?> <?php echo htmlspecialchars((string)($order['shipping_zip'] ?? '')); ?><br>
            <?php echo htmlspecialchars((string)($order['shipping_phone'] ?? $order['customer_phone'] ?? '')); ?><br>
            Method: <?php echo htmlspecialchars((string)($order['payment_method'] ?? 'Standard')); ?>
          </p>
        </div>
      </section>

      <!-- ORDER INFO -->
      <section class="order-info">
        <div class="order-info__item">
          <div class="order-info__label" style="color:var(--color-brand-primary)">Order Date</div>
          <div class="order-info__value"><?php echo $orderDate; ?></div>
        </div>
        <div class="order-info__item">
          <div class="order-info__label" style="color:var(--color-brand-secondary)">Payment</div>
          <div class="order-info__value"><?php echo ucfirst($order['payment_method']); ?></div>
        </div>
        <div class="order-info__item">
          <div class="order-info__label" style="color:var(--color-success)">Status</div>
          <div class="order-info__value">
            <span class="badge <?php 
              $ps = strtolower($order['payment_status']);
              echo ($ps === 'paid') ? 'badge--success' : (($ps === 'pending') ? 'badge--warning' : 'badge--danger'); 
            ?>"><?php echo $payStatus; ?></span>
          </div>
        </div>
        <div class="order-info__item">
          <div class="order-info__label" style="color:var(--color-brand-accent)">Order Tracking</div>
          <div class="order-info__value">
            <div class="status-update">
              <span id="orderStatusBadge" class="badge <?php 
                $os = strtolower($order['status']);
                echo ($os === 'delivered') ? 'badge--success' : (($os === 'cancelled') ? 'badge--danger' : (($os === 'shipped') ? 'badge--warning' : 'badge--info'));
              ?>" onclick="toggleStatusDropdown()" style="cursor:pointer;">
                <?php echo $status; ?>
              </span>
              <div id="statusDropdown" class="status-dropdown">
                <button class="status-option" onclick="updateOrderStatus('Processing')">Processing</button>
                <button class="status-option" onclick="updateOrderStatus('Shipped')">Shipped</button>
                <button class="status-option" onclick="updateOrderStatus('Delivered')">Delivered</button>
                <button class="status-option" onclick="updateOrderStatus('Cancelled')">Cancelled</button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ITEMS TABLE -->
      <section class="items-table-container">
        <table class="items-table">
          <thead>
            <tr>
              <th>Description</th>
              <th style="text-align:center">Qty</th>
              <th style="text-align:right">Price</th>
              <th style="text-align:right">Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): 
              $price = (float)$item['price_at_time'];
              $qty   = (int)$item['quantity'];
              $line  = $price * $qty;
            ?>
            <tr>
              <td>
                <p class="items-table__name"><?php echo htmlspecialchars((string)($item['name'] ?? '')); ?></p>
                <p class="items-table__sku">SKU: <?php echo strtoupper((string)($item['slug'] ?? '')); ?></p>
              </td>
              <td style="text-align:center; font-weight:600;"><?php echo $qty; ?></td>
              <td style="text-align:right; font-weight:500;">₹<?php echo number_format($price, 2); ?></td>
              <td style="text-align:right; font-weight:800; color:var(--color-gray-900);">₹<?php echo number_format($line, 2); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <!-- NOTES & PRICING -->
      <section class="pricing-grid">
        <div>
          <h4 style="color:var(--color-brand-primary); font-weight:800; margin-bottom:8px;">Notes</h4>
          <p style="font-size:var(--font-sm); color:var(--color-gray-500); line-height:1.7;">
            <?php echo nl2br(htmlspecialchars((string)($order['notes'] ?? 'Authentic sweets handcrafted with love. Please store in a cool, dry place.'))); ?>
          </p>
        </div>
        <div class="pricing-table">
          <div class="pricing-row">
            <span class="label">Subtotal</span>
            <span class="value">₹<?php echo number_format((float)$order['subtotal'], 2); ?></span>
          </div>
          <?php if ((float)$order['discount_amount'] > 0): ?>
          <div class="pricing-row">
            <span class="label">Discount</span>
            <span class="value" style="color:var(--color-danger)">-₹<?php echo number_format((float)$order['discount_amount'], 2); ?></span>
          </div>
          <?php endif; ?>
          <div class="pricing-row">
            <span class="label">Shipping</span>
            <span class="value">₹<?php echo number_format((float)$order['shipping_charges'], 2); ?></span>
          </div>
          <div class="pricing-row">
            <span class="label">Tax (GST <?php echo (float)$order['tax_rate']; ?>%)</span>
            <span class="value">₹<?php echo number_format((float)$order['tax_amount'], 2); ?></span>
          </div>
          <div class="pricing-row pricing-total">
            <span style="font-weight:800; color:var(--color-gray-900); font-size:var(--font-md);">Grand Total</span>
            <span style="font-weight:900; color:var(--color-brand-primary); font-size:var(--font-xl);">₹<?php echo number_format((float)$order['total_amount'], 2); ?></span>
          </div>
        </div>
      </section>

      <!-- FOOTER -->
      <footer style="display:flex; justify-content:space-between; align-items:flex-end; padding-top:24px; border-top:1px solid var(--color-gray-100);">
        <div>
          <h3 style="color:var(--color-brand-primary); font-weight:800; margin-bottom:4px;">Thank you for your order!</h3>
          <p style="font-size:var(--font-xs); color:var(--color-gray-400);">Terms apply. Goods sold are usually perishable sweets.</p>
        </div>
        <div style="text-align:right;">
          <div style="width:180px; height:1.5px; background:var(--color-gray-300); margin-bottom:8px; margin-left:auto;"></div>
          <p style="font-size:var(--font-sm); font-weight:600; color:var(--color-gray-500); font-style:italic;">Authorized Signatory</p>
        </div>
      </footer>

    </div><!-- .card-body -->
  </article>

  <!-- LIBRARIES -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

  <script>
    // Initialize Icons
    lucide.createIcons();

    // STATUS DROPDOWN TOGGLE
    function toggleStatusDropdown() {
      document.getElementById('statusDropdown').classList.toggle('is-open');
    }

    // UPDATE STATUS AJAX
    async function updateOrderStatus(newStatus) {
      const orderId = <?php echo $orderId; ?>;
      const dropdown = document.getElementById('statusDropdown');
      const badge = document.getElementById('orderStatusBadge');
      
      dropdown.classList.remove('is-open');
      showToast('Updating order status...', 'info');

      try {
        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('order_id', orderId);
        formData.append('status', newStatus);
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');

        const response = await fetch('api/v1/orders.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
          showToast(result.message, 'success');
          // Update Badge UI
          badge.textContent = newStatus;
          // Update Badge Colors
          badge.className = 'badge';
          const lower = newStatus.toLowerCase();
          if (lower === 'delivered') badge.classList.add('badge--success');
          else if (lower === 'cancelled') badge.classList.add('badge--danger');
          else if (lower === 'shipped') badge.classList.add('badge--warning');
          else badge.classList.add('badge--info');
        } else {
          showToast(result.message || 'Update failed', 'danger');
        }
      } catch (error) {
        console.error('Update Error:', error);
        showToast('Network error while updating status.', 'danger');
      }
    }

    // PDF DOWNLOAD
    document.getElementById('downloadPdfBtn')?.addEventListener('click', function() {
      if (typeof html2pdf === 'undefined') {
        showToast('PDF library not loaded. Please refresh.', 'danger');
        return;
      }

      showToast('Preparing your high-fidelity PDF...', 'info');
      
      const element = document.getElementById('invoiceCard');
      const invoiceNum = '<?php echo htmlspecialchars((string)($invoice['invoice_number'] ?? 'INV-' . $orderId)); ?>';
      
      const opt = {
        margin:       [10, 10],
        filename:     `Invoice-${invoiceNum}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 2, 
            useCORS: true,
            logging: false,
            letterRendering: true,
            allowTaint: true
        },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };

      html2pdf().set(opt).from(element).save().then(() => {
        showToast('PDF downloaded successfully.', 'success');
      }).catch(err => {
        console.error('PDF Error:', err);
        showToast('Failed to generate PDF. Trying print fallback...', 'warning');
        window.print();
      });
    });

    // TOAST SYSTEM
    function showToast(message, type = 'success') {
      const container = document.getElementById('toastContainer');
      const toast = document.createElement('div');
      toast.className = `toast toast--${type}`;
      
      // Select simple text color based on type for border if needed
      if (type === 'danger') toast.style.borderLeftColor = 'var(--color-danger)';
      if (type === 'info') toast.style.borderLeftColor = 'var(--color-info)';
      if (type === 'warning') toast.style.borderLeftColor = 'var(--color-warning)';

      toast.textContent = message;
      container.appendChild(toast);

      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
      }, 3500);
    }

    // Close dropdown on click away
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.status-update')) {
        document.getElementById('statusDropdown')?.classList.remove('is-open');
      }
    });
  </script>
</body>
</html>
