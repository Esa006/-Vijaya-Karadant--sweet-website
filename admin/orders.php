<?php
/**
 * Sweets Website
 * =============================================================
 * File: orders.php
 * Description: Order Management — Premium Luxury Theme
 * Author: Antigravity - Senior Backend Engineer
 * Version: 3.0.0
 * =============================================================
 */

$pageStyles = ['assets/css/admin/pages/orders-modern.css'];
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once REPOS_PATH . '/OrderRepository.php';
require_once SERVICES_PATH . '/OrderService.php';

$orderService = new OrderService();
$orderRepo    = new OrderRepository();

$stats = $orderService->getOrderStats();
// Get more orders for pagination demo
$orders = $orderRepo->getAllOrders(100);

/**
 * Mapping for Status Modern Badges
 */
function getOrderStatusBadge($status) {
    $status = strtolower($status);
    $map = [
        'pending'    => 'badge-pending',
        'paid'       => 'badge-processing',
        'shipped'    => 'badge-processing',
        'delivered'  => 'badge-delivered',
        'cancelled'  => 'badge-cancelled'
    ];
    $cls = $map[$status] ?? 'bg-light';
    return '<span class="status-badge-modern ' . $cls . '">' . ucfirst($status) . '</span>';
}

function getPaymentStatusBadge($status) {
    $status = strtolower($status);
    $cls = ($status === 'paid') ? 'badge-paid' : 'badge-unpaid';
    return '<span class="status-badge-modern ' . $cls . '">' . ucfirst($status) . '</span>';
}
?>

