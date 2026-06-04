<?php
/**
 * Sweets Website
 * =============================================================
 * File: invoice.php
 * Description: Printable Order Invoice
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/OrderService.php';

$orderId = (int)($_GET['id'] ?? $_GET['order_id'] ?? 0);
$orderService = new OrderService();
$order = $orderId ? $orderService->getOrderDetails($orderId) : null;

if (!$order) {
    die("Order not found.");
}

// Security: Optional check if logged in user matches order user
// if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== (int)$order['user_id']) {
//     die("Unauthorized access.");
// }

$items = $order['items'] ?? [];
$totalAmount = (float)($order['total_amount'] ?? 0);
$shippingCharges = (float)($order['shipping_charges'] ?? 0);
$discountAmount = (float)($order['discount_amount'] ?? 0);
$taxAmount = (float)($order['tax_amount'] ?? ($totalAmount * 0.18));
$subtotal = (float)($order['subtotal'] ?? ($totalAmount - $shippingCharges + $discountAmount - $taxAmount));

$orderNumber = $order['order_number'] ?? '#VK-' . str_pad((string)$order['id'], 8, '0', STR_PAD_LEFT);
$orderDate = date('d M Y', strtotime($order['created_at'] ?? 'now'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $orderNumber; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            font-family: 'Inter', sans-serif;
            color: #333;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        .invoice-header {
            border-bottom: 2px solid #8a2c22;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .brand-logo {
            max-width: 200px;
        }
        .invoice-title {
            color: #8a2c22;
            font-weight: 800;
            text-transform: uppercase;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .summary-box {
            float: right;
            width: 300px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .total-row {
            border-top: 2px solid #8a2c22;
            margin-top: 10px;
            padding-top: 10px;
            font-weight: 800;
            font-size: 1.2rem;
            color: #8a2c22;
        }
        @media (max-width: 767.98px) {
            /* Table Card Flip */
            .table thead {
                display: none;
            }
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            .table tr {
                margin-bottom: 15px;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 10px 15px;
                background: #fff;
            }
            .table td {
                text-align: right !important;
                padding: 8px 0 !important;
                border: none !important;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .table td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #6c757d;
            }
            .table td strong {
                text-align: right;
            }
            .summary-box {
                width: 100%;
                float: none;
            }
        }
        @media print {
            .no-print {
                display: none;
            }
            .invoice-box {
                border: none;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>

<div class="container my-5">
    <div class="no-print mb-4 text-center">
        <button onclick="window.print()" class="btn btn-primary btn-lg" style="background-color: #8a2c22; border: none;">
            <i class="bi bi-printer me-2"></i> Print Invoice
        </button>
        <a href="order-details.php?id=<?php echo $orderId; ?>" class="btn btn-outline-secondary btn-lg ms-2">
            Back to Order
        </a>
    </div>

    <div class="invoice-box">
        <div class="invoice-header d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-start text-center text-md-start">
            <div class="mb-3 mb-md-0">
                <img src="assets/images/logo-1.png" alt="Vijaya Karadant" class="brand-logo mb-2">
                <p class="mb-0 small">Amingad, Bagalkot, Karnataka - 587112</p>
                <p class="mb-0 small">Email: support@vijayakaradant.in</p>
                <p class="mb-0 small">Phone: +91 7259699366</p>
            </div>
            <div class="text-center text-md-end">
                <h1 class="invoice-title">Invoice</h1>
                <p class="mb-0"><strong>Invoice No:</strong> <?php echo $orderNumber; ?></p>
                <p class="mb-0"><strong>Date:</strong> <?php echo $orderDate; ?></p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12 col-md-6 mb-4 mb-md-0 text-center text-md-start">
                <h6 class="text-muted text-uppercase small fw-bold">Billed To:</h6>
                <p class="fw-bold mb-1"><?php echo htmlspecialchars($order['full_name'] ?? 'Customer'); ?></p>
                <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($order['address'] ?? '')); ?></p>
                <p class="mb-0 small">Phone: <?php echo htmlspecialchars($order['phone'] ?? ''); ?></p>
            </div>
            <div class="col-12 col-md-6 text-center text-md-end">
                <h6 class="text-muted text-uppercase small fw-bold">Order Summary:</h6>
                <p class="mb-0 small"><strong>Order ID:</strong> <?php echo htmlspecialchars($orderNumber); ?></p>
                <p class="mb-0 small"><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? 'Online'); ?></p>
                <p class="mb-0 small"><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status'] ?? 'Paid'); ?></p>
            </div>
        </div>

        <table class="table table-bordered mb-4">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th>Product Description</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): ?>
                <tr>
                    <td data-label="#" class="text-center"><?php echo $index + 1; ?></td>
                    <td data-label="Product">
                        <div class="text-md-start">
                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                            <br><small class="text-muted">SKU: <?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></small>
                        </div>
                    </td>
                    <td data-label="Price" class="text-center">₹<?php echo number_format((float)$item['price_at_time'], 2); ?></td>
                    <td data-label="Qty" class="text-center"><?php echo (int)$item['quantity']; ?></td>
                    <td data-label="Total" class="text-end">₹<?php echo number_format((float)$item['price_at_time'] * (int)$item['quantity'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="row justify-content-end">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="summary-box w-100">
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Shipping</span>
                        <span><?php echo $shippingCharges > 0 ? '₹' . number_format($shippingCharges, 2) : 'FREE'; ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Discount</span>
                        <span>-₹<?php echo number_format($discountAmount, 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Tax (18% GST)</span>
                        <span>₹<?php echo number_format($taxAmount, 2); ?></span>
                    </div>
                    <div class="total-row summary-item">
                        <span>Grand Total</span>
                        <span>₹<?php echo number_format($totalAmount, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-5 border-top text-center text-muted small">
            <p>Thank you for choosing Vijaya Karadant. We hope you enjoy your sweets!</p>
            <p class="mb-0">This is a computer-generated invoice and does not require a signature.</p>
        </div>
    </div>
</div>

<script>
    // Auto-trigger print dialog
    window.onload = function() {
        // Optional: window.print();
    }
</script>
</body>
</html>
