<?php
/**
 * Sweets Website
 * =============================================================
 * File: other-products.php
 * Description: Orders Management Dashboard (Maroon Theme)
 * =============================================================
 */

$pageStyles = [
    'assets/css/admin/products.css',
    'assets/css/admin/pages/customers.css'
];

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';

/* ── Static demo data (replace with DB queries when ready) ── */
$stats = [
    'total'      => 1428,
    'pending'    => 12,
    'processing' => 24,
    'delivered'  => 1280,
];

$initial_orders = [
    ['id' => 'ORD-4091', 'customer' => 'Rahul Sharma',  'email' => 'rahul.s@example.com', 'items' => 3, 'amount' => 1250, 'payment' => 'Paid',   'status' => 'Processing', 'date' => 'Oct 24, 2023 10:00 AM'],
    ['id' => 'ORD-4092', 'customer' => 'Sneha Patel',   'email' => 'sneha.patel@mail.com', 'items' => 1, 'amount' => 1580, 'payment' => 'Unpaid', 'status' => 'Pending',    'date' => 'Oct 24, 2023 11:30 AM'],
    ['id' => 'ORD-4093', 'customer' => 'Amit Kumar',    'email' => 'amit.kumar@domain.in', 'items' => 4, 'amount' => 890,  'payment' => 'Paid',   'status' => 'Delivered',  'date' => 'Oct 23, 2023 09:15 AM'],
    ['id' => 'ORD-4094', 'customer' => 'Priya Singh',   'email' => 'priya.s@example.com',  'items' => 6, 'amount' => 2400, 'payment' => 'Paid',   'status' => 'Cancelled',  'date' => 'Oct 23, 2023 02:45 PM'],
    ['id' => 'ORD-4095', 'customer' => 'Vikram Reddy',  'email' => 'v.reddy@workspace.in', 'items' => 2, 'amount' => 1600, 'payment' => 'Paid',   'status' => 'Pending',    'date' => 'Oct 22, 2023 05:20 PM'],
    ['id' => 'ORD-4096', 'customer' => 'Anjali Gupta',  'email' => 'anjali.g@test.com',    'items' => 1, 'amount' => 450,  'payment' => 'Paid',   'status' => 'Processing', 'date' => 'Oct 22, 2023 12:10 PM']
];

/* ── Status badge helper (Maroon Theme compatible) ── */
function getStatusBadge(string $s): string {
    $map = [
        'Processing' => 'badge-active-maroon',
        'Pending'    => 'badge-inactive-maroon',
        'Delivered'  => 'badge-active-maroon',
        'Cancelled'  => 'badge-blocked-maroon'
    ];
    $cls = $map[$s] ?? 'badge-inactive-maroon';
    return "<span class=\"{$cls}\" style=\"font-size: 0.72rem; padding: 5px 12px; border-radius: 20px; font-weight: 700; text-transform: uppercase;\">{$s}</span>";
}
?>