<div class="main-content orders-page-modern">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body px-4 pb-5">
        
        <!-- Page Header & Global Actions -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center py-4 mb-2 gap-3">
            <div>
                <h1 class="fw-bold h2 mb-0" style="color:var(--orders-brand-primary);">Orders</h1>
            </div>
            <div>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-modern btn-export shadow-sm dropdown-toggle" type="button" id="exportOrdersBtn" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download me-2"></i> Export Orders
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="border-radius: 12px; font-size: 0.9rem;">
                        <li><a class="dropdown-item py-2" href="#" id="exportCsvBtn"><i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i> Export as CSV</a></li>
                        <li><a class="dropdown-item py-2" href="#" id="exportPdfBtn"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i> Export as PDF</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Stat Cards Row -->
        <div class="row g-4 mb-5">
            <!-- Total Orders -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card-modern d-flex justify-content-between align-items-center" onclick="applyStatusFilter('all')" style="cursor:pointer;">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Total Orders</div>
                        <h2 class="fw-black mb-0" style="font-size: 2rem;"><?php echo number_format($stats['total']); ?></h2>
                        <p class="text-muted small mt-2 mb-0">Lifetime orders across all categories.</p>
                    </div>
                    <div class="stat-icon-box" style="background:#EEF2FF; color:#6366F1;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
            <!-- Pending -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card-modern d-flex justify-content-between align-items-center" onclick="applyStatusFilter('pending')" style="cursor:pointer;">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Pending</div>
                        <h2 class="fw-black mb-0" style="font-size: 2rem;"><?php echo number_format($stats['pending']); ?></h2>
                        <p class="text-muted small mt-2 mb-0">Awaiting payment or initial processing.</p>
                    </div>
                    <div class="stat-icon-box" style="background:#FFF7ED; color:#F97316;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
            <!-- Processing -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card-modern d-flex justify-content-between align-items-center" onclick="applyStatusFilter('processing')" style="cursor:pointer;">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Processing</div>
                        <h2 class="fw-black mb-0" style="font-size: 2rem;"><?php echo number_format($stats['processing']); ?></h2>
                        <p class="text-muted small mt-2 mb-0">Orders currently being packed.</p>
                    </div>
                    <div class="stat-icon-box" style="background:#F0FDF4; color:#22C55E;">
                        <i class="bi bi-gear-wide-connected"></i>
                    </div>
                </div>
            </div>
            <!-- Delivered -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card-modern d-flex justify-content-between align-items-center" onclick="applyStatusFilter('delivered')" style="cursor:pointer;">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Delivered</div>
                        <h2 class="fw-black mb-0" style="font-size: 2rem;"><?php echo number_format($stats['delivered']); ?></h2>
                        <p class="text-muted small mt-2 mb-0">Successfully fulfilled to customers.</p>
                    </div>
                    <div class="stat-icon-box" style="background:#EEF2FF; color:#6366F1;">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Table Controls -->
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
            <!-- Search -->
            <div class="position-relative flex-grow-1" style="min-width: 200px; max-width: 320px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search search-icon-modern" viewBox="0 0 16 16" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--orders-brand-primary); z-index: 5;">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                </svg>
                <input type="text" id="orderSearchModern" class="form-control search-input-modern shadow-none" placeholder="Search by name or Order ID...">
            </div>
            <!-- Date Range Picker (Calendar Add) -->
            <div class="position-relative" style="min-width: 240px;">
                <i class="bi bi-calendar3 search-icon-modern" style="left: 15px; top: 50%; transform: translateY(-50%); color: var(--orders-brand-primary);"></i>
                <input type="text" id="dateRangePickerModern" class="form-control search-input-modern shadow-none ps-5" placeholder="Filter by date range..." style="background: white; border-color: var(--table-border); cursor: pointer;">
                <button type="button" id="clearDateBtn" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted px-2" style="display: none; z-index: 5;">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
            <!-- Status Filter -->
            <div style="min-width: 140px;">
                <select id="statusFilterModern" class="form-select shadow-none btn-modern border-1" style="border-color:var(--table-border);">
                    <option value="all">Status All</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <!-- Bulk Update (auto width, never full width) -->
            <div class="ms-auto">
                <button id="bulkUpdateBtn" class="btn btn-modern btn-bulk shadow-sm px-4" disabled style="white-space: nowrap;">
                    <i class="bi bi-pencil-square me-2"></i>Bulk Update
                </button>
            </div>
        </div>

        <!-- Main Orders Table -->
        <div class="orders-table-card">
            <div class="p-4 bg-white border-bottom">
                <h4 class="fw-bold mb-0" style="color:var(--orders-brand-primary);">Recent Orders</h4>
            </div>
            <div class="table-responsive">
                <table class="table modern-table align-middle mb-0 orders-mobile-card-grid" id="ordersTable">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" class="form-check-input shadow-none" id="selectAllOrders"></th>
                            <th>Customer & Order ID</th>
                            <th class="d-none d-lg-table-cell">Items</th>
                            <th>Total Amount</th>
                            <th class="text-center d-none d-lg-table-cell">Payment Status</th>
                            <th class="text-center">Order Status</th>
                            <th class="text-center d-none d-lg-table-cell">Date & Time</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $ord): 
                                $orderNum = $ord['order_number'] ?? $ord['id'];
                                $customerName = htmlspecialchars($ord['customer_name'] ?? 'Guest');
                                $customerEmail = htmlspecialchars($ord['customer_email'] ?? '');
                                $itemCount = (int)($ord['item_count'] ?? 0);
                                $total = (float)$ord['total_amount'];
                                $orderStatus = strtolower($ord['status']);
                                $paymentStatus = strtolower($ord['payment_status'] ?? 'unpaid');
                                $createdAt = date('d M Y', strtotime($ord['created_at']));
                                $createdAtTime = date('h:i A', strtotime($ord['created_at']));
                            ?>
                            <tr class="order-row-item" 
                                data-status="<?php echo $orderStatus; ?>" 
                                data-search="<?php echo strtolower($orderNum . ' ' . $customerName); ?>"
                                data-date="<?php echo date('Y-m-d', strtotime($ord['created_at'])); ?>">
                                <td class="td-check"><input type="checkbox" class="form-check-input shadow-none order-check"></td>
                                <td class="td-info">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-md-none p-2 rounded-2 bg-light text-accent">
                                            <i class="bi bi-bag-check-fill fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold mb-0">
                                                <a href="order-details.php?id=<?php echo $ord['id']; ?>" class="order-id-link">#<?php echo $orderNum; ?></a>
                                            </div>
                                            <div class="fw-semibold text-dark">
                                                <a href="customer-details.php?id=<?php echo $ord['user_id']; ?>" class="text-decoration-none text-dark hover-primary">
                                                    <?php echo $customerName; ?>
                                                </a>
                                            </div>
                                            <div class="text-muted x-small d-none d-md-block" style="font-size: 0.75rem;"><?php echo $customerEmail; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Items" class="td-items d-none d-lg-table-cell">
                                    <div class="fw-bold">
                                        <?php echo $itemCount; ?> item<?php echo $itemCount !== 1 ? 's' : ''; ?>
                                    </div>
                                </td>
                                <td data-label="Total Amount" class="td-price">
                                    <div class="fw-bold">₹ <?php echo number_format($total, 2); ?></div>
                                </td>
                                <td data-label="Payment" class="text-center td-pay d-none d-lg-table-cell">
                                    <?php echo getPaymentStatusBadge($paymentStatus); ?>
                                </td>
                                <td data-label="Status" class="text-center td-status">
                                    <?php echo getOrderStatusBadge($orderStatus); ?>
                                </td>
                                <td data-label="Date" class="text-center td-date d-none d-lg-table-cell">
                                    <div class="fw-semibold text-dark"><?php echo $createdAt; ?></div>
                                    <div class="text-muted x-small" style="font-size: 0.75rem;"><?php echo $createdAtTime; ?></div>
                                </td>
                                <td class="text-end td-actions">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button class="action-icon-btn edit-order-trigger" 
                                                data-id="<?php echo $ord['id']; ?>" 
                                                data-num="<?php echo $orderNum; ?>"
                                                data-status="<?php echo $orderStatus; ?>"
                                                data-payment="<?php echo $paymentStatus; ?>"
                                                data-customer="<?php echo $customerName; ?>"
                                                data-total="₹ <?php echo number_format($total, 2); ?>"
                                                data-paymethod="<?php echo ucfirst($ord['payment_method'] ?? 'online'); ?>"
                                                data-tracking="<?php echo htmlspecialchars((string)($ord['tracking_id'] ?? '')); ?>"
                                                data-partner="<?php echo htmlspecialchars((string)($ord['delivery_partner'] ?? '')); ?>"
                                                data-deliverydate="<?php echo htmlspecialchars((string)($ord['estimated_delivery_date'] ?? '')); ?>"
                                                data-adminnotes="<?php echo htmlspecialchars((string)($ord['admin_notes'] ?? '')); ?>"
                                                title="Quick Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="order-details.php?id=<?php echo $ord['id']; ?>" class="action-icon-btn" title="View Full Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button class="action-icon-btn delete-order-btn" data-id="<?php echo $ord['id']; ?>" data-num="<?php echo $orderNum; ?>" title="Delete Order">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">No orders found.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Footer -->
            <div class="p-4 bg-white border-top d-flex flex-column flex-lg-row justify-content-between align-items-center gap-3">
                <div class="text-muted small pagination-text-modern">
                    Page 1 of <?php echo ceil(count($orders) / 10); ?> · 1–10 of <?php echo count($orders); ?> orders
                </div>
                <nav id="ordersPaginationNav">
                    <ul class="pagination pagination-sm mb-0 pagination-modern">
                        <li class="page-item" id="prevPageBtn">
                            <a class="page-link shadow-none" href="javascript:void(0)" onclick="changePage(currentPage - 1)">Back</a>
                        </li>
                        <?php 
                        $totalPages = ceil(count($orders) / 10);
                        $maxVisible = 5;
                        for($i = 1; $i <= $totalPages; $i++): 
                            // Only show first 5 and the last page if total > 5
                            if ($totalPages > 7 && $i > $maxVisible && $i < $totalPages) {
                                if ($i == $maxVisible + 1) echo '<li class="page-item px-2 text-muted pagination-dots">...</li>';
                                continue;
                            }
                        ?>
                            <li class="page-item <?php echo $i === 1 ? 'active' : ''; ?> pagination-number" data-page="<?php echo $i; ?>">
                                <a class="page-link shadow-none" href="javascript:void(0)" onclick="changePage(<?php echo $i; ?>)"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $totalPages <= 1 ? 'disabled' : ''; ?>" id="nextPageBtn">
                            <a class="page-link shadow-none" href="javascript:void(0)" onclick="changePage(currentPage + 1)">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- BULK UPDATE MODAL -->
