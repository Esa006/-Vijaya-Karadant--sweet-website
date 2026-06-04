<?php
/**
 * Sweets Website
 * =============================================================
 * File: customers.php
 * Description: High-Fidelity Maroon Theme Customer CRM
 * =============================================================
 */

$pageStyles = [
    'assets/css/admin/products.css',
    'assets/css/admin/pages/admin-customers.css',
    'assets/css/admin/pages/customers.css',
    'assets/css/admin/pages/add-customer.css',
    'assets/css/admin/pages/product-preview.css',
    'assets/css/admin/pages/product-delete.css'
];
$pageScripts = ['assets/js/admin/modals.js'];

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';

require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/Database.php';
require_once REPOS_PATH . '/CustomerRepository.php';

$customerRepo = new CustomerRepository(Database::getInstance());
$dbCustomers = $customerRepo->getAllCustomers();
$stats = $customerRepo->getDashboardStats();

$initial_customers = [];
foreach ($dbCustomers as $c) {
    // Generate initials
    $customerName = $c['name'] ?? 'Unknown User';
    $names = explode(' ', $customerName);
    $initials = '';
    foreach ($names as $n) {
        if (trim($n) !== '') {
            $initials .= strtoupper($n[0]);
        }
    }
    $initials = mb_substr($initials, 0, 2);

    $initial_customers[] = [
        'id' => (int)$c['id'],
        'name' => htmlspecialchars($customerName),
        'email' => htmlspecialchars($c['email'] ?? ''),
        'phone' => !empty($c['phone']) ? htmlspecialchars($c['phone']) : '+91 XX XXXX XXXX',
        'spend' => (float)($c['total_spend'] ?? 0),
        'orders' => (int)($c['total_orders'] ?? 0),
        'status' => !empty($c['status']) ? $c['status'] : 'Active',
        'join' => !empty($c['created_at']) ? date('d M, Y', strtotime($c['created_at'])) : 'N/A',
        'initials' => $initials ?: '?'
    ];
}
?>