<div class="main-content products-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 customers-content-body">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 mb-4 border-bottom px-4 mx-n4 position-sticky products-header-sticky">
            <div>
                <h1 class="page-title" style="font-size: 1.7rem; font-weight: 700; color: #7B1F1F; margin: 0;">Order Management</h1>
            </div>
            <div class="d-flex gap-2">
                <button class="btn-export-maroon" onclick="exportOrdersCSV()">
                    <i class="bi bi-file-earmark-spreadsheet"></i> CSV
                </button>
                <button class="btn-export-maroon" style="background: #1a1a1a; border-color: #1a1a1a; color: #fff;" onclick="exportOrdersPDF()">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
            </div>
        </div>

        <div class="container-fluid px-0">
            <!-- Stat Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Orders</div>
                        <div class="stat-value" id="statTotal"><?php echo number_format($stats['total']); ?></div>
                    </div>
                    <div class="stat-icon icon-orange"><i class="bi bi-bag-fill"></i></div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Pending Orders</div>
                        <div class="stat-value" id="statPending"><?php echo number_format($stats['pending']); ?></div>
                    </div>
                    <div class="stat-icon icon-pink"><i class="bi bi-clock-history"></i></div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-label">In Processing</div>
                        <div class="stat-value" id="statProcessing"><?php echo number_format($stats['processing']); ?></div>
                    </div>
                    <div class="stat-icon icon-green"><i class="bi bi-arrow-repeat"></i></div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Delivered</div>
                        <div class="stat-value" id="statDelivered"><?php echo number_format($stats['delivered']); ?></div>
                    </div>
                    <div class="stat-icon icon-purple"><i class="bi bi-box-seam"></i></div>
                </div>
            </div>

            <!-- Refined Filter Bar -->
            <div class="filter-container mt-0 mb-4">
                <div class="filter-left">
                    <div class="search-box-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #AE4B3A; font-size: 1.1rem; pointer-events: none;">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                        </svg>
                        <input type="text" id="orderSearch" placeholder="Search by Order ID, Customer, or Email..." oninput="filterOrders()" />
                    </div>
                    
                    <select class="filter-item-select" id="orderStatusFilter" onchange="filterOrders()">
                        <option value="">Status All</option>
                        <option value="Processing">Processing</option>
                        <option value="Pending">Pending</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>

                    <button class="filter-item-btn" onclick="document.getElementById('dateInput').showPicker?.() || document.getElementById('dateInput').click()">
                        <i class="bi bi-calendar3"></i> 
                        <span>Order Date</span>
                        <input type="date" id="dateInput" style="position:absolute;opacity:0;width:0;height:0;pointer-events:none;" onchange="filterOrders()" />
                    </button>
                </div>
            </div>

            <!-- Section Title -->
            <div class="section-title mb-3" style="font-size: 1.1rem; font-weight: 700; color: #2d2d2d;">Recent Orders</div>

            <!-- DESKTOP TABLE VIEW -->
            <div class="table-card-maroon d-none d-md-block">
                <div class="table-responsive">
                    <table class="inventory-table-maroon" id="ordersTable">
                        <thead>
                            <tr>
                                <th style="width:50px;"><input type="checkbox" class="form-check-input" onchange="toggleAllOrders(this)" /></th>
                                <th>Order ID</th>
                                <th>Customer Details</th>
                                <th class="text-center">Items</th>
                                <th class="text-center">Total Amount</th>
                                <th class="text-center">Status</th>
                                <th>Date & Time</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ordersBody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination-bar-maroon">
                    <span class="page-info" id="pageInfoDesktop" style="font-size: 0.8rem; color: #8a8a8a;"></span>
                    <div class="page-controls" id="paginationDesktop"></div>
                </div>
            </div>

            <!-- MOBILE CARD VIEW -->
            <div class="mobile-cards d-md-none" id="orderMobileCards">
                <!-- Populated by JS -->
            </div>
            <div class="mobile-pagination d-md-none mt-3">
                <div class="pagination-bar-maroon" style="background:#fff; border-radius:12px; border:1px solid #e2d8ce;">
                    <div class="page-controls w-100 justify-content-between" id="paginationMobile"></div>
                </div>
            </div>

            <!-- No Results Placeholder -->
            <div id="noOrdersResults" class="text-center py-5 table-card-maroon" style="display: none;">
                 <div class="mb-3 text-muted" style="font-size: 3rem;">
                     <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                         <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                     </svg>
                 </div>
                 <h5 class="fw-bold">No orders found</h5>
                <p class="text-muted">Try adjusting your search criteria or filters.</p>
            </div>
        </div>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div class="toast-container-maroon" id="toastContainer"></div>

<script>
let orders = <?php echo json_encode($initial_orders); ?>;
let filteredOrdersList = [...orders];
let currentPageOrders = 1;
const perPageOrders = 8;

document.addEventListener('DOMContentLoaded', () => {
    renderOrders();
});

function getBadgeOrders(status) {
    const map = {
        'Processing': 'badge-active-maroon',
        'Pending': 'badge-inactive-maroon',
        'Delivered': 'badge-active-maroon',
        'Cancelled': 'badge-blocked-maroon'
    };
    const cls = map[status] || 'badge-inactive-maroon';
    return `<span class="${cls}" style="font-size: 0.72rem; padding: 5px 12px; border-radius: 20px; font-weight: 700; text-transform: uppercase;">${status}</span>`;
}