<div class="modal fade" id="orderBulkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold">Bulk Update Status</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <p class="text-muted mb-4" id="bulkSelectedCount">0 orders selected</p>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-accent">New Status</label>
                    <select id="bulkOrderStatus" class="form-select shadow-none border-1 p-2" style="border-color: #8e4422; border-radius: 8px;">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="confirmBulkUpdate">Apply Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- QUICK EDIT MODAL (MODERNIZED) -->
<div class="modal fade" id="quickEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: #faf8f5;">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <div>
                    <h4 class="modal-title fw-bold text-dark mb-1">Edit Order Status</h4>
                    <p class="text-muted small mb-0">Order <span id="modalOrderNum" class="fw-bold"></span></p>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickEditForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="modal-body p-4">
                    <!-- SUMMARY CARD -->
                    <div class="p-3 p-md-4 mb-4 summary-card-details" style="background: #fff; border: 1.5px solid #8e4422; border-radius: 12px; position: relative;">
                        <!-- Right accent bar mimicking screenshot -->
                        <div class="d-none d-md-block" style="position: absolute; right: -8px; top: 12px; bottom: 12px; width: 6px; background: #fbae5d; border-radius: 10px;"></div>
                        
                        <div class="row g-3 g-md-4">
                            <div class="col-12 col-md-6">
                                <label class="small fw-bold text-accent mb-1" style="color: #e8890c; letter-spacing: 0.05em; text-transform: uppercase;">Customer Name</label>
                                <div id="summaryCustomerName" class="fw-bold text-dark fs-6 fs-md-5">Rahul Sharma</div>
                            </div>
                            <div class="col-12 col-md-6 text-start text-md-end">
                                <label class="small fw-bold text-accent mb-1" style="color: #e8890c; letter-spacing: 0.05em; text-transform: uppercase;">Total Amount</label>
                                <div id="summaryTotalAmount" class="fw-bold text-dark fs-6 fs-md-5">₹ 1,250.00</div>
                            </div>
                            <div class="col-6 col-md-6">
                                <label class="small fw-bold text-accent mb-1" style="color: #e8890c; letter-spacing: 0.05em; text-transform: uppercase;">Payment Method</label>
                                <div id="summaryPaymentMethod" class="fw-bold text-dark small">UPI</div>
                            </div>
                            <div class="col-6 col-md-6 text-end">
                                <label class="small fw-bold text-accent mb-1 d-block" style="color: #e8890c; letter-spacing: 0.05em; text-transform: uppercase;">Current Status</label>
                                <div id="summaryCurrentStatus">
                                    <span class="badge" style="background: #dbeafe; color: #1e40af; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.75rem;">Processing</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="order_id" id="editOrderId">
                    <input type="hidden" name="action" value="edit_order">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-accent" style="color: #e8890c;">Order Status*</label>
                            <select name="status" id="editOrderStatus" class="form-select shadow-none border-1 p-2" style="border-color: #8e4422; border-radius: 8px;">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid (Processing)</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-accent" style="color: #e8890c;">Payment Status*</label>
                            <select name="payment_status" id="editPaymentStatus" class="form-select shadow-none border-1 p-2" style="border-color: #8e4422; border-radius: 8px;">
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-accent" style="color: #e8890c;">Tracking ID</label>
                            <input type="text" name="tracking_id" id="editTrackingId" class="form-control shadow-none border-1 p-2" placeholder="e.g. TRK-001" style="border-color: #8e4422; border-radius: 8px;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-accent" style="color: #e8890c;">Delivery Partner</label>
                            <select name="delivery_partner" id="editDeliveryPartner" class="form-select shadow-none border-1 p-2" style="border-color: #8e4422; border-radius: 8px;">
                                <option value="">Select Partner</option>
                                <option value="Delivery">Delivery</option>
                                <option value="BlueDart">BlueDart</option>
                                <option value="FedEx">FedEx</option>
                                <option value="DTDC">DTDC</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-accent" style="color: #e8890c;">Estimated Delivery Date*</label>
                            <div class="input-group">
                                <input type="date" name="estimated_delivery_date" id="editDeliveryDate" class="form-control shadow-none border-1 p-2" style="border-color: #8e4422; border-radius: 8px; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                <span class="input-group-text bg-white" style="border-color: #8e4422; border-radius: 8px; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                    <i class="bi bi-calendar-check text-muted"></i>
                                </span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-accent" style="color: #e8890c;">Admin Notes</label>
                            <textarea name="admin_notes" id="editAdminNotes" class="form-control shadow-none border-1 p-2" placeholder="Add note...." rows="2" style="border-color: #8e4422; border-radius: 8px;"></textarea>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">Notify Customer</div>
                                    <div class="text-muted small">Send status update notification to the customer via email/SMS</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input shadow-none" type="checkbox" name="notify_customer" id="notifyCustomerToggle" style="width: 45px; height: 24px; cursor: pointer;" checked>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-3">
                    <button type="button" class="btn btn-modern px-4 py-2" data-bs-dismiss="modal" style="background: #dadada; color: #444; border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-modern px-5 py-2 fw-bold" id="saveEditBtn" style="background: #bf5b10; color: white; border-radius: 8px;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div id="ordersToastContainer" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;"></div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Toast Utility ──────────────────────────────────────────
    function showToast(msg, type = 'success') {
        const container = document.getElementById('ordersToastContainer');
        const el = document.createElement('div');
        const bg = type === 'error' ? '#dc2626' : '#16a34a';
        el.style.cssText = `background:${bg};color:#fff;padding:12px 18px;border-radius:10px;
                             box-shadow:0 4px 15px rgba(0,0,0,.15);display:flex;align-items:center;
                             gap:10px;font-size:.9rem;font-weight:600;min-width:280px; transition: opacity 0.4s;`;
        el.innerHTML = `<span>${msg}</span>`;
        container.appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 3500);
    }

    // ── Table Filtering ─────────────────────────────────────────
    const searchInput = document.getElementById('orderSearchModern');
    const statusFilter = document.getElementById('statusFilterModern');
    const rows = document.querySelectorAll('.order-row-item');

    // ── Date Picker (Flatpickr) ──────────────────────────────────
    let dateRange = { start: null, end: null };
    const datePickerInput = document.getElementById("dateRangePickerModern");
    
    if (datePickerInput && typeof flatpickr !== 'undefined') {
        const datePicker = flatpickr(datePickerInput, {
            mode: "range",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            allowInput: true,
            static: true, // Render calendar inside the container
            onClose: function(selectedDates) {
                if (selectedDates.length === 2) {
                    dateRange.start = new Date(selectedDates[0].setHours(0,0,0,0));
                    dateRange.end = new Date(selectedDates[1].setHours(23,59,59,999));
                    document.getElementById('clearDateBtn').style.display = 'block';
                } else {
                    dateRange.start = null;
                    dateRange.end = null;
                    if (selectedDates.length === 0) document.getElementById('clearDateBtn').style.display = 'none';
                }
                currentPage = 1;
                filterTable();
            }
        });

        // Open picker when clicking the calendar icon
        document.querySelector('.bi-calendar3')?.addEventListener('click', () => datePicker.open());

        document.getElementById('clearDateBtn')?.addEventListener('click', function() {
            datePicker.clear();
            dateRange = { start: null, end: null };
            this.style.display = 'none';
            currentPage = 1;
            filterTable();
        });
    }


    searchInput?.addEventListener('input', () => { currentPage = 1; filterTable(); });
    statusFilter?.addEventListener('change', () => { currentPage = 1; filterTable(); });

    // ── Client Side Pagination ──────────────────────────────────
    let currentPage = 1;
    const itemsPerPage = 10;
    const totalOrders = <?php echo count($orders); ?>;

    window.changePage = function(page) {
        const totalP = Math.ceil(totalOrders / itemsPerPage);
        if (page < 1 || page > totalP) return;
        
        currentPage = page;
        filterTable();
        
        // Scroll to table
        document.querySelector('.orders-table-card').scrollIntoView({ behavior: 'smooth' });
    };

    function filterTable() {
        const term = searchInput.value.toLowerCase();
        const status = statusFilter.value.toLowerCase();
        const hasDateRange = dateRange.start && dateRange.end;
        const isFiltering = (term !== '' || status !== 'all' || hasDateRange);
        let visibleCount = 0;
        let matchIndex = 0;


        rows.forEach(row => {
            const rowSearch = row.getAttribute('data-search');
            const rowStatus = row.getAttribute('data-status');
            const rowDateStr = row.getAttribute('data-date');
            const rowDate = new Date(rowDateStr);
            
            const matchesSearch = rowSearch.includes(term);
            const matchesStatus = (status === 'all') || 
                                  (status === 'processing' && (rowStatus === 'paid' || rowStatus === 'shipped')) || 
                                  (rowStatus === status);

            let matchesDate = true;
            if (hasDateRange) {
                matchesDate = (rowDate >= dateRange.start && rowDate <= dateRange.end);
            }

            if (matchesSearch && matchesStatus && matchesDate) {
                matchIndex++;
                // If not filtering, handle pagination
                if (!isFiltering) {
                    const start = (currentPage - 1) * itemsPerPage;
                    const end = start + itemsPerPage;
                    if (matchIndex > start && matchIndex <= end) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                } else {
                    row.style.display = '';
                }
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update pagination UI
        const pagination = document.querySelector('.pagination-modern')?.closest('nav');
        const paginationText = document.querySelector('.pagination-text-modern');
        
        if (isFiltering) {
            if (pagination) pagination.style.display = 'none';
            if (paginationText) paginationText.textContent = `Showing ${visibleCount} matches`;
        } else {
            if (pagination) pagination.style.display = '';
            
            const start = (currentPage - 1) * itemsPerPage + 1;
            const end = Math.min(currentPage * itemsPerPage, totalOrders);
            const totalP = Math.ceil(totalOrders / itemsPerPage);

            if (paginationText) {
                paginationText.textContent = `Page ${currentPage} of ${totalP} · ${start}–${end} of ${totalOrders} orders`;
            }

            // Update active state in pagination buttons
            document.querySelectorAll('.pagination-number').forEach(li => {
                li.classList.toggle('active', parseInt(li.dataset.page) === currentPage);
            });
            
            const prevBtn = document.getElementById('prevPageBtn');
            const nextBtn = document.getElementById('nextPageBtn');
            if (prevBtn) prevBtn.classList.toggle('disabled', currentPage === 1);
            if (nextBtn) nextBtn.classList.toggle('disabled', currentPage === totalP);
        }
    }

    // Initialize first page view
    filterTable();

    window.applyStatusFilter = function(status) {
        if (statusFilter) {
            statusFilter.value = status;
            // Highlight filter
            statusFilter.style.backgroundColor = '#fff7ed';
            setTimeout(() => statusFilter.style.backgroundColor = '', 500);
        }
        filterTable();
        
        // Scroll to table
        document.querySelector('.orders-table-card').scrollIntoView({ behavior: 'smooth' });
    };

    // Auto-apply search from URL (from Global Search)
    const urlParams = new URLSearchParams(window.location.search);
    const urlSearch = urlParams.get('search');
    if (urlSearch && searchInput) {
        searchInput.value = urlSearch;
        setTimeout(filterTable, 100);
    }

    // ── Bulk & Select All ───────────────────────────────────────
    const selectAllCheck = document.getElementById('selectAllOrders');
    const rowChecks = document.querySelectorAll('.order-check');
    
    selectAllCheck?.addEventListener('change', function() {
        rowChecks.forEach(check => check.checked = selectAllCheck.checked);
        updateBulkBtn();
    });

    rowChecks.forEach(check => {
        check.addEventListener('change', updateBulkBtn);
    });

    function updateBulkBtn() {
        const checked = document.querySelectorAll('.order-check:checked');
        const btn = document.getElementById('bulkUpdateBtn');
        btn.disabled = checked.length === 0;
    }

    // Bulk Update Logic
    const bulkBtn = document.getElementById('bulkUpdateBtn');
    const bulkModal = new bootstrap.Modal(document.getElementById('orderBulkModal'));

    bulkBtn?.addEventListener('click', () => {
        const checked = document.querySelectorAll('.order-check:checked');
        document.getElementById('bulkSelectedCount').textContent = `${checked.length} orders selected`;
        bulkModal.show();
    });

    document.getElementById('confirmBulkUpdate')?.addEventListener('click', async function() {
        const checked = document.querySelectorAll('.order-check:checked');
        const ids = Array.from(checked).map(cb => cb.closest('tr').querySelector('.edit-order-trigger').dataset.id);
        const status = document.getElementById('bulkOrderStatus').value;
        const btn = this;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

        try {
            const res = await fetch('api/v1/order-bulk.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids, status })
            });
            const result = await res.json();

            if (result.success) {
                showToast(result.message);
                bulkModal.hide();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast(result.error || 'Update failed', 'error');
            }
        } catch (err) {
            showToast('Network error', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Apply Changes';
        }
    });

    // ── Action Handlers ─────────────────────────────────────────
    
    // Quick Edit Logic
    const editModalEl = document.getElementById('quickEditModal');
    const editModal = new bootstrap.Modal(editModalEl);
    const editForm = document.getElementById('quickEditForm');

    document.querySelectorAll('.edit-order-trigger').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;
            
            // Header
            document.getElementById('modalOrderNum').textContent = '#' + data.num;
            document.getElementById('editOrderId').value = data.id;

            // Summary Card
            document.getElementById('summaryCustomerName').textContent = data.customer;
            document.getElementById('summaryTotalAmount').textContent = data.total;
            document.getElementById('summaryPaymentMethod').textContent = data.paymethod;
            
            const currentBadgeContainer = document.getElementById('summaryCurrentStatus');
            const statusUpper = data.status.charAt(0).toUpperCase() + data.status.slice(1);
            currentBadgeContainer.innerHTML = `<span class="badge" style="background: #dbeafe; color: #1e40af; padding: 8px 16px; border-radius: 6px; font-weight: 600;">${statusUpper}</span>`;

            // Form Fields
            document.getElementById('editOrderStatus').value = data.status;
            document.getElementById('editPaymentStatus').value = data.payment;
            document.getElementById('editTrackingId').value = data.tracking;
            document.getElementById('editDeliveryPartner').value = data.partner;
            document.getElementById('editDeliveryDate').value = data.deliverydate;
            document.getElementById('editAdminNotes').value = data.adminnotes;
            
            editModal.show();
        });
    });

    editForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const saveBtn = document.getElementById('saveEditBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        try {
            const fd = new FormData(this);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('api/v1/orders.php', { 
                method: 'POST', 
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: fd 
            });
            const result = await res.json();

            if (result.status === 'success') {
                showToast(result.message);
                editModal.hide();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast(result.message || 'Update failed', 'error');
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Save Changes';
            }
        } catch (err) {
            showToast('Infrastructure error', 'error');
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save Changes';
        }
    });

    // Delete Logic
    document.querySelectorAll('.delete-order-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const num = this.dataset.num;

            if (!confirm(`Are you sure you want to PERMANENTLY delete Order #${num}? This action cannot be undone.`)) return;

            try {
                const fd = new FormData();
                fd.append('action', 'delete_order');
                fd.append('order_id', id);

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('api/v1/orders.php', { 
                    method: 'POST', 
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: fd 
                });
                const result = await res.json();

                if (result.status === 'success') {
                    showToast(result.message);
                    this.closest('tr').style.opacity = '0.5';
                    this.closest('tr').style.pointerEvents = 'none';
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(result.message || 'Delete failed', 'error');
                }
            } catch (err) {
                showToast('Infrastructure error', 'error');
            }
        });
    });

    // ── Export Functionality ───────────────────────────────────
    
    // CSV Export
    function exportToCSV() {
        const visibleRows = [...rows].filter(r => r.style.display !== 'none');
        if (visibleRows.length === 0) {
            showToast('No orders found to export', 'error');
            return;
        }

        let csv = 'Order ID,Customer,Items,Total,Order Status,Payment Status,Date,Time\n';
        visibleRows.forEach(row => {
            const id = row.querySelector('.order-id-link').textContent.trim();
            const customer = row.querySelector('.fw-bold.text-dark').textContent.trim();
            const items = row.querySelector('td:nth-child(4) .fw-bold').textContent.trim();
            const total = row.querySelector('td:nth-child(5) .fw-bold').textContent.trim().replace('₹ ', '').replace(',', '');
            const status = row.querySelectorAll('.status-badge-modern')[1].textContent.trim();
            const payment = row.querySelectorAll('.status-badge-modern')[0].textContent.trim();
            const date = row.querySelector('.fw-semibold.text-dark').textContent.trim();
            const time = row.querySelector('.text-muted.x-small').textContent.trim();

            csv += `"${id}","${customer}","${items}","${total}","${status}","${payment}","${date}","${time}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", `Sweets_Orders_${new Date().toISOString().slice(0,10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showToast('CSV exported successfully');
    }

    // PDF Export
    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'pt', 'a4'); // Landscape
        
        const visibleRows = [...rows].filter(r => r.style.display !== 'none');
        if (visibleRows.length === 0) {
            showToast('No orders found to export', 'error');
            return;
        }

        const data = visibleRows.map(row => [
            row.querySelector('.order-id-link').textContent.trim(),
            row.querySelector('.fw-bold.text-dark').textContent.trim(),
            row.querySelector('td:nth-child(4) .fw-bold').textContent.trim(),
            row.querySelector('td:nth-child(5) .fw-bold').textContent.trim(),
            row.querySelectorAll('.status-badge-modern')[1].textContent.trim(),
            row.querySelectorAll('.status-badge-modern')[0].textContent.trim(),
            row.querySelector('.fw-semibold.text-dark').textContent.trim()
        ]);

        doc.setFontSize(18);
        doc.setTextColor(74, 26, 4); // Brand Primary
        doc.text("Order Report - Vijaya Karadant Sweets", 40, 45);
        
        doc.setFontSize(10);
        doc.setTextColor(100);
        doc.text(`Generated on: ${new Date().toLocaleString()}`, 40, 65);

        doc.autoTable({
            head: [['Order ID', 'Customer', 'Items', 'Amount', 'Order Status', 'Payment', 'Date']],
            body: data,
            startY: 80,
            theme: 'striped',
            headStyles: { fillColor: [74, 26, 4], textColor: [255, 255, 255], fontStyle: 'bold' },
            styles: { fontSize: 9, cellPadding: 8 }
        });

        doc.save(`Sweets_Orders_${new Date().toISOString().slice(0,10)}.pdf`);
        showToast('PDF exported successfully');
    }

    document.getElementById('exportCsvBtn')?.addEventListener('click', (e) => { e.preventDefault(); exportToCSV(); });
    document.getElementById('exportPdfBtn')?.addEventListener('click', (e) => { e.preventDefault(); exportToPDF(); });
});
</script>

<?php require_once 'includes/footer.php'; ?>