<div class="main-content customers-page" style="background-color: #fdfaf7;">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 customers-content-body px-4">
        <!-- Page Header -->
        <div class="row align-items-center mb-4">
            <div class="col-12 col-lg-auto mb-3 mb-lg-0">
                <h1 class="fw-bold mb-0" style="color: #8C3333; font-size: 2.2rem;">Customers</h1>
            </div>
            <div class="col-12 col-lg-auto ms-lg-auto">
                <div class="d-flex flex-wrap gap-2 w-100">
                    <button class="btn-export shadow-sm flex-grow-1 flex-md-grow-0" onclick="exportCSV()">
                        <i class="bi bi-file-earmark-spreadsheet"></i> CSV
                    </button>
                    <button class="btn-export shadow-sm flex-grow-1 flex-md-grow-0"  onclick="exportPDF()">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                    <button class="btn-add-cust shadow-sm flex-grow-1 flex-md-grow-0" type="button" id="openAddCustomerPanel">
                        <i class="bi bi-plus-lg"></i> Add Customer
                    </button>
                </div>
            </div>
        </div>

        <!-- High Fidelity Stat Cards -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="stat-card h-100" onclick="window.location.reload()" style="cursor:pointer;">
                    <div class="stat-info">
                        <span class="stat-label">Total Customers</span>
                        <span class="stat-value" id="statTotal"><?php echo number_format($stats['total_customers']); ?></span>
                        <span class="stat-trend">+12% from last month</span>
                    </div>
                    <div class="stat-icon-wrap icon-blue"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-xl-3">
                <div class="stat-card h-100" onclick="applyStatusFilter('Active')" style="cursor:pointer;">
                    <div class="stat-info">
                        <span class="stat-label">Active Accounts</span>
                        <span class="stat-value" id="statActive"><?php echo number_format($stats['active_accounts']); ?></span>
                        <span class="stat-trend">Purchased in last 90 days</span>
                    </div>
                    <div class="stat-icon-wrap icon-pink"><i class="bi bi-person-check-fill"></i></div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="stat-card h-100" onclick="window.location.href='orders.php'" style="cursor:pointer;">
                    <div class="stat-info">
                        <span class="stat-label">Average Order Value</span>
                        <span class="stat-value" id="statRevenue">₹<?php echo number_format($stats['average_order_value']); ?></span>
                        <span class="stat-trend">Orders currently being packed.</span>
                    </div>
                    <div class="stat-icon-wrap icon-success"><i class="bi bi-currency-rupee"></i></div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="stat-card h-100" onclick="window.location.reload()" style="cursor:pointer;">
                    <div class="stat-info">
                        <span class="stat-label">Returning Customers</span>
                        <span class="stat-value" id="statRetention"><?php echo round($stats['returning_rate']); ?>%</span>
                        <span class="stat-trend">High retention for mithai</span>
                    </div>
                    <div class="stat-icon-wrap icon-indigo"><i class="bi bi-arrow-repeat"></i></div>
                </div>
            </div>
        </div>

        <!-- Refined Filter Bar -->
        <div class="row g-3 mb-4 align-items-center">
            <div class="col-12 col-lg-6">
                <div class="search-box-wrap w-100" style="max-width: 100%;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #AE4B3A; font-size: 1.1rem; pointer-events: none;">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Search by name or email....">
                </div>
            </div>
            
            <div class="col-12 col-sm-6 col-lg-3">
                <select class="filter-item-select w-100" id="statusFilter" onchange="filterData()">
                    <option value="">Status All</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Blocked">Blocked</option>
                </select>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <button class="filter-item-btn w-100 d-flex justify-content-between align-items-center" onclick="document.getElementById('dateInput').showPicker?.() || document.getElementById('dateInput').click()">
                    <span>Join Date</span>
                    <i class="bi bi-calendar3"></i>
                    <input type="date" id="dateInput" style="position:absolute;opacity:0;width:0;height:0;pointer-events:none;" onchange="filterData()" />
                </button>
            </div>
        </div>

        <h3 class="fw-bold mb-3" style="color: #222; font-size: 1.5rem;">Customer Directory</h3>

        <!-- DESKTOP TABLE VIEW -->
        <div class="table-card-maroon d-none d-md-block shadow-sm">
            <div class="table-responsive">
                <table class="inventory-table-maroon" id="mainTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 1%;">
                                <input type="checkbox" class="form-check-input" onchange="toggleAll(this)" style="border-color: #BC5B4A;" />
                            </th>
                            <th>Customer Name</th>
                            <th class="d-none d-xl-table-cell">Email</th>
                            <th>Phone Number</th>
                            <th class="text-center">Total Orders</th>
                            <th class="d-none d-xl-table-cell">Total Spend (₹)</th>
                            <th class="d-none d-xl-table-cell">Last Order</th>
                            <th>Status</th>
                            <th class="d-none d-xl-table-cell">Join Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
            <div class="pagination-bar-maroon">
                <span class="page-info" id="pageInfoDesktop" style="font-size: 0.85rem; color: #666; font-weight: 500;"></span>
                <div class="page-controls" id="paginationDesktop"></div>
            </div>
        </div>

            <!-- MOBILE CARD VIEW -->
            <div class="mobile-cards d-md-none" id="mobileCards">
                <!-- Populated by JS -->
            </div>
            <div class="mobile-pagination d-md-none mt-3">
                <div class="pagination-bar-maroon"
                    style="background:#fff; border-radius:12px; border:1px solid #e2d8ce;">
                    <div class="page-controls w-100 justify-content-between" id="paginationMobile"></div>
                </div>
            </div>

            <!-- No Results Placeholder -->
            <div id="noResults" class="text-center py-5 table-card-maroon" style="display: none;">
                 <div class="mb-3 text-muted" style="font-size: 3rem;">
                     <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                         <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                     </svg>
                 </div>
                <h5 class="fw-bold">No customers found</h5>
                <p class="text-muted">Try adjusting your search criteria or filters.</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Overlay -->
