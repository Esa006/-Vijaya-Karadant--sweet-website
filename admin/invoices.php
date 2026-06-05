<?php
/**
 * Sweets Website
 * =============================================================
 * File: invoices.php
 * Description: Invoice Management Listing
 * Author: Antigravity - Senior Backend Engineer
 * Version: 1.0.0
 * =============================================================
 */

$pageStyles = ['assets/css/admin/products.css'];
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once REPOS_PATH . '/InvoiceRepository.php';
require_once REPOS_PATH . '/OrderRepository.php';

$invoiceRepo = new InvoiceRepository();
$orderRepo   = new OrderRepository();

// Get all orders that have invoices, or all orders and check for invoices
// For better UX, let's list all orders and show if an invoice is "Generated" or "Pending"
// Get unified data
$dataList = $invoiceRepo->getAllInvoicesWithOrders(200);

$paymentLabels = [
    'online'   => ['label' => 'Online Paid', 'cls' => 'text-success'],
    'cod'      => ['label' => 'COD',         'cls' => 'text-warning'],
    'refunded' => ['label' => 'Refunded',    'cls' => 'text-danger'],
];
?>

<div class="main-content products-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 products-content-body">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 mb-3 border-bottom px-4 mx-n4">
            <div>
                <h2 class="fw-bold mb-0 products-page-title">Invoice Management</h2>
                <p class="text-muted small mb-0">Track and manage official billing documents.</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn rounded-2 d-flex align-items-center products-outline-btn products-export-btn">
                    <i class="bi bi-printer me-2 fs-5"></i> Print Summary
                </button>
            </div>
        </div>

        <div class="px-2 pb-5">
            <!-- Filter Row -->
            <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 mb-5">
                <div class="input-group products-search-group" style="max-width:320px;">
                    <span class="input-group-text bg-transparent border-0 pe-1 products-search-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                        </svg>
                    </span>
                    <input type="text" id="invoiceSearch"
                        class="form-control border-0 shadow-none bg-transparent ps-2 placeholder-muted products-filter-input"
                        placeholder="Search Invoice # or Customer...">
                </div>
            </div>

            <div class="table-responsive products-table-wrapper">
                <table class="table align-middle mb-0 products-mobile-card-grid" id="invoicesTable">
                    <thead class="products-table-head">
                        <tr>
                            <th class="ps-4 py-3">Invoice / Order #</th>
                            <th class="py-3">Customer</th>
                            <th class="py-3 text-center d-none d-md-table-cell">Amount</th>
                            <th class="py-3 text-center d-none d-md-table-cell">Payment</th>
                            <th class="py-3 text-center">Order Status</th>
                            <th class="py-3 text-center d-none d-md-table-cell">Date</th>
                            <th class="py-3 text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="products-table-body">
                        <?php if (!empty($dataList)): ?>
                            <?php foreach ($dataList as $ord): 
                                $status      = strtolower($ord['order_status'] ?? 'pending');
                                $statusClass = 'products-status-out';
                                if ($status === 'delivered') $statusClass = 'products-status-in';
                                elseif (in_array($status, ['pending', 'processing', 'shipped', 'paid'])) $statusClass = 'products-status-low';

                                $paymentMethod = strtolower($ord['payment_method'] ?? 'online');
                                $payInfo = $paymentLabels[$paymentMethod] ?? ['label' => ucfirst($paymentMethod), 'cls' => 'text-muted'];
                                
                                $invoiceNum = $ord['invoice_number'] ?? null;
                                $displayNum = $invoiceNum ? $invoiceNum : '#' . ($ord['order_number'] ?? $ord['order_id']);
                                $isGenerated = !empty($invoiceNum);
                            ?>
                                <tr class="product-row" data-search="<?php echo strtolower($displayNum . ' ' . ($ord['customer_name'] ?? '')); ?>">
                                    <td class="ps-3 border-0 py-3 td-info">
                                        <div class="fw-bold <?php echo $isGenerated ? 'text-primary' : 'text-dark'; ?>">
                                            <?php echo $displayNum; ?>
                                        </div>
                                        <?php if (!$isGenerated): ?>
                                            <span class="badge bg-warning-subtle text-warning x-small" style="font-size:0.65rem">PENDING GENERATION</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 td-customer">
                                        <div class="fw-bold text-dark">
                                            <a href="customer-details.php?id=<?php echo (int)($ord['user_id'] ?? 0); ?>" class="text-decoration-none text-dark hover-primary">
                                                <?php echo htmlspecialchars((string)($ord['customer_name'] ?? 'Guest')); ?>
                                            </a>
                                        </div>
                                        <div class="text-muted small"><?php echo htmlspecialchars((string)($ord['customer_email'] ?? '')); ?></div>
                                    </td>
                                    <td class="border-0 py-3 text-center td-amount d-none d-md-table-cell" data-label="Amount">
                                        <span class="fw-bolder text-dark">&#8377; <?php echo number_format($ord['total_amount'], 2); ?></span>
                                    </td>
                                    <td class="border-0 py-3 text-center td-pay d-none d-md-table-cell" data-label="Payment">
                                        <span class="small fw-semibold <?php echo $payInfo['cls']; ?>"><?php echo $payInfo['label']; ?></span>
                                    </td>
                                    <td class="border-0 py-3 text-center td-status" data-label="Status">
                                        <span class="d-inline-block fw-bold text-center products-status-pill <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td class="border-0 py-3 text-center td-date d-none d-md-table-cell" data-label="Date">
                                        <span class="small text-muted"><?php echo date('d M, Y', strtotime($ord['invoice_date'] ?? $ord['created_at'])); ?></span>
                                    </td>
                                    <td class="text-center pe-3 border-0 py-3 td-actions">
                                        <a href="invoice.php?id=<?php echo $ord['order_id']; ?>" target="_blank"
                                           class="btn btn-sm <?php echo $isGenerated ? 'btn-outline-primary' : 'btn-outline-warning'; ?> rounded-pill px-3 inv-action-btn">
                                            <i class="bi bi-receipt me-1"></i> <?php echo $isGenerated ? 'View Invoice' : 'Generate &amp; View'; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No records available for invoicing.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('invoiceSearch')?.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.product-row').forEach(row => {
        const text = row.getAttribute('data-search') || '';
        row.style.display = text.includes(term) ? '' : 'none';
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

