<?php
/**
 * Sweets Website
 * =============================================================
 * File: delivery-details.php
 * Description: High-fidelity Delivery Timeline & Shipment Details
 * Version: 3.0.0
 * =============================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$orderId) {
    die("Invalid Order ID");
}

$orderRepo = new OrderRepository();
$order = $orderRepo->getById($orderId);

if (!$order) {
    die("Order not found");
}

$items = $orderRepo->getItemsByOrderId($orderId);
$shipment = $orderRepo->getShipmentDetails($orderId);
$timeline = $orderRepo->getDeliveryTimeline($orderId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Details - Order #<?php echo htmlspecialchars($order['order_number'] ?? 'N/A'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-maroon: #6B201F;
            --accent-orange: #9E331F;
            --bg-light: #f9fafb;
            --card-header-bg: #FFEFE8;
            --text-dark: #1a1a1a;
            --text-gray: #4b5563;
            --border-color: #fed7aa; /* orange-200 */
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
        }

        /* Container & Layout */
        .page-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Breadcrumbs */
        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-bottom: 1.5rem;
        }
        .breadcrumb-nav a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s;
        }
        .breadcrumb-nav a:hover {
            color: var(--primary-maroon);
        }
        .breadcrumb-nav .active {
            font-weight: 700;
            color: var(--primary-maroon);
        }

        /* Page Header */
        .page-header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        @media (min-width: 768px) {
            .page-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--primary-maroon);
            margin: 0;
        }

        /* Header Buttons */
        .btn-action-outline {
            padding: 0.5rem 1rem;
            border: 1px solid var(--primary-maroon);
            color: var(--primary-maroon);
            border-radius: 0.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            background: transparent;
        }
        .btn-action-outline:hover {
            background-color: #fff1f2;
            color: var(--primary-maroon);
        }
        .btn-action-primary {
            padding: 0.5rem 1.5rem;
            background-color: var(--accent-orange);
            color: white;
            border: none;
            border-radius: 0.25rem;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn-action-primary:hover {
            background-color: #852a1a;
            color: white;
        }

        /* Cards */
        .card-custom {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .card-header-custom {
            background-color: var(--card-header-bg);
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        .card-header-custom h2 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--accent-orange);
            margin: 0;
        }
        .card-body-custom {
            padding: 1.5rem;
        }

        /* Form Controls */
        .label-custom {
            font-size: 0.75rem;
            font-weight: 600;
            color: #c2410c; /* orange-700 */
            text-transform: uppercase;
            margin-bottom: 0.25rem;
            display: block;
        }
        .input-custom {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        .input-custom:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 1px #f97316;
        }
        .input-group-custom {
            display: flex;
        }
        .input-group-text-custom {
            display: inline-flex;
            align-items: center;
            padding: 0 0.75rem;
            border: 1px solid #d1d5db;
            border-right: none;
            border-radius: 0.375rem 0 0 0.375rem;
            background-color: #f9fafb;
            color: #6b7280;
            font-size: 0.875rem;
        }
        .input-group-custom .input-custom {
            border-radius: 0 0.375rem 0.375rem 0;
        }

        /* Product Table */
        .responsive-table {
            width: 100%;
            border-collapse: collapse;
        }
        .responsive-table thead {
            background-color: var(--card-header-bg);
        }
        .responsive-table th {
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--accent-orange);
            text-align: left;
        }
        .responsive-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .product-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .product-img-wrapper {
            width: 3rem;
            height: 3rem;
            background-color: #ffedd5;
            border-radius: 0.25rem;
            overflow: hidden;
            flex-shrink: 0;
        }
        .product-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-name {
            font-weight: 700;
            font-size: 0.875rem;
        }
        .product-sku {
            font-size: 0.625rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        /* Table Responsive Styles */
        @media (max-width: 767px) {
            .responsive-table thead {
                display: none;
            }
            .responsive-table tr {
                display: block;
                border: 1px solid var(--border-color);
                margin: 1rem;
                border-radius: 0.5rem;
                background: white;
            }
            .responsive-table td {
                display: block;
                text-align: right;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #fff7ed;
            }
            .responsive-table td:last-child {
                border-bottom: none;
            }
            .responsive-table td::before {
                content: attr(data-label);
                float: left;
                font-weight: 700;
                color: var(--accent-orange);
                margin-right: 0.5rem;
            }
            .responsive-table td[data-label="Product"] {
                text-align: left;
                background: #fff7ed;
            }
            .responsive-table td[data-label="Product"]::before {
                display: none;
            }
        }

        /* Summary Bar */
        .summary-bar {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            border-top: 1px solid #f3f4f6;
        }
        @media (min-width: 640px) {
            .summary-bar {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }
        .summary-item {
            font-weight: 500;
            color: #374151;
        }
        .summary-value {
            font-weight: 700;
        }
        .total-amount {
            font-size: 1.125rem;
        }

        /* Customer Detail Items */
        .customer-detail-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .customer-icon-box {
            width: 2.5rem;
            height: 2.5rem;
            background-color: var(--primary-maroon);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        .customer-label-small {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 0;
        }
        .customer-value-bold {
            font-weight: 700;
            margin-bottom: 0;
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding-top: 0.5rem;
        }
        .timeline-item {
            position: relative;
            display: flex;
            gap: 1rem;
            padding-bottom: 2rem;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-line {
            position: absolute;
            left: 0.75rem;
            top: 1.5rem;
            width: 2px;
            height: calc(100% - 1.5rem);
            background-color: #e5e7eb;
        }
        .timeline-dot {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            flex-shrink: 0;
            font-size: 0.875rem;
        }
        .dot-completed {
            background-color: #16a34a;
            color: white;
        }
        .dot-active {
            background-color: var(--accent-orange);
            color: white;
        }
        .dot-pending {
            background-color: white;
            border: 2px solid #e5e7eb;
            color: #d1d5db;
        }
        .timeline-content {
            flex: 1;
        }
        .timeline-title {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .timeline-time {
            font-size: 0.75rem;
            color: #6b7280;
        }
        .timeline-note {
            margin-top: 0.5rem;
            padding: 0.75rem;
            background-color: white;
            border: 1px solid #f3f4f6;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            color: #4b5563;
            line-height: 1.5;
        }

        /* Status colors */
        .text-delivered { color: #16a34a; }
        .text-shipped { color: #2563eb; }
        .text-pending { color: #d97706; }
        .text-cancelled { color: #dc2626; }

        /* Toast & Modal */
        #toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--primary-maroon);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1050;
        }
        .modal-content {
            border-radius: 0.75rem;
            border: 1px solid var(--border-color);
        }
    </style>
</head>
<body>

    <div class="page-container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumb-nav">
            <a href="delivery.php">Delivery</a>
            <span>></span>
            <a href="#">Shipment Details</a>
            <span>></span>
            <span class="active">Order #<?php echo htmlspecialchars($order['order_number']); ?></span>
        </nav>

        <!-- Header -->
        <div class="page-header">
            <h1 class="page-title">Delivery details</h1>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn-action-outline" onclick="window.open('invoice.php?id=<?php echo $orderId; ?>', '_blank')">
                    <i class="bi bi-download"></i>
                    Download Invoice
                </button>
                <button class="btn-action-outline" data-bs-toggle="modal" data-bs-target="#assignCourierModal">
                    <i class="bi bi-person-plus"></i>
                    Assign Courier
                </button>
                <button class="btn-action-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                    Update Status
                </button>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-12 col-lg-8">
                <!-- Shipment Overview -->
                <div class="card-custom">
                    <div class="card-header-custom">
                        <h2>Shipment Overview</h2>
                    </div>
                    <div class="card-body-custom">
                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <label class="label-custom">Courier Partner</label>
                                <input type="text" class="input-custom" value="<?php echo htmlspecialchars($shipment['courier_name'] ?? 'Not Assigned'); ?>" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="label-custom">Tracking Number</label>
                                <input type="text" class="input-custom" value="<?php echo htmlspecialchars($shipment['tracking_id'] ?? 'N/A'); ?>" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="label-custom">Dispatch date</label>
                                <input type="text" class="input-custom" value="<?php echo $shipment['dispatch_date'] ? date('d/m/Y', strtotime($shipment['dispatch_date'])) : 'N/A'; ?>" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="label-custom">Estimate delivery date</label>
                                <input type="text" class="input-custom" value="<?php echo $shipment['estimated_delivery'] ? date('d/m/Y', strtotime($shipment['estimated_delivery'])) : 'N/A'; ?>" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="label-custom">Shipping method</label>
                                <input type="text" class="input-custom" value="<?php echo htmlspecialchars($shipment['shipping_method'] ?? 'Standard Delivery'); ?>" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="label-custom">Delivery charges</label>
                                <div class="input-group-custom">
                                    <span class="input-group-text-custom">₹</span>
                                    <input type="text" class="input-custom" value="<?php echo number_format($order['shipping_charges'] ?? 0, 2); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Table -->
                <div class="card-custom">
                    <table class="responsive-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td data-label="Product">
                                    <div class="product-info">
                                        <div class="product-img-wrapper">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="Product">
                                            <?php else: ?>
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-orange-100">
                                                    <i class="bi bi-box-fill text-orange-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <div class="product-sku">
                                                SKU : <?php echo htmlspecialchars($item['slug'] ?? 'N/A'); ?>
                                                <?php if (!empty($item['variant_label'])): ?>
                                                    | <?php echo htmlspecialchars($item['variant_label']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center" data-label="Quantity">
                                    <span class="fw-medium"><?php echo $item['quantity']; ?></span>
                                </td>
                                <td class="text-center" data-label="Unit Price">
                                    <span class="text-gray-900">₹ <?php echo number_format($item['price_at_time'] ?? $item['price'] ?? 0); ?></span>
                                </td>
                                <td class="text-center" data-label="Subtotal">
                                    <span class="fw-bold">₹ <?php echo number_format(($item['price_at_time'] ?? $item['price'] ?? 0) * $item['quantity']); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="summary-bar">
                        <p class="summary-item mb-0">Payment Method : <span class="summary-value"><?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?></span></p>
                        <p class="summary-item mb-0">Total Amount : <span class="summary-value total-amount text-dark">₹ <?php echo number_format($order['total_amount'], 2); ?></span></p>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-12 col-lg-4">
                <!-- Customer Details -->
                <div class="card-custom" style="background-color: rgba(255, 239, 232, 0.3);">
                    <div class="card-header-custom">
                        <h2>Customer Details</h2>
                    </div>
                    <div class="card-body-custom">
                        <div class="customer-detail-row">
                            <div class="customer-icon-box">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <p class="customer-label-small">Name</p>
                                <p class="customer-value-bold"><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></p>
                            </div>
                        </div>
                        <div class="customer-detail-row">
                            <div class="customer-icon-box">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div>
                                <p class="customer-label-small">Phone Number</p>
                                <p class="customer-value-bold"><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <div class="customer-detail-row">
                            <div class="customer-icon-box">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div>
                                <p class="customer-label-small">Email Address</p>
                                <p class="customer-value-bold"><?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <hr class="border-orange-100 my-4">
                        <div class="customer-detail-row">
                            <div class="customer-icon-box">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div>
                                <p class="customer-label-small">Shipping Address</p>
                                <p class="customer-value-bold text-sm" style="font-size: 0.875rem; line-height: 1.5;">
                                    <?php 
                                    $addr = array_filter([
                                        $order['address_line1'],
                                        $order['address_line2'],
                                        $order['city'],
                                        $order['state'],
                                        $order['zip_code'],
                                        $order['country']
                                    ]);
                                    echo htmlspecialchars(implode(', ', $addr) ?: 'Address Not Provided');
                                    ?>
                                </p>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <div style="width: 1rem; height: 1rem; background-color: #dc2626; border-radius: 2px;"></div>
                                    <span style="font-size: 0.75rem; color: #4b5563; font-weight: 500; font-style: italic;">Billing address is same</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Timeline -->
                <div class="card-custom" style="background-color: rgba(255, 239, 232, 0.3);">
                    <div class="card-header-custom">
                        <h2>Delivery Timeline</h2>
                    </div>
                    <div class="card-body-custom">
                        <div class="timeline">
                            <?php 
                            // Map of statuses for timeline
                            $allStatuses = ['DELIVERED', 'OUT_FOR_DELIVERY', 'SHIPPED', 'PACKED', 'PENDING'];
                            $currentStatus = strtoupper($order['status']);
                            
                            // Get unique events from DB
                            $eventsByStatus = [];
                            foreach ($timeline as $e) {
                                $eventsByStatus[strtoupper($e['status'])] = $e;
                            }
                            
                            foreach ($allStatuses as $status): 
                                $event = $eventsByStatus[$status] ?? null;
                                $isCompleted = $event !== null;
                                $isActive = ($currentStatus === $status);
                                
                                $dotClass = 'dot-pending';
                                $icon = 'bi-circle';
                                if ($isCompleted) {
                                    $dotClass = 'dot-completed';
                                    $icon = 'bi-check-circle';
                                }
                                if ($isActive) {
                                    $dotClass = 'dot-active';
                                    $icon = ($status === 'OUT_FOR_DELIVERY') ? 'bi-truck' : 'bi-check-circle-fill';
                                }
                            ?>
                            <div class="timeline-item">
                                <?php if ($status !== 'PENDING'): ?>
                                    <div class="timeline-line"></div>
                                <?php endif; ?>
                                <div class="timeline-dot <?php echo $dotClass; ?>">
                                    <i class="bi <?php echo $icon; ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <p class="timeline-title <?php echo $isActive ? 'text-dark' : ($isCompleted ? 'text-gray' : 'text-muted'); ?>" 
                                       style="<?php echo !$isCompleted ? 'color: #9ca3af;' : ''; ?>">
                                        <?php echo ucfirst(strtolower(str_replace('_', ' ', $status))); ?>
                                    </p>
                                    <?php if ($event): ?>
                                        <p class="timeline-time"><?php echo date('M d, Y, h:i A', strtotime($event['created_at'])); ?></p>
                                        <?php if (!empty($event['location'])): ?>
                                            <p class="mb-1 text-muted" style="font-size: 0.75rem;"><i class="bi bi-pin-map-fill me-1"></i><?php echo htmlspecialchars($event['location']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($event['description'])): ?>
                                            <div class="timeline-note">
                                                <?php echo htmlspecialchars($event['description']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Assign Courier Modal -->
    <div class="modal fade" id="assignCourierModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="assignForm" action="api/v1/shipments.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                    <input type="hidden" name="action" value="assign">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Assign Courier</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="label-custom">Courier Partner</label>
                            <select name="courier_name" class="input-custom" required>
                                <option value="Blue Dart">Blue Dart</option>
                                <option value="Delhivery">Delhivery</option>
                                <option value="DTDC">DTDC</option>
                                <option value="FedEx">FedEx</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="label-custom">Tracking ID</label>
                            <input type="text" name="tracking_id" class="input-custom" placeholder="e.g. BD-8902762" required>
                        </div>
                        <div class="mb-3">
                            <label class="label-custom">Estimated Delivery Date</label>
                            <input type="date" name="estimated_delivery" class="input-custom" required>
                        </div>
                    </div>
                    <div class="p-4 bg-gray-50 border-top d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-action-primary">Assign Courier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="statusForm" action="api/v1/tracking.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Update Status</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="label-custom">New Status</label>
                            <select name="status" class="input-custom" required>
                                <option value="PACKED">Packed</option>
                                <option value="SHIPPED">Shipped</option>
                                <option value="OUT_FOR_DELIVERY">Out for Delivery</option>
                                <option value="DELIVERED">Delivered</option>
                                <option value="CANCELLED">Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="label-custom">Location / Hub</label>
                            <input type="text" name="location" class="input-custom" placeholder="e.g. Sorting Facility, Mumbai">
                        </div>
                        <div class="mb-3">
                            <label class="label-custom">Description / Notes</label>
                            <textarea name="description" class="input-custom" rows="3" placeholder="e.g. Package left the sorting center."></textarea>
                        </div>
                    </div>
                    <div class="p-4 bg-gray-50 border-top d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-action-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="toast"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(msg) {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3000);
        }

        async function handleForm(id) {
            const form = document.getElementById(id);
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success) {
                        showToast('Updated successfully!');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(data.error || 'Update failed');
                    }
                } catch (err) {
                    showToast('System error occurred');
                }
            });
        }

        handleForm('assignForm');
        handleForm('statusForm');
    </script>
</body>
</html>