<div class="add-customer-overlay" id="addCustomerOverlay" hidden aria-hidden="true">
    <div class="add-customer-wrapper" role="dialog" aria-modal="true" aria-labelledby="addCustomerOverlayTitle">
        <div class="add-customer-card">
            <div class="add-customer-scroll-track">
                <div class="add-customer-track-bg"></div>
                <div class="add-customer-thumb" id="addCustomerOverlayThumb"></div>
            </div>

            <div class="add-customer-scroll" id="addCustomerOverlayScroll">
                <div class="add-customer-header">
                    <h2 class="add-customer-title" id="addCustomerOverlayTitle">Add Customer</h2>
                    <button class="add-customer-close" type="button" id="addCustomerOverlayClose" aria-label="Close panel">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="add-customer-field">
                    <label class="add-customer-label" for="overlayCustomerName">Full Name</label>
                    <input type="text" id="overlayCustomerName" class="add-customer-input" placeholder="Enter full name*">
                </div>

                <div class="add-customer-field">
                    <div class="add-customer-row-2">
                        <div class="add-customer-col-1-2">
                            <label class="add-customer-label" for="overlayCustomerEmail">Email Address</label>
                            <input type="email" id="overlayCustomerEmail" class="add-customer-input" placeholder="Enter email">
                        </div>
                        <div class="add-customer-col-1-2">
                            <label class="add-customer-label" for="overlayCustomerPhone">Phone Number</label>
                            <input type="text" id="overlayCustomerPhone" class="add-customer-input" placeholder="Enter phone number">
                        </div>
                    </div>
                </div>

                <div class="add-customer-field">
                    <label class="add-customer-label" for="overlayCustomerAddress">Address Line</label>
                    <select id="overlayCustomerAddress" class="add-customer-select">
                        <option value="" disabled selected>Enter address*</option>
                        <option value="address1">123 Main Street, Block A</option>
                        <option value="address2">456 Park Avenue, Suite 10</option>
                        <option value="address3">789 Gandhi Nagar, Sector 5</option>
                    </select>
                </div>

                <div class="add-customer-field">
                    <div class="add-customer-row-3">
                        <div class="add-customer-col-1-3">
                            <label class="add-customer-label" for="overlayCustomerCity">City</label>
                            <input type="text" id="overlayCustomerCity" class="add-customer-input" placeholder="Enter city">
                        </div>
                        <div class="add-customer-col-1-3">
                            <label class="add-customer-label" for="overlayCustomerState">State</label>
                            <input type="text" id="overlayCustomerState" class="add-customer-input" placeholder="Enter state">
                        </div>
                        <div class="add-customer-col-1-3">
                            <label class="add-customer-label" for="overlayCustomerPincode">Pincode</label>
                            <input type="text" id="overlayCustomerPincode" class="add-customer-input" placeholder="Enter pincode">
                        </div>
                    </div>
                </div>

                <div class="add-customer-field">
                    <div class="add-customer-row-2">
                        <div class="add-customer-col-1-2">
                            <label class="add-customer-label" for="overlayCustomerPassword">Password</label>
                            <input type="password" id="overlayCustomerPassword" class="add-customer-input" placeholder="Enter password">
                        </div>
                        <div class="add-customer-col-1-2">
                            <label class="add-customer-label" for="overlayCustomerPasswordConfirm">Confirm Password</label>
                            <input type="password" id="overlayCustomerPasswordConfirm" class="add-customer-input" placeholder="Re-enter password">
                        </div>
                    </div>
                </div>

                <div class="add-customer-toggle-row">
                    <span class="add-customer-toggle-label">Customer Status</span>
                    <label class="add-customer-toggle">
                        <input type="checkbox" id="overlayCustomerStatus" checked>
                        <span class="add-customer-toggle-slider"></span>
                    </label>
                </div>

                <div class="add-customer-field">
                    <div class="add-customer-radio-group">
                        <label class="add-customer-radio">
                            <input type="radio" name="overlayCustomerType" value="new" checked>
                            <span class="add-customer-radio-dot"></span>
                            <span class="add-customer-radio-text">New</span>
                        </label>
                        <label class="add-customer-radio">
                            <input type="radio" name="overlayCustomerType" value="vip">
                            <span class="add-customer-radio-dot"></span>
                            <span class="add-customer-radio-text">VIP</span>
                        </label>
                        <label class="add-customer-radio">
                            <input type="radio" name="overlayCustomerType" value="frequent">
                            <span class="add-customer-radio-dot"></span>
                            <span class="add-customer-radio-text">Frequent Buyer</span>
                        </label>
                    </div>
                </div>

                <div class="add-customer-field">
                    <label class="add-customer-label" for="overlayCustomerNotes">Notes</label>
                    <input type="text" id="overlayCustomerNotes" class="add-customer-input" placeholder="Add notes">
                </div>

                <div class="add-customer-actions">
                    <button class="add-customer-btn add-customer-btn-cancel" type="button" id="addCustomerOverlayCancel">Cancel</button>
                    <button class="add-customer-btn add-customer-btn-save" type="button" id="addCustomerOverlaySave">Save Customer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-maroon">
            <div class="modal-header modal-header-maroon py-3 px-4">
                <h5 class="modal-title fw-bold mb-0">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="editId" />
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Full Name</label>
                        <input type="text" class="form-control rounded-3" id="editName" />
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Email Address</label>
                        <input type="email" class="form-control rounded-3" id="editEmail" />
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted">Phone Number</label>
                        <input type="text" class="form-control rounded-3" id="editPhone" />
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-muted">Status</label>
                        <select class="form-select rounded-3" id="editStatus">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Blocked">Blocked</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 pb-4 border-0 gap-2">
                <button class="btn-secondary-maroon" data-bs-dismiss="modal">Cancel</button>
                <button class="btn-primary-maroon" onclick="saveEdit()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<?php 