<style>
/* ====================================================
   INVOICES TABLE — AMAZON-STYLE MOBILE CARD LAYOUT
   ==================================================== */
@media (max-width: 767.98px) {

    /* Hide table header */
    #invoicesTable .products-table-head {
        display: none !important;
    }

    /* Table / tbody / tr / td all become block */
    #invoicesTable,
    #invoicesTable tbody,
    #invoicesTable .product-row,
    #invoicesTable .product-row td {
        display: block !important;
        width: 100% !important;
    }

    /* Each row = Amazon-style card */
    #invoicesTable .product-row {
        background: #fff;
        border: 1px solid #fee7d6;
        border-radius: 14px;
        padding: 14px;
        margin-bottom: 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        position: relative;
    }

    /* Strip default td padding/borders */
    #invoicesTable .product-row td {
        border: none !important;
        padding: 0 !important;
        text-align: left !important;
    }

    /* ── Invoice # block: top, full width ── */
    #invoicesTable .product-row .td-info {
        padding-bottom: 10px !important;
        border-bottom: 1px solid #f3ede7 !important;
        margin-bottom: 8px;
        word-break: break-word;
    }

    #invoicesTable .product-row .td-info .fw-bold {
        font-size: 14px;
        line-height: 1.4;
    }

    /* ── Customer block ── */
    #invoicesTable .product-row .td-customer {
        padding-bottom: 8px !important;
        border-bottom: 1px solid #f8f4f0 !important;
        margin-bottom: 6px;
    }

    #invoicesTable .product-row .td-customer .fw-bold {
        font-size: 14px;
    }

    #invoicesTable .product-row .td-customer .text-muted {
        font-size: 11px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
    }

    /* ── Hidden columns revealed as labeled key/value rows ── */
    #invoicesTable .product-row .d-none.d-md-table-cell {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0 !important;
        border-bottom: 1px solid #f8f4f0 !important;
        font-size: 13px;
    }

    /* ── Status row ── */
    #invoicesTable .product-row .td-status {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0 !important;
        border-bottom: 1px solid #f8f4f0 !important;
        font-size: 13px;
    }

    /* data-label pseudo-element */
    #invoicesTable .product-row td[data-label]::before {
        content: attr(data-label);
        font-size: 11px;
        font-weight: 700;
        color: #8B2E2E;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        flex-shrink: 0;
        margin-right: 10px;
        opacity: 0.85;
    }

    /* Status pill: no min-width on mobile */
    #invoicesTable .product-row .products-status-pill {
        min-width: auto !important;
        font-size: 11px;
        padding: 4px 10px;
    }

    /* ── Actions: full-width button ── */
    #invoicesTable .product-row .td-actions {
        padding-top: 10px !important;
    }

    #invoicesTable .product-row .td-actions .inv-action-btn {
        display: block;
        width: 100%;
        text-align: center;
        padding: 10px 0;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }
}

/* Very small phones (320px) */
@media (max-width: 380px) {
    #invoicesTable .product-row {
        padding: 12px 10px;
        border-radius: 12px;
    }

    #invoicesTable .product-row .td-info .fw-bold {
        font-size: 13px;
    }

    #invoicesTable .product-row .td-customer .fw-bold {
        font-size: 13px;
    }

    /* Page header: stack buttons */
    .products-page .d-flex.flex-column.flex-md-row .d-flex.gap-2 {
        width: 100%;
    }

    .products-page .d-flex.flex-column.flex-md-row .d-flex.gap-2 .btn {
        width: 100%;
        justify-content: center;
    }

    /* Filter search: full width */
    .products-page .products-search-group {
        max-width: 100% !important;
        width: 100%;
    }
}
</style>
