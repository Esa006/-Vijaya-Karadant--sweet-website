<?php
/**
 * Sweets Website
 * =============================================================
 * File: sidebar.php
 * Description: Admin navigation sidebar with luxury branding
 * =============================================================
 */
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="<?php echo BASE_URL . SITE_LOGO; ?>" alt="<?php echo SITE_NAME; ?> Logo">
        </div>
        <button class="sidebar-close d-lg-none" id="sidebarClose">
            <i class="bi bi-x"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a href="index.php" class="nav-link <?php echo is_active('index.php'); ?>">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="products.php" class="nav-link <?php echo is_active('products.php', ['edit-product.php']); ?>">
                    <i class="bi bi-box-seam"></i>
                    <span>Products</span>
                </a>
                <a href="products.php" class="nav-link small ps-4 py-1 opacity-75" style="font-size: 0.8rem;">
                    <i class="bi bi-plus-circle me-1"></i> Add Product
                </a>
            </li>
            <li class="nav-item">
                <a href="combos.php" class="nav-link <?php echo is_active('combos.php'); ?>">
                    <i class="bi bi-gift"></i>
                    <span>Combo Offers</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link <?php echo is_active('categories.php', ['edit-category.php']); ?>" id="navCategories">
                    <i class="bi bi-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="subcategories.php" class="nav-link <?php echo is_active('subcategories.php'); ?>">
                    <i class="bi bi-tags"></i>
                    <span>Sub Categories</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="orders.php" class="nav-link <?php echo is_active('orders.php', ['order-details.php', 'invoice.php']); ?>">
                    <i class="bi bi-grid-3x3-gap"></i>
                    <span>Orders</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="customers.php" class="nav-link <?php echo is_active('customers.php', ['customer-details.php', 'add-customer.php']); ?>">
                    <i class="bi bi-people"></i>
                    <span>Customers</span>
                </a>
                <a href="customer-details.php" class="nav-link small ps-4 py-1 opacity-75" style="font-size: 0.8rem;">
                    <i class="bi bi-plus-circle me-1"></i> Customer Details
                </a>
            </li>

            <li class="nav-divider">Logistics</li>
            <li class="nav-item">
                <a href="invoices.php" class="nav-link <?php echo is_active('invoices.php'); ?>">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Invoices</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="inventory.php" class="nav-link <?php echo is_active('inventory.php', ['inventory-detail.php']); ?>">
                    <i class="bi bi-receipt"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="stock-requests.php" class="nav-link <?php echo is_active('stock-requests.php'); ?>">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Stock Requests</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="delivery.php" class="nav-link <?php echo is_active('delivery.php'); ?>">
                    <i class="bi bi-truck"></i>
                    <span>Delivery</span>
                </a>
            </li>
            
            <li class="nav-divider">Marketing & Reports</li>
            <li class="nav-item">
                <a href="promotions.php" class="nav-link <?php echo is_active('promotions.php', ['create-offer.php']); ?>">
                    <i class="bi bi-percent"></i>
                    <span>Offers / Coupons</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="hero-slides.php" class="nav-link <?php echo is_active('hero-slides.php'); ?>">
                    <i class="bi bi-images"></i>
                    <span>Hero Slider</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="notifications.php" class="nav-link <?php echo is_active('notifications.php'); ?>">
                    <i class="bi bi-bell"></i>
                    <span>Notifications</span>
                    <?php 
                        if (class_exists('AdminNotificationService')) {
                            $notifServiceForSidebar = new AdminNotificationService();
                            $unreadCount = $notifServiceForSidebar->getTopbarData()['count'] ?? 0;
                            if ($unreadCount > 0) {
                                echo '<span class="badge rounded-pill bg-danger ms-auto" style="font-size: 10px;">' . $unreadCount . '</span>';
                            }
                        }
                    ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="news.php" class="nav-link <?php echo is_active('news.php'); ?>">
                    <i class="bi bi-newspaper"></i>
                    <span>Latest News</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="queries.php" class="nav-link <?php echo is_active('queries.php'); ?>">
                    <i class="bi bi-chat-left-text"></i>
                    <span>Customer Queries</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="reports.php" class="nav-link <?php echo is_active('reports.php'); ?>">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Reports / Analytics</span>
                </a>
                <a href="inventory-report.php" class="nav-link small ps-4 py-1 opacity-75 <?php echo is_active('inventory-report.php'); ?>" style="font-size: 0.8rem;">
                    <i class="bi bi-clipboard-data me-1"></i> Inventory Status
                </a>
            </li>

            <li class="nav-divider">Account</li>
            <li class="nav-item">
                <a href="profile.php" class="nav-link <?php echo is_active('profile.php'); ?>">
                    <i class="bi bi-person-circle"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link <?php echo is_active('settings.php'); ?>">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
            <?php if (getCurrentAdminRoleSlug() === 'super_admin'): ?>
            <li class="nav-item">
                <a href="permissions.php" class="nav-link <?php echo is_active('permissions.php'); ?>">
                    <i class="bi bi-shield-lock"></i>
                    <span>Permissions</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a href="audit-logs.php" class="nav-link <?php echo is_active('audit-logs.php'); ?>">
                    <i class="bi bi-journal-text"></i>
                    <span>Audit Logs</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="logs.php" class="nav-link <?php echo is_active('logs.php'); ?>">
                    <i class="bi bi-journal-code"></i>
                    <span>System Logs</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>" class="nav-link text-primary" target="_blank">
                    <i class="bi bi-browser-safari"></i>
                    <span>View Website</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>api/logout.php" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>