// Include Global Modals
require_once 'includes/modals/product-preview.php';
require_once 'includes/modals/delete-confirm.php';
?>

<!-- TOAST CONTAINER -->
<div class="toast-container-maroon" id="toastContainer"></div>

<script>
    let customers = <?php echo json_encode($initial_customers); ?>;
    let filtered = [...customers];
    let currentPage = 1;
    const perPage = 8;
    let deleteId = null;
    let editModal, deleteModal;
    let renderTimeout = null;

    document.addEventListener('DOMContentLoaded', () => {
        editModal = new bootstrap.Modal(document.getElementById('editModal'));
        if(document.getElementById('deleteModal')) {
            deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        }
        
        // Debounce Search
        const searchInput = document.getElementById('searchInput');
        let searchTimeout;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterData, 300);
        });

        render();
    });

    function getBadge(status) {
        if (status === 'Active') return '<span class="badge-active-maroon">Active</span>';
        if (status === 'Inactive') return '<span class="badge-inactive-maroon">Inactive</span>';
        return '<span class="badge-blocked-maroon">Blocked</span>';
    }

    function render() {
        const tbody = document.getElementById('tableBody');
        const mc = document.getElementById('mobileCards');

        // 1) Show Skeleton State
        tbody.innerHTML = Array(perPage).fill(0).map(() => `
        <tr>
            <td><div class="skeleton skeleton-text w-50 mx-auto"></div></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="skeleton skeleton-avatar"></div>
                    <div class="w-100"><div class="skeleton skeleton-text w-75"></div></div>
                </div>
            </td>
            <td class="d-none d-xl-table-cell"><div class="skeleton skeleton-text w-75"></div></td>
            <td><div class="skeleton skeleton-text w-75"></div></td>
            <td><div class="skeleton skeleton-text w-50 mx-auto"></div></td>
            <td class="d-none d-xl-table-cell"><div class="skeleton skeleton-text w-50"></div></td>
            <td class="d-none d-xl-table-cell"><div class="skeleton skeleton-text w-50"></div></td>
            <td><div class="skeleton skeleton-text w-50"></div></td>
            <td class="d-none d-xl-table-cell"><div class="skeleton skeleton-text w-50"></div></td>
            <td><div class="skeleton skeleton-text w-75 mx-auto"></div></td>
        </tr>`).join('');

        mc.innerHTML = Array(perPage).fill(0).map(() => `
        <div class="mob-card-maroon">
            <div class="mob-card-header-maroon">
                <div class="skeleton skeleton-avatar"></div>
                <div class="w-100">
                    <div class="skeleton skeleton-text w-75"></div>
                    <div class="skeleton skeleton-text w-50"></div>
                </div>
            </div>
            <div class="mob-card-body-maroon p-3">
                <div class="skeleton skeleton-row"></div>
                <div class="skeleton skeleton-row"></div>
            </div>
        </div>`).join('');

        clearTimeout(renderTimeout);
        renderTimeout = setTimeout(() => {
            const start = (currentPage - 1) * perPage;
            const pageData = filtered.slice(start, start + perPage);
            const total = filtered.length;
            const pages = Math.ceil(total / perPage) || 1;

            // Desktop
            tbody.innerHTML = pageData.map(c => `
            <tr>
                <td class="text-center" style="width: 1%;">
                    <input type="checkbox" class="form-check-input row-check" style="border-color:#ccc;" />
                </td>
                <td>
                    <div class="cust-cell">
                        <div class="cust-thumb">${c.initials}</div>
                        <div>
                            <div class="cust-name-main">
                                <a href="customer-details.php?id=${c.id}" class="text-decoration-none text-dark hover-primary">${c.name}</a>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="d-none d-xl-table-cell cust-subtext">${c.email}</td>
                <td style="color:#444;font-weight:500;">${c.phone}</td>
                <td style="text-align:center;font-weight:600;color:#444;">${c.orders}</td>
                <td class="d-none d-xl-table-cell" style="font-weight:600;color:#1a1a1a;">₹ ${Number(c.spend).toLocaleString('en-IN')}</td>
                <td class="d-none d-xl-table-cell" style="color:#888;font-size:0.88rem;">${c.join}</td>
                <td>${getBadge(c.status)}</td>
                <td class="d-none d-xl-table-cell" style="color:#888;font-size:0.88rem;">${c.join}</td>
                <td>
                    <div style="display:flex;gap:8px;justify-content:center;">
                        <button class="act-btn-maroon" title="Edit" aria-label="Edit Customer" onclick="openEdit(${c.id})"><i class="bi bi-pencil"></i></button>
                        <a href="customer-details.php?id=${c.id}" class="act-btn-maroon text-decoration-none" title="View Details" aria-label="View Customer Details">
                            <i class="bi bi-eye"></i>
                        </a>
                        <button class="act-btn-maroon" title="Delete" aria-label="Delete Customer" onclick='openDeleteModal(${JSON.stringify(c)}, "customer")'><i class="bi bi-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');

            // Mobile (Premium Stacked Cards as requested)
            mc.innerHTML = pageData.map(c => `
            <div class="mob-card-maroon">
                <div class="mob-card-header-maroon">
                    <div class="cust-thumb">${c.initials}</div>
                    <div style="flex:1;">
                        <div class="cust-name-main">
                            <a href="customer-details.php?id=${c.id}" class="text-decoration-none text-dark hover-primary">${c.name}</a>
                        </div>
                        <div class="cust-subtext">${c.email}</div>
                    </div>
                    <input type="checkbox" class="form-check-input shadow-none" />
                </div>
                <div class="mob-card-body-maroon">
                    <div class="mob-data-point"><i class="bi bi-telephone text-primary"></i> <span>${c.phone}</span></div>
                    <div class="mob-data-point"><i class="bi bi-bag-check text-success"></i> <span>${c.orders} <span class="text-muted">Orders</span></span></div>
                    <div class="mob-data-point"><i class="bi bi-cash-stack text-warning"></i> <span>₹ ${Number(c.spend).toLocaleString('en-IN')}</span></div>
                    <div class="mob-data-point"><i class="bi bi-circle-fill text-muted" style="font-size:0.6rem;"></i> <span>${getBadge(c.status)}</span></div>
                </div>
                <div class="mob-card-footer-maroon">
                    <a href="customer-details.php?id=${c.id}" class="mob-act-btn-maroon"><i class="bi bi-eye"></i> View</a>
                    <a href="javascript:void(0)" class="mob-act-btn-maroon edit" onclick="openEdit(${c.id})"><i class="bi bi-pencil-square"></i> Edit</a>
                </div>
            </div>
        `).join('');

            // Info & Pagination
            const range = total === 0 ? '0' : `${start + 1}–${Math.min(start + perPage, total)}`;
            const info = total === 0 ? 'No customers found' : `Page ${currentPage} of ${pages} · ${range} of ${total} customers`;
            document.getElementById('pageInfoDesktop').textContent = info;

            buildPagination(pages, 'paginationDesktop');
            buildPagination(pages, 'paginationMobile');

            document.getElementById('noResults').style.display = total === 0 ? 'block' : 'none';
        }, 400); // 400ms delay to simulate loading for premium feel
    }

    function buildPagination(pages, containerId) {
        const c = document.getElementById(containerId);
        let html = `<button class="page-btn-maroon me-2" onclick="goPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Back</button>`;

        for (let i = 1; i <= pages; i++) {
            if (i === 1 || i === pages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `<button class="page-btn-maroon mx-1 ${i === currentPage ? 'active' : ''}" onclick="goPage(${i})">${i}</button>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                html += `<span class="px-2">...</span>`;
            }
        }

        html += `<button class="page-btn-maroon ms-2" onclick="goPage(${currentPage + 1})" ${currentPage === pages ? 'disabled' : ''}>Next</button>`;
        c.innerHTML = html;
    }

    function goPage(p) {
        const pages = Math.ceil(filtered.length / perPage) || 1;
        if (p < 1 || p > pages) return;
        currentPage = p;
        render();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function filterData() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        const st = document.getElementById('statusFilter').value;

        filtered = customers.filter(c => {
            const matchesQ = !q || c.name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q) || c.phone.includes(q);
            const matchesSt = !st || c.status.toLowerCase() === st.toLowerCase();
            return matchesQ && matchesSt;
        });

        currentPage = 1;
        render();
    }

    function toggleAll(cb) {
        document.querySelectorAll('.row-check').forEach(c => c.checked = cb.checked);
    }

    window.applyStatusFilter = function(status) {
        const filterSelect = document.getElementById('statusFilter');
        if (filterSelect) {
            filterSelect.value = status;
            filterData();
            // Scroll to table
            document.querySelector('.table-card-maroon').scrollIntoView({ behavior: 'smooth' });
        }
    };

    function openEdit(id) {
        const c = customers.find(x => x.id === id);
        if (!c) return;
        document.getElementById('editId').value = id;
        document.getElementById('editName').value = c.name;
        document.getElementById('editEmail').value = c.email;
        document.getElementById('editPhone').value = c.phone;
        document.getElementById('editStatus').value = c.status;
        editModal.show();
    }

    async function saveEdit() {
        const id = parseInt(document.getElementById('editId').value);
        const data = {
            action: 'update',
            id: id,
            name: document.getElementById('editName').value,
            email: document.getElementById('editEmail').value,
            phone: document.getElementById('editPhone').value,
            status: document.getElementById('editStatus').value
        };

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('api/customers.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({...data, csrf_token: csrfToken})
            });
            const result = await res.json();
            if (result.success) {
                showToast('Customer updated successfully');
                editModal.hide();
                // Update local data and re-render
                const c = customers.find(x => x.id === id);
                if (c) {
                    c.name = data.name;
                    c.email = data.email;
                    c.phone = data.phone;
                    c.status = data.status;
                }
                filterData();
            } else {
                showToast(result.error || 'Failed to update customer', 'error');
            }
        } catch (err) {
            showToast('Network error occurred', 'error');
        }
    }

    async function performDelete(id) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('api/customers.php', {
                method: 'DELETE',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ id: id, csrf_token: csrfToken })
            });
            const result = await res.json();
            if (result.success) {
                showToast('Customer deleted successfully');
                customers = customers.filter(c => c.id !== id);
                filterData();
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            } else {
                showToast(result.error || 'Failed to delete customer', 'error');
            }
        } catch (err) {
            showToast('Network error occurred', 'error');
        }
    }

    // Overwrite the global confirmDelete in admin/includes/modals/delete-confirm.php if it exists
    // or just ensure our delete logic is wired. 
    // Usually, the delete modal calls a global function.
    window.confirmDelete = function() {
        const modal = document.getElementById('deleteModal');
        const id = parseInt(modal.getAttribute('data-id'));
        if (id) performDelete(id);
    };

    function showToast(msg, type = 'success') {
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

    function exportCSV() {
        // Robust CSV Generation
        const headers = ['Name', 'Email', 'Phone', 'Spend', 'Orders', 'Status'];
        
        const escapeCSV = (val) => {
            const str = String(val === null || val === undefined ? '' : val);
            if (str.includes(',') || str.includes('"') || str.includes('\n')) {
                return `"${str.replace(/"/g, '""')}"`;
            }
            return str;
        };

        const csvRows = [headers.join(',')];
        filtered.forEach(c => {
            const row = [c.name, c.email, c.phone, c.spend, c.orders, c.status];
            csvRows.push(row.map(escapeCSV).join(','));
        });

        const csvString = csvRows.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", `customers_export_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showToast('CSV Exported Successfully');
    }

    function exportPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        
        // Add Title
        doc.setFontSize(18);
        doc.setTextColor(123, 31, 31); // Maroon #7B1F1F
        doc.text("Customer Directory Report", 14, 20);
        
        doc.setFontSize(10);
        doc.setTextColor(100);
        doc.text(`Generated on: ${new Date().toLocaleString()}`, 14, 28);
        
        // Prepare Table Data
        const tableBody = filtered.map(c => [
            c.name,
            c.email,
            c.phone,
            'INR ' + Number(c.spend).toLocaleString('en-IN'),
            c.orders,
            c.status
        ]);

        doc.autoTable({
            startY: 35,
            head: [['Name', 'Email', 'Phone', 'Spend (INR)', 'Orders', 'Status']],
            body: tableBody,             headStyles: { fillColor: [123, 31, 31], fontSize: 10, fontStyle: 'bold' },
            bodyStyles: { fontSize: 8 },
            alternateRowStyles: { fillColor: [245, 245, 245] },
            margin: { top: 35 }
        });

        doc.save(`customers_report_${new Date().toISOString().split('T')[0]}.pdf`);
        showToast('PDF Exported Successfully');
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const overlay = document.getElementById('addCustomerOverlay');
        const openBtn = document.getElementById('openAddCustomerPanel');
        const closeBtn = document.getElementById('addCustomerOverlayClose');
        const cancelBtn = document.getElementById('addCustomerOverlayCancel');
        const saveBtn = document.getElementById('addCustomerOverlaySave');
        const scrollContainer = document.getElementById('addCustomerOverlayScroll');
        const thumb = document.getElementById('addCustomerOverlayThumb');

        if (!overlay || !scrollContainer || !thumb || !openBtn) {
            return;
        }

        const updateThumb = () => {
            const trackHeight = scrollContainer.offsetHeight;
            const contentHeight = scrollContainer.scrollHeight;
            const scrollTop = scrollContainer.scrollTop;

            if (contentHeight <= trackHeight) {
                thumb.style.display = 'none';
                return;
            }

            thumb.style.display = 'block';
            const thumbHeight = Math.max((trackHeight / contentHeight) * trackHeight, 40);
            const maxScroll = contentHeight - trackHeight;
            const maxThumbTop = trackHeight - thumbHeight;
            const thumbTop = (scrollTop / maxScroll) * maxThumbTop;
            thumb.style.height = thumbHeight + 'px';
            thumb.style.top = `${thumbTop}px`;
        };

        const resetForm = () => {
            overlay.querySelectorAll('.add-customer-input').forEach(input => {
                input.value = '';
            });
            overlay.querySelectorAll('.add-customer-select').forEach(select => {
                select.selectedIndex = 0;
            });
        };

        const openOverlay = () => {
            overlay.removeAttribute('hidden');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('add-customer-overlay-open');
            updateThumb();
        };

        const closeOverlay = () => {
            overlay.setAttribute('aria-hidden', 'true');
            overlay.setAttribute('hidden', 'hidden');
            document.body.classList.remove('add-customer-overlay-open');
        };

        const handleSave = async () => {
            const data = {
                action: 'create',
                name: document.getElementById('overlayCustomerName').value,
                email: document.getElementById('overlayCustomerEmail').value,
                phone: document.getElementById('overlayCustomerPhone').value,
                address: document.getElementById('overlayCustomerAddress').value,
                city: document.getElementById('overlayCustomerCity').value,
                state: document.getElementById('overlayCustomerState').value,
                pincode: document.getElementById('overlayCustomerPincode').value,
                password: document.getElementById('overlayCustomerPassword').value,
                status: document.getElementById('overlayCustomerStatus').checked
            };

            if (!data.name || !data.email || !data.password) {
                showToast('Please fill in required fields (Name, Email, Password)', 'error');
                return;
            }

            try {
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('api/customers.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({...data, csrf_token: csrfToken})
                });
                const result = await res.json();
                
                if (result.success) {
                    showToast('Customer added successfully');
                    closeOverlay();
                    resetForm();
                    // Optional: fetch all or just push to local (simpler to reload or refetch)
                    location.reload(); 
                } else {
                    showToast(result.error || 'Failed to add customer', 'error');
                }
            } catch (err) {
                showToast('Network error occurred', 'error');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Save Customer';
            }
        };

        openBtn.addEventListener('click', openOverlay);
        closeBtn?.addEventListener('click', closeOverlay);
        cancelBtn?.addEventListener('click', () => {
            resetForm();
            closeOverlay();
        });
        saveBtn?.addEventListener('click', handleSave);
        scrollContainer.addEventListener('scroll', updateThumb);
        window.addEventListener('resize', updateThumb);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !overlay.hasAttribute('hidden')) {
                closeOverlay();
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
