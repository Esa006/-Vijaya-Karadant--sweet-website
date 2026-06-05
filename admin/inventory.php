<?php
/**
 * Sweets Website
 * =============================================================
 * File: inventory.php
 * Description: Premium Inventory CRM – Stock, Quantity, and Stats 
 * =============================================================
 */

$pageStyles = ['assets/css/admin/products.css', 'assets/css/admin/pages/admin-inventory.css', 'assets/css/admin/pages/product-preview.css', 'assets/css/admin/pages/product-delete.css', 'assets/css/admin/components/add-inventory-modal.css'];
$pageScripts = ['assets/js/admin/modals.js'];
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once SERVICES_PATH . '/ProductService.php';
require_once SERVICES_PATH . '/CategoryService.php';

$productService = new ProductService();
$categoryService = new CategoryService();

$stats = $productService->getProductStats();
$inventoryItems = $productService->getInventoryData();
$allCategories = $categoryService->getCategoriesTree(); // Or getAllFlat
?>

<div class="main-content products-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 products-content-body">
        <!-- Header Section -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 mb-3 border-bottom px-4 mx-n4">
            <div>
                <h2 class="fw-bold mb-0 products-page-title">Inventory</h2>
            </div>
            <div class="d-flex gap-2">
                <button class="btn rounded-2 d-flex align-items-center products-outline-btn products-export-btn" onclick="exportInventoryCSV()" >
                    <i class="bi bi-download me-2 fs-5"></i> Export
                </button>
                <button class="btn-primary rounded-2 d-flex align-items-center products-add-btn" data-bs-toggle="modal" data-bs-target="#addInventoryModal" style="background-color: #8c3333;">
                    <i class="bi bi-plus-lg me-2 fs-5"></i> Add Stock
                </button>
            </div>
        </div>

        <div class="px-2 pb-5">
            <!-- Stat Cards Section -->
            <div class="row g-4 mb-5">
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="admin-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-card-label text-muted fw-bold mb-2 small products-stat-label">Total
                                    Products</div>
                                <h3 id="stat-total-products" class="stat-card-value fw-bolder mb-0 text-dark products-stat-value"><?php echo number_format($stats['total']); ?></h3>
                            </div>
                            <div class="products-stat-icon-wrapper"
                                style="background: rgba(174, 75, 58, 0.1); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items:center; justify-content:center;">
                                <i class="bi bi-box-seam text-danger fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="admin-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-card-label text-muted fw-bold mb-2 small products-stat-label">In Stock
                                </div>
                                <h3 id="stat-in-stock" class="stat-card-value fw-bolder mb-0 text-dark products-stat-value"><?php echo number_format($stats['in_stock']); ?></h3>
                            </div>
                            <div class="products-stat-icon-wrapper"
                                style="background: rgba(25, 135, 84, 0.1); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items:center; justify-content:center;">
                                <i class="bi bi-check-circle text-success fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="admin-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-card-label text-muted fw-bold mb-2 small products-stat-label">Low Stock
                                </div>
                                <h3 id="stat-low-stock" class="stat-card-value fw-bolder mb-0 text-dark products-stat-value"><?php echo number_format($stats['low_stock']); ?></h3>
                            </div>
                            <div class="products-stat-icon-wrapper"
                                style="background: rgba(255, 193, 7, 0.1); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items:center; justify-content:center;">
                                <i class="bi bi-exclamation-triangle text-warning fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="admin-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-card-label text-muted fw-bold mb-2 small products-stat-label">Out of
                                    Stock</div>
                                <h3 id="stat-out-of-stock" class="stat-card-value fw-bolder mb-0 text-dark products-stat-value"><?php echo number_format($stats['out_of_stock']); ?></h3>
                            </div>
                            <div class="products-stat-icon-wrapper"
                                style="background: rgba(220, 53, 69, 0.1); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items:center; justify-content:center;">
                                <i class="bi bi-x-circle text-danger fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="mt-4">
                <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 mb-5">
                    <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 0;">
                        <div class="input-group products-search-group"
                            style="max-width: 280px; min-width: 160px; flex: 1 1 160px;">
                            <span class="input-group-text bg-transparent border-0 pe-1 products-search-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" id="inventorySearch"
                                class="form-control border-0 shadow-none bg-transparent ps-2 placeholder-muted products-filter-input"
                                placeholder="Search inventory...">
                        </div>
                        <button
                            class="btn btn-light d-md-none border bg-white shadow-sm products-filter-trigger flex-shrink-0"
                            type="button" data-bs-toggle="offcanvas" data-bs-target="#inventoryFilterOffcanvas">
                            <i class="bi bi-sliders"></i>
                        </button>
                    </div>

                    <select class="form-select shadow-none products-filter-select d-none d-md-block" id="statusFilter"
                        style="width: auto; min-width: 150px;">
                        <option value="all">Status All</option>
                        <option value="In Stock">In Stock</option>
                        <option value="Low Stock">Low Stock</option>
                        <option value="Out of Stock">Out of Stock</option>
                    </select>

                    <select class="form-select shadow-none products-filter-select d-none d-md-block" id="categoryFilter"
                        style="width: auto; min-width: 160px;">
                        <option value="all">All Categories</option>
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Mobile Offcanvas -->
                <div class="offcanvas offcanvas-bottom products-filter-offcanvas" tabindex="-1"
                    id="inventoryFilterOffcanvas"
                    style="max-height: 80vh; border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title fw-bold">Filters</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <label
                                    class="fw-bold form-label products-filter-label text-muted small mb-1">Status</label>
                                <select class="form-select shadow-none products-filter-select w-100"
                                    id="statusFilterMobile">
                                    <option value="all">Status All</option>
                                    <option value="In Stock">In Stock</option>
                                    <option value="Low Stock">Low Stock</option>
                                    <option value="Out of Stock">Out of Stock</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="fw-bold form-label products-filter-label text-muted small mb-1">Category</label>
                                <select class="form-select shadow-none products-filter-select w-100"
                                    id="categoryFilterMobile">
                                    <option value="all">All Categories</option>
                                    <?php foreach ($allCategories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <hr class="my-2 text-muted">
                            <button class="btn btn-dark w-100 py-3 fw-bold border-0" data-bs-dismiss="offcanvas"
                                style="background-color: #8c3333;">Show Results</button>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <h4 class="fw-bold text-dark mb-4 products-table-title">Stock Directory</h4>
                <div class="table-responsive products-table-wrapper rounded-3 border-0">
                    <table class="table align-middle mb-0 products-mobile-card-grid shadow-none" id="inventoryTable">
                        <thead class="products-table-head">
                            <tr>
                                <th class="ps-4 py-3 products-table-th products-table-th-toggle">&nbsp;</th>
                                <th class="py-3 products-table-th products-table-th-check">&nbsp;</th>
                                <th class="py-3 products-table-th">Product Details</th>
                                <th class="py-3 text-center d-none d-lg-table-cell products-table-th">Category</th>
                                <th class="py-3 text-center products-table-th">Stock Quantity</th>
                                <th class="py-3 text-center d-none d-md-table-cell products-table-th">Status</th>
                                <th class="py-3 text-center d-none d-sm-table-cell products-table-th">Last Updated</th>
                                <th class="py-3 pe-4 text-center products-table-th">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="products-table-body">
                            <?php foreach ($inventoryItems as $index => $item): ?>
                                <tr class="product-row" data-id="<?php echo $item['id']; ?>"
                                    data-name="<?php echo strtolower($item['name'] ?? ''); ?>"
                                    data-variant-labels="<?php echo htmlspecialchars(strtolower(implode(' ', array_map(static function ($variant) { return (string)($variant['label'] ?? $variant['weight'] ?? ''); }, $item['variants'] ?? []))), ENT_QUOTES, 'UTF-8'); ?>"
                                    data-status="<?php echo $item['status_label'] ?? ''; ?>"
                                    data-category="<?php echo $item['category'] ?? ''; ?>">
                                    <td class="ps-4 border-0 py-3 td-toggle text-center">
                                        <?php if (!empty($item['has_variants'])): ?>
                                            <button type="button" class="btn btn-link p-0 text-decoration-none expand-toggle" data-target="variants-<?php echo (int)$item['id']; ?>" aria-expanded="false" aria-label="Toggle variants">
                                                <i class="bi bi-chevron-right"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td class="ps-4 border-0 py-3 td-check">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input shadow-none products-row-checkbox"
                                                type="checkbox">
                                        </div>
                                    </td>
                                    <td class="border-0 py-3 td-info">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?php echo BASE_URL . ($item['image'] ?? 'assets/images/placeholders/product-placeholder.png'); ?>"
                                                alt="<?php echo htmlspecialchars((string)($item['name'] ?? 'Product')); ?>"
                                                class="rounded-3 product-thumb"
                                                style="width: 50px; height: 50px; object-fit: cover;"
                                                onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>assets/images/placeholders/product-placeholder.png'">
                                            <div>
                                                <div class="fw-bold text-dark products-product-name">
                                                    <?php echo htmlspecialchars((string)($item['name'] ?? 'Unknown Product')); ?>
                                                    <?php if (!empty($item['has_variants'])): ?>
                                                        <span class="badge variant-badge ms-2">Variants</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-uppercase text-muted mt-1 fw-bold"
                                                    style="font-size: 10px; letter-spacing: 0.5px;">
                                                    SKU : <?php echo htmlspecialchars((string)($item['sku'] ?? 'N/A')); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-lg-table-cell border-0 py-3 text-center td-category"
                                        data-label="Category">
                                        <span
                                            class="text-dark fw-bold"><?php echo htmlspecialchars((string)($item['category'] ?? 'General')); ?></span>
                                    </td>
                                    <td class="border-0 py-3 text-center td-stock" data-label="Stock">
                                        <?php if (!empty($item['has_variants'])): ?>
                                            <span class="fw-bold"><?php echo (int)($item['total_stock'] ?? 0); ?></span>
                                        <?php else: ?>
                                            <div class="inventory-qty-control mx-auto shadow-sm" style="width: fit-content;">
                                                <button class="qty-btn qty-btn-minus" type="button">-</button>
                                                <input type="number" class="qty-input" value="<?php echo (int)($item['stock_quantity'] ?? 0); ?>"
                                                    readonly>
                                                <button class="qty-btn qty-btn-plus" type="button">+</button>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-md-table-cell border-0 py-3 text-center td-status"
                                        data-label="Status">
                                        <span
                                            class="d-inline-block fw-bold text-center products-status-pill <?php echo $item['status_class'] ?? 'products-status-out'; ?>">
                                            <?php echo $item['status_label'] ?? 'Out of Stock'; ?>
                                        </span>
                                    </td>
                                    <td class="d-none d-sm-table-cell border-0 py-3 text-center td-time"
                                        data-label="Last Updated">
                                        <span class="text-muted small"><?php echo htmlspecialchars((string)($item['updated_at'] ?? 'Recently')); ?></span>
                                    </td>
                                    <td class="border-0 py-3 text-center pe-4 td-actions">
                                        <div class="d-flex justify-content-center gap-2 td-actions-wrapper">
                                            <?php
                                            $itemJson = htmlspecialchars(json_encode([
                                                'id' => $item['id'] ?? 0,
                                                'name' => $item['name'] ?? 'Product',
                                                'image_path' => $item['image'] ?? '',
                                                'sku' => $item['sku'] ?? 'N/A',
                                                'stock' => $item['stock_quantity'] ?? 0,
                                                'status' => $item['status_label'] ?? 'Unknown'
                                            ]), ENT_QUOTES, 'UTF-8');
                                            ?>
                                            <a href="inventory-detail.php?id=<?php echo $item['id']; ?>" class="text-dark text-decoration-none fs-6" title="Edit"><i
                                                    class="bi bi-pencil"></i></a>
                                            <button type="button" 
                                                class="btn btn-link text-dark p-0 fs-6 text-decoration-none shadow-none" 
                                                title="View" 
                                                onclick='openPreviewMode(<?php echo $itemJson; ?>)'>
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" 
                                                class="btn btn-link text-dark p-0 fs-6 text-decoration-none shadow-none" 
                                                title="Delete" 
                                                onclick='openDeleteModal(<?php echo $itemJson; ?>, "inventory")'>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php if (!empty($item['has_variants'])): ?>
                                    <tr class="variant-details-row" id="variants-<?php echo (int)$item['id']; ?>" data-parent-id="<?php echo (int)$item['id']; ?>" style="display:none;">
                                        <td colspan="8" class="border-0 pt-0 pb-3">
                                            <div class="variant-details-wrap">
                                                <table class="table mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Variant</th>
                                                            <th class="text-center">Price</th>
                                                            <th class="text-center">Stock</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach (($item['variants'] ?? []) as $variant): ?>
                                                            <tr class="variant-row">
                                                                <td>
                                                                    <div class="fw-semibold"><?php echo htmlspecialchars((string)($variant['label'] ?? $variant['weight'] ?? 'Variant')); ?></div>
                                                                    <?php if (!empty($variant['weight'])): ?>
                                                                        <div class="small text-muted"><?php echo htmlspecialchars((string)$variant['weight']); ?></div>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-center"><?php echo number_format((float)($variant['price'] ?? 0), 2); ?></td>
                                                                <td class="text-center">
                                                                    <div class="inventory-qty-control mx-auto shadow-sm variant-qty" style="width: fit-content;">
                                                                        <button class="qty-btn qty-btn-minus" type="button">-</button>
                                                                        <input type="number" class="qty-input" value="<?php echo (int)($variant['stock'] ?? 0); ?>" readonly>
                                                                        <button class="qty-btn qty-btn-plus" type="button">+</button>
                                                                    </div>
                                                                    <input type="hidden" class="variant-id" value="<?php echo (int)($variant['id'] ?? 0); ?>">
                                                                    <input type="hidden" class="variant-product-id" value="<?php echo (int)($item['id'] ?? 0); ?>">
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Meta -->
                <div
                    class="p-4 border-top border-light d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="small text-muted" id="inventoryFooterCount">
                        Showing 1-<?php echo count($inventoryItems); ?> of <?php echo count($inventoryItems); ?> items
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('inventorySearch');
        const statusFilter = document.getElementById('statusFilter');
        const categoryFilter = document.getElementById('categoryFilter');
        const statusFilterMob = document.getElementById('statusFilterMobile');
        const categoryFilterMob = document.getElementById('categoryFilterMobile');
        const rows = document.querySelectorAll('.product-row');

        function filterInventory() {
            const searchTerm = searchInput.value.toLowerCase();

            const isMobile = window.innerWidth < 768;
            const selStatus = isMobile && statusFilterMob ? statusFilterMob.value : (statusFilter ? statusFilter.value : 'all');
            const selCategory = isMobile && categoryFilterMob ? categoryFilterMob.value : (categoryFilter ? categoryFilter.value : 'all');

            rows.forEach(row => {
                const name = (row.getAttribute('data-name') || '').toLowerCase().trim();
                const variantLabels = (row.getAttribute('data-variant-labels') || '').toLowerCase().trim();
                const status = (row.getAttribute('data-status') || '').toLowerCase().trim();
                const category = (row.getAttribute('data-category') || '').toLowerCase().trim();
                const detailRow = document.getElementById('variants-' + row.getAttribute('data-id'));

                const matchesSearch = name.includes(searchTerm) || variantLabels.includes(searchTerm);
                const matchesStatus = (selStatus === 'all' || status === selStatus.toLowerCase().trim());
                const matchesCategory = (selCategory === 'all' || category === selCategory.toLowerCase().trim());

                if (matchesSearch && matchesStatus && matchesCategory) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                    if (detailRow) {
                        detailRow.style.display = 'none';
                    }
                }
                const visibleCount = [...rows].filter(r => r.style.display !== 'none').length;
                const total = rows.length;
                const footerCount = document.getElementById('inventoryFooterCount');
                if (footerCount) {
                    footerCount.textContent = visibleCount === total
                        ? `Showing 1-${total} of ${total} items`
                        : `Showing ${visibleCount} of ${total} items (filtered)`;
                }
            }); // ← close rows.forEach
        } // ← close filterInventory()

        if (searchInput) searchInput.addEventListener('input', filterInventory);
        if (statusFilter) statusFilter.addEventListener('change', filterInventory);
        if (categoryFilter) categoryFilter.addEventListener('change', filterInventory);
        if (statusFilterMob) statusFilterMob.addEventListener('change', filterInventory);
        if (categoryFilterMob) categoryFilterMob.addEventListener('change', filterInventory);

        // Quick Filter by Stat Cards
        document.querySelectorAll('.admin-card').forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function() {
                const label = this.querySelector('.products-stat-label').textContent.trim();
                let statusValue = 'all';
                
                if (label.includes('In Stock')) statusValue = 'In Stock';
                else if (label.includes('Low Stock')) statusValue = 'Low Stock';
                else if (label.includes('Out of Stock')) statusValue = 'Out of Stock';
                else if (label.includes('Total')) statusValue = 'all';

                if (statusFilter) {
                    statusFilter.value = statusValue;
                    // Trigger change event to run filterInventory
                    statusFilter.dispatchEvent(new Event('change'));
                }
                if (statusFilterMob) {
                    statusFilterMob.value = statusValue;
                    statusFilterMob.dispatchEvent(new Event('change'));
                }
            });
        });

        document.querySelectorAll('.expand-toggle').forEach(toggle => {
            toggle.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const target = document.getElementById(targetId);
                const isExpanded = this.getAttribute('aria-expanded') === 'true';

                if (!target) {
                    return;
                }

                target.style.display = isExpanded ? 'none' : 'table-row';
                this.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                this.classList.toggle('is-open', !isExpanded);
            });
        });

        // Dynamic Qty Control (product + variant)
        document.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();

                const control = this.closest('.inventory-qty-control');
                const variantRow = this.closest('.variant-row');
                const productIdAttr = variantRow ? variantRow.querySelector('.variant-product-id').value : this.closest('.product-row').getAttribute('data-id');
                const parentProductRow = document.querySelector(`.product-row[data-id="${productIdAttr}"]`);
                
                const input = control.querySelector('.qty-input');
                let currentVal = parseInt(input.value) || 0;
                const isPlus = this.classList.contains('qty-btn-plus');
                const variantIdField = variantRow ? variantRow.querySelector('.variant-id') : null;
                const variantId = variantIdField ? variantIdField.value : '';
                
                if (!isPlus && currentVal <= 0) return; 

                const action = isPlus ? 'add' : 'reduce';
                const qtyChange = 1;


                // Optimistic UI Update
                input.value = isPlus ? currentVal + 1 : currentVal - 1;
                
                const buttons = control.querySelectorAll('.qty-btn');
                buttons.forEach(b => b.disabled = true);
                
                fetch('api/v1/inventory.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        product_id: productIdAttr,
                        variant_id: variantId || undefined,
                        quantity: qtyChange,
                        action: action,
                        notes: 'Quick update from inventory list'
                    })
                })
                .then(r => {
                    if (!r.ok) throw new Error('Server returned ' + r.status);
                    return r.json();
                })
                .then(data => {
                    buttons.forEach(b => b.disabled = false);
                    if (data.status === 'success' && data.data) {
                        if (data.data.stock !== undefined) {
                            input.value = data.data.stock;
                        }

                        // Update summary cards
                        if (data.data.stats) {
                            const stats = data.data.stats;
                            const statsMap = {
                                'stat-total-products': stats.total,
                                'stat-in-stock': stats.in_stock,
                                'stat-low-stock': stats.low_stock,
                                'stat-out-of-stock': stats.out_of_stock
                            };
                            for (const [id, val] of Object.entries(statsMap)) {
                                const el = document.getElementById(id);
                                if (el) el.textContent = new Intl.NumberFormat().format(val);
                            }
                        }

                        // Update Status Pill and Row Data
                        if (parentProductRow && data.data.status_label) {
                            const statusPill = parentProductRow.querySelector('.products-status-pill');
                            if (statusPill) {
                                statusPill.textContent = data.data.status_label;
                                statusPill.classList.remove('products-status-in', 'products-status-low', 'products-status-out');
                                statusPill.classList.add(data.data.status_class);
                            }
                            parentProductRow.setAttribute('data-status', data.data.status_label);
                        }

                        if (variantRow && data.data.total_stock !== undefined && parentProductRow) {
                            const stockCell = parentProductRow.querySelector('.td-stock .fw-bold');
                            if (stockCell) {
                                stockCell.textContent = data.data.total_stock;
                            }
                        }
                    } else {
                        throw new Error(data.message || 'Unknown error from server');
                    }
                })
                .catch(err => {
                    buttons.forEach(b => b.disabled = false);
                    input.value = currentVal;
                    console.error('Update failed:', err);
                    alert('Error: ' + err.message);
                });
            });
        });
    });

    /**
     * Export currently visible inventory rows to a CSV file.
     * Respects active search/filter state.
     */
    function exportInventoryCSV() {
        const rows = document.querySelectorAll('#inventoryTable .product-row');
        const headers = ['Product Name', 'SKU', 'Category', 'Stock Quantity', 'Status', 'Last Updated'];
        const csvLines = [headers.join(',')];

        rows.forEach(row => {
            if (row.style.display === 'none') return; // skip filtered-out rows

            const name     = (row.getAttribute('data-name') || '').trim();
            const category = (row.getAttribute('data-category') || '').trim();
            const status   = (row.getAttribute('data-status') || '').trim();

            const skuEl  = row.querySelector('[style*="letter-spacing"]');
            const sku    = skuEl ? skuEl.textContent.replace('SKU :', '').trim() : '';

            const qtyInput = row.querySelector('.qty-input');
            const qtyBold  = row.querySelector('.td-stock .fw-bold');
            const stock    = qtyInput ? qtyInput.value : (qtyBold ? qtyBold.textContent : '0');

            const timeEl = row.querySelector('.td-time .text-muted');
            const time   = timeEl ? timeEl.textContent.trim() : '';

            // Escape any commas or quotes in fields
            const escape = v => `"${String(v).replace(/"/g, '""')}"`;
            csvLines.push([escape(name), escape(sku), escape(category), escape(stock), escape(status), escape(time)].join(','));
        });

        if (csvLines.length <= 1) {
            alert('No visible inventory rows to export.');
            return;
        }

        const csvContent = csvLines.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url  = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', 'inventory_' + new Date().toISOString().slice(0, 10) + '.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }
</script>

<?php 
// Include Global Modals
require_once 'includes/modals/product-preview.php';
require_once 'includes/modals/delete-confirm.php';
require_once 'includes/modals/add-inventory.php';
?>

<?php require_once 'includes/footer.php'; ?>