function renderOrders() {
    const start = (currentPageOrders - 1) * perPageOrders;
    const pageData = filteredOrdersList.slice(start, start + perPageOrders);
    const total = filteredOrdersList.length;
    const pages = Math.ceil(total / perPageOrders) || 1;

    // Desktop
    const tbody = document.getElementById('ordersBody');
    tbody.innerHTML = pageData.map(ord => `
        <tr>
            <td class="text-center"><input type="checkbox" class="form-check-input order-row-check" /></td>
            <td><span class="fw-bold" style="color:#7B1F1F;">#${ord.id}</span></td>
            <td>
                <div class="cust-name-main">${ord.customer}</div>
                <div class="cust-subtext">${ord.email}</div>
            </td>
            <td class="text-center fw-bold">${ord.items} items</td>
            <td class="text-center fw-bold">₹ ${ord.amount.toLocaleString('en-IN')}</td>
            <td class="text-center">${getBadgeOrders(ord.status)}</td>
            <td style="font-size: 13px;">${ord.date}</td>
            <td>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="javascript:void(0)" class="act-btn-maroon view" onclick="showOrderToast('Order ${ord.id} Details Viewing')"><i class="bi bi-eye"></i></a>
                    <a href="javascript:void(0)" class="act-btn-maroon edit" onclick="showOrderToast('Edit Order ${ord.id}')"><i class="bi bi-pencil-square"></i></a>
                </div>
            </td>
        </tr>
    `).join('');

    // Mobile
    const mc = document.getElementById('orderMobileCards');
    mc.innerHTML = pageData.map(ord => `
        <div class="mob-card-maroon">
            <div class="mob-card-header-maroon">
                <div style="flex:1;">
                    <div class="cust-name-main"><span style="color:#7B1F1F;">#${ord.id}</span> - ${ord.customer}</div>
                    <div class="cust-subtext">${ord.email}</div>
                </div>
                <input type="checkbox" class="form-check-input shadow-none" />
            </div>
            <div class="mob-card-body-maroon">
                <div class="mob-row-maroon"><span class="mob-label-maroon">Amount</span><span class="mob-value-maroon">₹ ${ord.amount.toLocaleString()}</span></div>
                <div class="mob-row-maroon"><span class="mob-label-maroon">Items</span><span class="mob-value-maroon">${ord.items}</span></div>
                <div class="mob-row-maroon"><span class="mob-label-maroon">Status</span>${getBadgeOrders(ord.status)}</div>
                <div class="mob-row-maroon"><span class="mob-label-maroon">Date</span><span class="mob-value-maroon" style="font-size:11px;">${ord.date}</span></div>
            </div>
            <div class="mob-card-footer-maroon">
                <a href="javascript:void(0)" class="mob-act-btn-maroon view" onclick="showOrderToast('Viewing #${ord.id}')"><i class="bi bi-eye"></i> Details</a>
                <a href="javascript:void(0)" class="mob-act-btn-maroon edit" onclick="showOrderToast('Editing #${ord.id}')"><i class="bi bi-pencil-square"></i> Edit</a>
            </div>
        </div>
    `).join('');

    // Pagination
    const range = total === 0 ? '0' : `${start + 1}–${Math.min(start + perPageOrders, total)}`;
    const info = total === 0 ? 'No orders found' : `Page ${currentPageOrders} of ${pages} · ${range} of ${total} orders`;
    document.getElementById('pageInfoDesktop').textContent = info;
    
    buildOrderPagination(pages, 'paginationDesktop');
    buildOrderPagination(pages, 'paginationMobile');

    document.getElementById('noOrdersResults').style.display = total === 0 ? 'block' : 'none';
}

function buildOrderPagination(pages, containerId) {
    const c = document.getElementById(containerId);
    let html = `<button class="page-btn-maroon me-2" onclick="goOrderPage(${currentPageOrders - 1})" ${currentPageOrders === 1 ? 'disabled' : ''}>Back</button>`;
    
    for (let i = 1; i <= pages; i++) {
        if (i === 1 || i === pages || (i >= currentPageOrders - 1 && i <= currentPageOrders + 1)) {
            html += `<button class="page-btn-maroon mx-1 ${i === currentPageOrders ? 'active' : ''}" onclick="goOrderPage(${i})">${i}</button>`;
        } else if (i === currentPageOrders - 2 || i === currentPageOrders + 2) {
            html += `<span class="px-2">...</span>`;
        }
    }
    
    html += `<button class="page-btn-maroon ms-2" onclick="goOrderPage(${currentPageOrders + 1})" ${currentPageOrders === pages ? 'disabled' : ''}>Next</button>`;
    c.innerHTML = html;
}

