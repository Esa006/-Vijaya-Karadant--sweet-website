<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/customer-details.php
 * Description: High-Fidelity Customer CRM View (Dynamic)
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 3.1.0
 * =============================================================
 */

require_once '../config/config.php';
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';

$userId = (int)($_GET['id'] ?? 0);
?>

<style>
    :root {
        --primary-color: #8B2E2E;
        --secondary-color: #D97706;
        --bg-light: #FDF8F5;
        --border-color: #D4A574;
        --success-bg: #DCFCE7;
        --success-text: #166534;
        --danger-bg: #FEE2E2;
        --danger-text: #991B1B;
    }

    .crm-details-page { background-color: #FAFAFA; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .breadcrumb-item a { color: var(--primary-color); text-decoration: none; }
    .breadcrumb-item.active { color: var(--primary-color); }
    .page-title { color: var(--primary-color); font-weight: 600; }

    .customer-avatar {
        width: 80px; height: 80px;
        background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        border-radius: 8px; display: flex; align-items: center; justify-content: center;
        color: white; font-size: 2rem; font-weight: bold;
    }

    .status-badge { background-color: var(--success-bg); color: var(--success-text); padding: 6px 16px; border-radius: 20px; display: inline-flex; align-items: center; gap: 8px; }
    .status-dot { width: 12px; height: 12px; background-color: #22C55E; border-radius: 50%; }

    .btn-outline-custom { border: 2px solid var(--primary-color); color: var(--primary-color); background: white; }
    .btn-outline-custom:hover { background-color: var(--primary-color); color: white; }
    .btn-primary-custom { background: linear-gradient(135deg, var(--secondary-color), var(--primary-color)); border: none; color: white; }
    .btn-primary-custom:hover { background: linear-gradient(135deg, #B45309, #7C2D2D); color: white; }

    .stat-card { background: var(--bg-light); border: 2px solid var(--border-color); border-radius: 12px; padding: 20px; transition: transform 0.2s; }
    .stat-icon { background-color: var(--primary-color); color: white; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .stat-label { color: #6B7280; font-size: 0.875rem; }
    .stat-value { color: var(--primary-color); font-size: 1.5rem; font-weight: 700; }

    .section-card { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px; }
    .section-title { color: var(--primary-color); font-weight: 600; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--border-color); }
    .info-label { color: #6B7280; font-size: 0.875rem; margin-bottom: 4px; }
    .info-value { color: #1F2937; font-weight: 500; margin-bottom: 16px; }

    .address-box { background: var(--bg-light); border: 2px solid var(--border-color); border-radius: 12px; padding: 20px; }
    .address-title { color: var(--secondary-color); font-weight: 600; margin-bottom: 12px; }

    .tag-badge { background: white; border: 2px solid var(--primary-color); color: var(--primary-color); padding: 6px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 8px; margin-right: 8px; margin-bottom: 8px; }
    .notes-box { background: var(--bg-light); border: 2px solid var(--border-color); border-radius: 12px; padding: 16px; min-height: 100px; }

    /* Timeline Styles */
    .timeline-item { position: relative; padding-left: 32px; padding-bottom: 24px; }
    .timeline-item::before { content: ''; position: absolute; left: 6px; top: 0; bottom: 0; width: 2px; background-color: #E5E7EB; }
    .timeline-item:last-child::before { display: none; }
    .timeline-dot { position: absolute; left: 0; top: 4px; width: 14px; height: 14px; border-radius: 50%; border: 3px solid #9CA3AF; background: white; }
    .timeline-item.active .timeline-dot { border-color: var(--secondary-color); background-color: var(--secondary-color); }
    .timeline-date { color: #6B7280; font-size: 0.875rem; }

    /* Skeleton Loading */
    .skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: skeleton-loading 1.5s infinite; border-radius: 12px; }
    @keyframes skeleton-loading { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
</style>

<div class="main-content crm-details-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="container-fluid py-4" id="crmApp" data-user-id="<?php echo $userId; ?>">
        
        <!-- Skeleton Loader -->
        <div id="skeletonLoader">
            <div class="section-card skeleton" style="height: 150px;"></div>
            <div class="row">
                <div class="col-lg-8">
                    <div class="row mb-4">
                        <div class="col-3"><div class="stat-card skeleton" style="height: 100px;"></div></div>
                        <div class="col-3"><div class="stat-card skeleton" style="height: 100px;"></div></div>
                        <div class="col-3"><div class="stat-card skeleton" style="height: 100px;"></div></div>
                        <div class="col-3"><div class="stat-card skeleton" style="height: 100px;"></div></div>
                    </div>
                    <div class="section-card skeleton" style="height: 400px;"></div>
                </div>
                <div class="col-lg-4">
                    <div class="section-card skeleton" style="height: 500px;"></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div id="mainContent" class="d-none">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="customers.php">Customer</a></li>
                    <li class="breadcrumb-item active" id="breadcrumbName">Customer Details</li>
                </ol>
            </nav>

            <!-- Customer Header Card -->
            <div class="section-card">
                <div class="row align-items-center">
                    <div class="col-md-7 d-flex align-items-center gap-3">
                        <div class="customer-avatar" id="profileInitials">JD</div>
                        <div>
                            <h4 class="mb-1 fw-bold h3" id="profileName">Loading...</h4>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <span class="text-muted" id="profileEmail">...</span>
                                <span class="text-muted" id="profileSince">...</span>
                                <span class="status-badge" id="statusBadgeContainer">
                                    <span class="status-dot"></span>
                                    <span id="statusText">Active</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 d-flex gap-2 justify-content-md-end mt-3 mt-md-0">
                        <a href="orders.php?user_id=<?php echo $userId; ?>" class="btn btn-outline-custom px-4">
                            <i class="bi bi-bag me-2"></i>View all Orders
                        </a>
                        <button class="btn btn-primary-custom px-4" data-bs-toggle="modal" data-bs-target="#editCustomerModal">
                            <i class="bi bi-pencil me-2"></i>Edit Customer
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Stats Grid -->
                    <div class="row mb-4">
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="stat-label">Total Orders</span>
                                    <div class="stat-icon"><i class="bi bi-bag"></i></div>
                                </div>
                                <div class="stat-value" id="statOrders">0</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="stat-label">Total Spend</span>
                                    <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
                                </div>
                                <div class="stat-value">₹ <span id="statSpend">0</span></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="stat-label">Avg. Order Value</span>
                                    <div class="stat-icon"><i class="bi bi-graph-up"></i></div>
                                </div>
                                <div class="stat-value">₹ <span id="statAov">0</span></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="stat-label">Last Order</span>
                                    <div class="stat-icon"><i class="bi bi-calendar"></i></div>
                                </div>
                                <div class="stat-value" id="statLastOrder">N/A</div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="section-card">
                        <h5 class="section-title">Personal Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-label">Full Name</div>
                                <div class="info-value" id="infoName">...</div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Email Address</div>
                                <div class="info-value" id="infoEmail">...</div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Phone Number</div>
                                <div class="info-value" id="infoPhone">...</div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Date of Birth</div>
                                <div class="info-value" id="infoDob">...</div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div class="section-card">
                        <h5 class="section-title">Address Information</h5>
                        <div class="row g-3" id="addressContainer">
                            <!-- Addresses injected by JS -->
                        </div>
                    </div>

                    <!-- Recent Orders Table -->
                    <div class="section-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="section-title mb-0" style="border:none;">Recent Orders</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table orders-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="ordersTable">
                                    <!-- Orders injected by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Content -->
                <div class="col-lg-4">
                    <!-- Notes & Tags -->
                    <div class="section-card">
                        <h5 class="section-title">Notes & Tags</h5>
                        
                        <div class="mb-4">
                            <label class="info-label">Customer Tags</label>
                            <div class="mt-2 d-flex flex-wrap gap-2" id="tagContainer">
                                <!-- Tags injected by JS -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="info-label">Internal Notes</label>
                            <textarea id="noteInput" class="form-control notes-box mt-2 shadow-none" rows="4" placeholder="Add internal notes..."></textarea>
                        </div>

                        <button class="btn btn-primary-custom w-100 py-2 fw-bold" onclick="saveNote()">
                            Save Note
                        </button>
                    </div>

                    <!-- Activity Timeline -->
                    <div class="section-card">
                        <h5 class="section-title">Activity Timeline</h5>
                        <div id="timelineList">
                            <!-- Timeline items injected by JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>
<script>
    // Ensure plugin is available globally for our app script
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof dayjs_plugin_relativeTime !== 'undefined') {
            dayjs.extend(dayjs_plugin_relativeTime);
        }
    });
</script>
<script src="../assets/js/admin/customer-details.js"></script>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCustomerForm">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="editFullName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" id="editPhone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="editStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary-custom" onclick="saveCustomer(event)">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