function goOrderPage(p) {
    const pages = Math.ceil(filteredOrdersList.length / perPageOrders) || 1;
    if (p < 1 || p > pages) return;
    currentPageOrders = p;
    renderOrders();
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function filterOrders() {
    const q = document.getElementById('orderSearch').value.toLowerCase();
    const st = document.getElementById('orderStatusFilter').value;
    
    filteredOrdersList = orders.filter(ord => {
        const matchesQ = !q || ord.id.toLowerCase().includes(q) || ord.customer.toLowerCase().includes(q) || ord.email.toLowerCase().includes(q);
        const matchesSt = !st || ord.status === st;
        return matchesQ && matchesSt;
    });
    
    currentPageOrders = 1;
    renderOrders();
}

function toggleAllOrders(cb) {
    document.querySelectorAll('.order-row-check').forEach(c => c.checked = cb.checked);
}

function showOrderToast(msg, type = 'success') {
    const c = document.getElementById('toastContainer');
    const t = document.createElement('div');
    const icon = type === 'error' ? 'bi-x-circle-fill' : type === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill';
    t.className = `custom-toast-maroon ${type}`;
    t.innerHTML = `<i class="bi ${icon} fs-5"></i><span>${msg}</span>`;
    c.appendChild(t);
    setTimeout(() => {
        t.style.opacity = '0';
        t.style.transform = 'translateX(50px)';
        t.style.transition = 'all 0.4s ease';
        setTimeout(() => t.remove(), 400);
    }, 3000);
}

function exportOrdersCSV() {
    // Robust CSV Generation
    const headers = ['Order ID', 'Customer', 'Email', 'Items', 'Amount', 'Status', 'Date'];
    
    const escapeCSV = (val) => {
        const str = String(val === null || val === undefined ? '' : val);
        if (str.includes(',') || str.includes('"') || str.includes('\n')) {
            return `"${str.replace(/"/g, '""')}"`;
        }
        return str;
    };

    const csvRows = [headers.join(',')];
    filteredOrdersList.forEach(ord => {
        const row = [
            ord.id,
            ord.customer,
            ord.email,
            ord.items,
            ord.amount,
            ord.status,
            ord.date
        ];
        csvRows.push(row.map(escapeCSV).join(','));
    });

    const csvString = csvRows.join('\n');
    const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", `orders_export_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showOrderToast('CSV Exported Successfully');
}

function exportOrdersPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');
    
    // Add Title
    doc.setFontSize(18);
    doc.setTextColor(123, 31, 31); // Maroon #7B1F1F
    doc.text("Order Management Report", 14, 20);
    
    doc.setFontSize(10);
    doc.setTextColor(100);
    doc.text(`Generated on: ${new Date().toLocaleString()}`, 14, 28);
    
    // Prepare Table Data
    const tableBody = filteredOrdersList.map(ord => [
        '#' + ord.id,
        ord.customer,
        ord.email,
        ord.items,
        'INR ' + Number(ord.amount).toLocaleString('en-IN'),
        ord.status,
        ord.date
    ]);

    doc.autoTable({
        startY: 35,
        head: [['Order ID', 'Customer', 'Email', 'Items', 'Amount', 'Status', 'Date']],
        body: tableBody,
        headStyles: { fillColor: [123, 31, 31], fontSize: 10, fontStyle: 'bold' },
        bodyStyles: { fontSize: 8 },
        alternateRowStyles: { fillColor: [245, 245, 245] },
        margin: { top: 35 }
    });

    doc.save(`orders_report_${new Date().toISOString().split('T')[0]}.pdf`);
    showOrderToast('PDF Exported Successfully');
}
</script>

<?php require_once 'includes/footer.php'; ?>
