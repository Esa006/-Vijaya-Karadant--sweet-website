<?php
/**
 * Sweets Website
 * =============================================================
 * File: products.php
 * Description: Premium Product Management – List, Stats, and Filtering
 * =============================================================
 */

$pageStyles = ['assets/css/admin/products.css', 'assets/css/admin/pages/product-preview.css', 'assets/css/admin/pages/product-delete.css'];
$pageScripts = ['assets/js/admin/modals.js'];
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once SERVICES_PATH . '/ProductService.php';
require_once SERVICES_PATH . '/SubcategoryService.php';
require_once REPOS_PATH . '/CategoryRepository.php';

$productService = new ProductService();
$subcatService  = new SubcategoryService();
$catRepo        = new CategoryRepository();
$stats          = $productService->getProductStats();
$products       = $productService->getAllProducts();

// Root categories for the filter dropdowns
$rootCategories = $catRepo->getRootCategories();
$subCategories  = $subcatService->getSubcategories();

// Build grouped structure: [root_cat_id => [root => ..., children => ...]]
$groupedCategories = [];
foreach ($rootCategories as $cat) {
    $groupedCategories[$cat['id']] = ['root' => $cat, 'children' => []];
}
foreach ($subCategories as $sub) {
    if (isset($groupedCategories[$sub['category_id']])) {
        $groupedCategories[$sub['category_id']]['children'][] = $sub;
    }
}

// Pre-filter by category if passed in URL
$preSelectedCategory = $_GET['category'] ?? 'all';
?>

<div class="main-content products-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 products-content-body">
        <!-- Header Section -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 mb-3 border-bottom  px-4 mx-n4">
            <div>
                <h2 class="fw-bold mb-0 products-page-title">Products</h2>
            </div>
            <div class="d-flex gap-2">
                <button class="btn rounded-2 d-flex align-items-center products-outline-btn products-add-btn"
                    data-bs-toggle="offcanvas" data-bs-target="#addProductOffcanvas">
                    <i class="bi bi-plus-lg me-2 fs-5"></i> Add Product
                </button>
            </div>
        </div>

        <div class="px-2 pb-5"> <!-- Inner container for better spacing -->
            <!-- Stat Cards Section -->
            <div class="row g-4 mb-5">
                <!-- Card 1 -->
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="admin-card p-4 h-100 d-flex flex-column justify-content-between" onclick="applyQuickFilter('all')" style="cursor:pointer;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <div class="stat-card-label text-muted fw-bold mb-2 products-stat-label">Total Products
                                </div>
                                <h3 class="stat-card-value fw-bolder mb-0 text-dark products-stat-value">
                                    <?php echo number_format($stats['total']); ?>
                                </h3>
                            </div>
                            <img src="<?php echo BASE_URL; ?>assets/images/admin/product-icon-1.png"
                                alt="Total Products" class="products-stat-icon">
                        </div>
                        <p class="text-muted mt-3 mb-0 products-stat-copy">Across sweets, snacks, gift packs, and
                            seasonal collections.</p>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="admin-card p-4 h-100 d-flex flex-column justify-content-between" onclick="applyQuickFilter('In Stock')" style="cursor:pointer;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <div class="stat-card-label text-muted fw-bold mb-2 products-stat-label">In Stock</div>
                                <h3 class="stat-card-value fw-bolder mb-0 text-dark products-stat-value">
                                    <?php echo number_format($stats['in_stock']); ?>
                                </h3>
                            </div>
                            <img src="<?php echo BASE_URL; ?>assets/images/admin/product-icon-2.png" alt="In Stock"
                                class="products-stat-icon">
                        </div>
                        <p class="text-muted mt-3 mb-0 products-stat-copy">Most popular items are available for
                            dispatch.</p>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="admin-card p-4 h-100 d-flex flex-column justify-content-between" onclick="applyQuickFilter('Low Stock')" style="cursor:pointer;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <div class="stat-card-label text-muted fw-bold mb-2 products-stat-label">Low Stock</div>
                                <h3 class="stat-card-value fw-bolder mb-0 text-dark products-stat-value">
                                    <?php echo number_format($stats['low_stock']); ?>
                                </h3>
                            </div>
                            <img src="<?php echo BASE_URL; ?>assets/images/admin/product-icon-3.png" alt="Low Stock"
                                class="products-stat-icon">
                        </div>
                        <p class="text-muted mt-3 mb-0 products-stat-copy">Review replenishment for high-demand mithai
                            lines.</p>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="col-xl-3 col-md-6 col-12">
                    <div class="admin-card p-4 h-100 d-flex flex-column justify-content-between" onclick="applyQuickFilter('Out of Stock')" style="cursor:pointer;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <div class="stat-card-label text-muted fw-bold mb-2 products-stat-label">Out of Stock
                                </div>
                                <h3 class="stat-card-value fw-bolder mb-0 text-dark products-stat-value">
                                    <?php echo number_format($stats['out_of_stock']); ?>
                                </h3>
                            </div>
                            <img src="<?php echo BASE_URL; ?>assets/images/admin/product-icon-4.png" alt="Out of Stock"
                                class="products-stat-icon">
                        </div>
                        <p class="text-muted mt-3 mb-0 products-stat-copy">Consider restocking before the festive
                            campaign starts.</p>
                    </div>
                </div>
            </div>

            <!-- Filter and Table Section -->
            <div class="mt-5">
                <!-- ── Toolbar Row ── -->
                <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 mb-5">

                    <!-- Search + Mobile trigger always on same row -->
                    <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 0;">
                        <div class="input-group products-search-group"
                            style="max-width: 280px; min-width: 160px; flex: 1 1 160px;">
                            <span class="input-group-text bg-transparent border-0 pe-1 products-search-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" id="productSearch"
                                class="form-control border-0 shadow-none bg-transparent ps-2 placeholder-muted products-filter-input"
                                placeholder="Search by name or SKU...">
                        </div>
                        <!-- Desktop filters inline (hidden on mobile) -->
                        <select class="form-select shadow-none products-filter-select d-none d-md-block"
                            id="categoryFilter" style="width: auto; min-width: 150px;">
                            <option value="all" <?php echo $preSelectedCategory === 'all' ? 'selected' : ''; ?>>All Categories</option>
                            <?php foreach ($rootCategories as $cat):
                                $catId   = (int)$cat['id'];
                                $catSlug = htmlspecialchars($cat['slug']);
                                $catName = htmlspecialchars($cat['name']);
                                $selected = ($preSelectedCategory === $catSlug) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $catSlug; ?>" data-id="<?php echo $catId; ?>" <?php echo $selected; ?>><?php echo $catName; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <select class="form-select shadow-none products-filter-select d-none d-md-block"
                            id="subcategoryFilter" style="width: auto; min-width: 150px;">
                            <option value="all">All Subcategories</option>
                        </select>



                        <select class="form-select shadow-none products-filter-select d-none d-md-block"
                            id="statusFilter" style="width: auto; min-width: 140px;">
                            <option value="all">All Statuses</option>
                            <option value="In Stock">In Stock</option>
                            <option value="Low Stock">Low Stock</option>
                            <option value="Out of Stock">Out of Stock</option>
                        </select>

                        <!-- Mobile filter trigger (right of search) -->
                        <button
                            class="btn btn-light d-md-none border bg-white shadow-sm products-filter-trigger flex-shrink-0"
                            type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                            <i class="bi bi-sliders"></i>
                        </button>
                    </div>
                    <!-- Export CSV (desktop only) -->
                    <button type="button"
                        class="btn shadow-none products-outline-btn products-export-btn d-none d-md-flex align-items-center gap-2">
                        <i class="bi bi-download"></i> Export CSV
                    </button>
                </div>

                <!-- Mobile-only Offcanvas (true offcanvas — never renders inline on desktop) -->
                <div class="offcanvas offcanvas-bottom products-filter-offcanvas" tabindex="-1" id="filterOffcanvas"
                    style="max-height: 80vh; border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title fw-bold" id="filterOffcanvasLabel">Filters</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <label
                                    class="fw-bold form-label products-filter-label text-muted small mb-1">Category</label>
                                <select class="form-select shadow-none products-filter-select w-100"
                                    id="categoryFilterMobile">
                                    <option value="all">All Categories</option>
                                    <?php foreach ($rootCategories as $cat):
                                        $catName = htmlspecialchars($cat['name']);
                                        $isSelected = ($preSelectedCategory === $cat['name']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $catName; ?>" <?php echo $isSelected; ?>><?php echo $catName; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="fw-bold form-label products-filter-label text-muted small mb-1">Stock
                                    Status</label>
                                <select class="form-select shadow-none products-filter-select w-100"
                                    id="statusFilterMobile">
                                    <option value="all">All Statuses</option>
                                    <option value="In Stock">In Stock</option>
                                    <option value="Low Stock">Low Stock</option>
                                    <option value="Out of Stock">Out of Stock</option>
                                </select>
                            </div>
                            <hr class="my-2 text-muted">
                            <button type="button"
                                class="btn shadow-none products-outline-btn products-export-btn w-100 d-flex justify-content-center align-items-center">
                                <i class="bi bi-download me-2"></i> Export CSV
                            </button>
                            <button class="btn btn-dark w-100 py-3 fw-bold border-0" data-bs-dismiss="offcanvas"
                                style="background-color: #8c3333;">Show Results</button>
                        </div>
                    </div>
                </div>


                <h4 class="fw-bold text-dark mb-4 products-table-title">Product Inventory</h4>

                <div class="table-responsive products-table-wrapper">
                    <table class="table align-middle mb-0 products-mobile-card-grid" id="productsTable">
                        <thead class="products-table-head">
                            <tr>
                                <th class="ps-4 py-3 products-table-th products-table-th-check">
                                    &nbsp;
                                </th>
                                <th class="py-3 text-center products-table-th products-table-th-image">Image</th>
                                <th class="py-3 text-center products-table-th">Product name</th>
                                <th class="py-3 text-center d-none d-lg-table-cell products-table-th">Category</th>
                                <th class="py-3 text-center d-none d-md-table-cell products-table-th">Price</th>
                                <th class="py-3 text-center d-none d-sm-table-cell products-table-th">Stock</th>
                                <th class="py-3 text-center products-table-th">Availability</th>
                                <th class="py-3 text-center d-none d-md-table-cell products-table-th" title="Featured Best Seller"><i class="bi bi-star-fill text-warning"></i></th>
                                <th class="py-3 text-center pe-4 products-table-th products-table-th-actions">Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="products-table-body"> <!-- Spacing before rows -->
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr class="product-row" data-product-id="<?php echo $product['id']; ?>"
                                        data-name="<?php echo strtolower($product['name']); ?>"
                                        data-sku="<?php echo strtolower($product['sku'] ?? ''); ?>"
                                        data-status="<?php echo $product['status_label']; ?>"
                                        data-category="<?php echo htmlspecialchars($product['category_name'] ?? ''); ?>"
                                        data-category-slug="<?php echo htmlspecialchars($product['category_slug'] ?? ''); ?>"
                                        data-subcategory="<?php echo htmlspecialchars($product['subcategory_name'] ?? ''); ?>"
                                        data-subcategory-slug="<?php echo htmlspecialchars($product['subcategory_slug'] ?? ''); ?>">
                                        <td class="ps-4 border-0 py-3 td-check">
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input shadow-none products-row-checkbox"
                                                    type="checkbox">
                                            </div>
                                        </td>
                                        <td class="border-0 py-3 text-center td-image">
                                            <img src="<?php echo BASE_URL . $product['image']; ?>"
                                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                class="product-thumb rounded-2 products-thumb-lg">
                                        </td>
                                        <td class="border-0 py-3 text-center td-info">
                                            <div class="fw-bold text-dark mx-auto products-product-name">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </div>
                                            <div class="text-uppercase mx-auto mt-1 products-product-sku">SKU:
                                                <?php echo htmlspecialchars($product['sku'] ?? 'DWP-001'); ?>
                                            </div>
                                        </td>
                                        <td class="d-none d-lg-table-cell border-0 py-3 text-center td-category"
                                            data-label="Category">
                                            <div class="text-dark fw-bold products-cell-md" style="font-size: 13px;">
                                                <?php echo htmlspecialchars($product['category_name'] ?? 'General'); ?>
                                            </div>
                                            <?php if (!empty($product['subcategory_name'])): ?>
                                                <div class="text-muted small mt-1" style="font-size: 11px;">
                                                    <i class="bi bi-arrow-return-right me-1"></i><?php echo htmlspecialchars($product['subcategory_name']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="d-none d-md-table-cell border-0 py-3 text-center td-price"
                                            data-label="Price">
                                            <span class="fw-bolder text-dark products-cell-md">₹
                                                <?php echo number_format($product['price'], 2); ?></span>
                                        </td>
                                        <td class="d-none d-sm-table-cell border-0 py-3 text-center td-stock"
                                            data-label="Stock">
                                            <span
                                                class="text-dark fw-bolder products-cell-md"><?php echo (int) ($product['stock_quantity'] ?? 0); ?>
                                                Units</span>
                                        </td>
                                        <td class="border-0 py-3 text-center td-status" data-label="Availability">
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input class="form-check-input status-toggle-custom" type="checkbox" 
                                                    role="switch"
                                                    data-id="<?php echo $product['id']; ?>" 
                                                    data-type="product"
                                                    <?php echo (strtolower($product['status']) === 'published') ? 'checked' : ''; ?>>
                                            </div>
                                            <div class="small mt-1 text-muted status-toggle-label" style="font-size: 10px;">
                                                <?php echo htmlspecialchars((string)($product['status_label'] ?? 'Out of Stock')); ?>
                                            </div>
                                        </td>
                                        <!-- Featured star -->
                                        <td class="d-none d-md-table-cell border-0 py-3 text-center td-featured">
                                            <?php if (!empty($product['featured'])): ?>
                                                <i class="bi bi-star-fill text-warning fs-5" title="Featured Best Seller"></i>
                                            <?php else: ?>
                                                <i class="bi bi-star text-muted fs-5" title="Not featured"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center pe-4 border-0 py-3 td-actions">
                                            <div class="d-flex justify-content-center gap-2 td-actions-wrapper">
                                                <?php
                                                // Prepare product data for JS (include featured flag)
                                                $jsData = htmlspecialchars(json_encode([
                                                    'id'                => $product['id'],
                                                    'name'              => $product['name'],
                                                    'sku'               => $product['sku'] ?? '',
                                                    'category_id'       => $product['category_id'] ?? '',
                                                    'base_price'        => $product['base_price'],
                                                    'sale_price'        => $product['sale_price'] ?? '',
                                                    'tax_rate'          => $product['tax_rate'] ?? '0.00',
                                                    'stock_quantity'    => $product['stock_quantity'] ?? 0,
                                                    'status'            => $product['status'] ?? 'published',
                                                    'featured'          => (int)($product['featured'] ?? 0),
                                                    'image_path'        => $product['image'],
                                                    'short_description' => $product['short_description'] ?? '',
                                                    'description'       => $product['description'] ?? ''
                                                ]), ENT_QUOTES, 'UTF-8');
                                                ?>
                                                <button type="button"
                                                    class="btn btn-link text-dark p-0 fs-6 text-decoration-none shadow-none"
                                                    title="Edit Product" onclick='openEditProduct(<?php echo $jsData; ?>)'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-link text-dark p-0 fs-6 text-decoration-none shadow-none"
                                                    title="Preview Mode"
                                                    onclick='openPreviewMode(<?php echo $jsData; ?>)'>
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-link text-dark p-0 fs-6 text-decoration-none shadow-none"
                                                    title="Delete Product"
                                                    onclick='openDeleteModal(<?php echo $jsData; ?>)'>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="emptyState">
                                    <td colspan="8">
                                        <div class="empty-state-card">
                                            <div class="empty-state-icon"><i class="bi bi-box-seam"></i></div>
                                            <h5 class="fw-bold">No Products Found</h5>
                                            <p class="text-muted">Start by adding your first luxury sweet collection.</p>
                                            <button class="btn btn-primary rounded-pill px-4 mt-2 products-empty-btn"
                                                data-bs-toggle="offcanvas" data-bs-target="#addProductOffcanvas">
                                                Add New Product
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <!-- Client-side No Results (Hidden by default) -->
                            <tr id="noResults" class="products-no-results-row">
                                <td colspan="8">
                                    <div class="empty-state-card">
                                         <div class="empty-state-icon">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                                 <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                             </svg>
                                         </div>
                                        <h5 class="fw-bold">No matches found</h5>
                                        <p class="text-muted">Try adjusting your search or filters.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Section -->
                <div
                    class="p-4 border-top border-light d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="small text-muted order-2 order-md-1">
                        Page 1 of <?php echo max(1, ceil($stats['total'] / 8)); ?> &bull; 
                        1-<?php echo count($products); ?> of <?php echo $stats['total']; ?> products
                    </div>
                    <nav class="order-1 order-md-2">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link border-0 text-muted" href="#"><i
                                        class="bi bi-chevron-left"></i> Back</a></li>
                            <li class="page-item active"><a
                                    class="page-link border-0 rounded-3 mx-1 products-page-link-active" href="#">1</a>
                            </li>
                            <?php if ($stats['total'] > 8): ?>
                            <li class="page-item"><a class="page-link border-0 rounded-3 mx-1" href="#">2</a></li>
                            <?php endif; ?>
                            <?php if ($stats['total'] > 16): ?>
                            <li class="page-item"><a class="page-link border-0 rounded-3 mx-1" href="#">3</a></li>
                            <?php endif; ?>
                            <li class="page-item disabled"><a class="page-link border-0 text-dark fw-bold ms-2" href="#">Next <i
                                        class="bi bi-chevron-right"></i></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Offcanvas Drawer -->
    <div class="offcanvas offcanvas-end border-0 products-add-offcanvas" tabindex="-1" id="addProductOffcanvas"
        aria-labelledby="addProductLabel">
        <div class="offcanvas-header pt-4 pb-2 px-4 d-flex justify-content-between align-items-start">
            <h3 class="fw-bolder text-dark mb-0 products-add-title" id="addProductLabel">Add Product</h3>
            <button type="button" class="btn p-0 shadow-none border-0 pe-2 pt-1 products-close-btn"
                data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="bi bi-x-lg products-close-icon"></i>
            </button>
        </div>
        <div class="offcanvas-body px-4 pt-4 custom-scrollbar position-relative pb-5">
            <!-- Add Product Form -->
            <form id="addProductForm" action="api/v1/products.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                <!-- Row 1: Product Name -->
                <div class="mb-3">
                    <label class="form-label form-label-custom d-block">Product Name</label>
                    <input type="text" name="name" class="form-control form-control-custom shadow-none"
                        placeholder="Enter Product Name" required>
                </div>

                <!-- Row 2: SKU & Category -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label form-label-custom d-block">SKU (Product ID)</label>
                        <input type="text" name="sku" class="form-control form-control-custom shadow-none"
                            placeholder="e.g. VK-101">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label form-label-custom d-block">Category</label>
                        <select name="category_id" id="add_category_id" class="form-select form-select-custom shadow-none" required onchange="loadSubcategories(this.value, 'add_subcategory_id')">
                            <option value="" disabled selected>Select Category</option>
                            <?php foreach ($groupedCategories as $group): ?>
                                <option value="<?php echo $group['root']['id']; ?>">
                                    <?php echo htmlspecialchars($group['root']['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label form-label-custom d-block">Subcategory</label>
                    <select name="subcategory_id" id="add_subcategory_id" class="form-select form-select-custom shadow-none">
                        <option value="">Select Category First</option>
                    </select>
                </div>

                <!-- Row 3: Price | Discount | Tax -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label form-label-custom d-block">Base Price (₹)</label>
                        <input type="number" step="0.01" name="base_price"
                            class="form-control form-control-custom shadow-none" placeholder="0.00" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label form-label-custom d-block">Sale Price (₹)</label>
                        <input type="number" step="0.01" name="sale_price"
                            class="form-control form-control-custom shadow-none" placeholder="Optional">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label form-label-custom d-block">Tax Rate (%)</label>
                        <input type="number" step="0.01" name="tax_rate"
                            class="form-control form-control-custom shadow-none" placeholder="18.00">
                    </div>
                </div>

                <!-- Row 4: Stock Quantity & Status -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label form-label-custom d-block">Initial Stock</label>
                        <input type="number" name="stock_quantity" class="form-control form-control-custom shadow-none"
                            placeholder="0" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label form-label-custom d-block">Initial Status</label>
                        <select name="status" class="form-select form-select-custom shadow-none products-stock-select">
                            <option value="published" selected>Published</option>
                            <option value="draft">Draft</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>
                </div>

                <!-- Featured / Best Seller Toggle -->
                <div class="mb-4 p-3 rounded-3" style="background:#fffbf2; border:1px solid #f0e6c8;">
                    <div class="form-check form-switch d-flex align-items-center gap-3">
                        <input class="form-check-input shadow-none" type="checkbox" role="switch"
                            name="featured" id="add_featured" value="1" style="width:2.5em;height:1.4em;">
                        <label class="form-check-label fw-bold" for="add_featured">
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            Feature in Best Sellers section
                        </label>
                    </div>
                    <div class="text-muted small mt-1 ps-1">Checked products appear in the homepage "Our Best Sellers" carousel.</div>
                </div>

                <!-- Row 5: Main Product Image -->
                <div class="mb-4">
                    <label class="form-label form-label-custom d-block">Primary Product Image</label>
                    <div class="file-overlay-wrapper input-group-file">
                        <div class="input-group-file-text">Choose image...</div>
                        <button type="button" class="input-group-file-btn">Browse</button>
                        <input type="file" name="product_image" class="file-overlay-input" accept="image/*"
                            onchange="updateFileName(this)">
                    </div>
                </div>

                <!-- Row 6: Short Description -->
                <div class="mb-3">
                    <label class="form-label form-label-custom d-block">Short Description</label>
                    <input type="text" name="short_description" class="form-control form-control-custom shadow-none"
                        placeholder="Brief summary for catalog cards">
                </div>

                <!-- Row 7: Full Description -->
                <div class="mb-4">
                    <label class="form-label form-label-custom d-block">Full Description</label>
                    <textarea name="description" class="form-control form-control-custom shadow-none products-no-resize"
                        rows="5" placeholder="Detailed technical and culinary notes"></textarea>
                </div>

                <!-- Info Note -->
                <div class="alert alert-info py-2 small mb-4">
                    <i class="bi bi-info-circle me-1"></i> <strong>Note:</strong> You can add specific weight variants (250g, 500g, 1kg) from the <strong>Edit Product</strong> page after creating this base product.
                </div>

                <!-- Footer Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="button" class="btn fw-bold rounded-2 px-4 shadow-none products-cancel-btn"
                        data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn fw-bold rounded-2 px-4 shadow-none products-save-btn">Create
                        Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Offcanvas -->
<div class="offcanvas offcanvas-end products-add-panel border-0 shadow-lg" tabindex="-1" id="editProductOffcanvas"
    aria-labelledby="editProductLabel">
    <div class="offcanvas-header border-bottom py-4 px-4 d-flex justify-content-between align-items-center">
        <h3 class="offcanvas-title fw-bolder text-dark products-panel-title" id="editProductLabel">Edit Product</h3>
        <button type="button" class="btn p-0 shadow-none border-0 pe-2 products-close-btn" data-bs-dismiss="offcanvas"
            aria-label="Close">
            <i class="bi bi-x-lg text-muted fs-4"></i>
        </button>
    </div>
    <div class="offcanvas-body custom-scrollbar px-4 py-4">
        <div class="products-add-form-wrapper">
            <form id="editProductForm" action="api/v1/products.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="hidden" name="product_id" id="edit_product_id">

                <div class="mb-3">
                    <label class="form-label form-label-custom d-block">Product Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control form-control-custom shadow-none"
                        required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label form-label-custom d-block">SKU</label>
                        <input type="text" name="sku" id="edit_sku"
                            class="form-control form-control-custom shadow-none">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label form-label-custom d-block">Category</label>
                        <select name="category_id" id="edit_category_id" class="form-select form-select-custom shadow-none" required onchange="loadSubcategories(this.value, 'edit_subcategory_id')">
                            <option value="">Select Category</option>
                            <?php foreach ($groupedCategories as $group): ?>
                                <option value="<?php echo $group['root']['id']; ?>">
                                    <?php echo htmlspecialchars($group['root']['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label form-label-custom d-block">Subcategory</label>
                    <select name="subcategory_id" id="edit_subcategory_id" class="form-select form-select-custom shadow-none">
                        <option value="">Select Category First</option>
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label form-label-custom d-block">Price</label>
                        <input type="number" step="0.01" name="base_price" id="edit_base_price"
                            class="form-control form-control-custom shadow-none" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label form-label-custom d-block">Sale Price</label>
                        <input type="number" step="0.01" name="sale_price" id="edit_sale_price"
                            class="form-control form-control-custom shadow-none">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label form-label-custom d-block">Tax (%)</label>
                        <input type="number" step="0.01" name="tax_rate" id="edit_tax_rate"
                            class="form-control form-control-custom shadow-none">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label form-label-custom d-block">Stock</label>
                        <input type="number" name="stock_quantity" id="edit_stock_quantity"
                            class="form-control form-control-custom shadow-none" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label form-label-custom d-block">Status</label>
                        <select name="status" id="edit_status" class="form-select form-select-custom shadow-none">
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>
                </div>

                <!-- Featured / Best Seller Toggle -->
                <div class="mb-4 p-3 rounded-3" style="background:#fffbf2; border:1px solid #f0e6c8;">
                    <div class="form-check form-switch d-flex align-items-center gap-3">
                        <input class="form-check-input shadow-none" type="checkbox" role="switch"
                            name="featured" id="edit_featured" value="1" style="width:2.5em;height:1.4em;">
                        <label class="form-check-label fw-bold" for="edit_featured">
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            Feature in Best Sellers section
                        </label>
                    </div>
                    <div class="text-muted small mt-1 ps-1">Checked products appear in the homepage "Our Best Sellers" carousel.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label form-label-custom d-block">Update Image (Leave blank to keep
                        current)</label>
                    <div class="file-overlay-wrapper input-group-file">
                        <div class="input-group-file-text">Choose new...</div>
                        <button type="button" class="input-group-file-btn">Browse</button>
                        <input type="file" name="product_image" class="file-overlay-input" accept="image/*"
                            onchange="updateFileName(this)">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label form-label-custom d-block">Short Description</label>
                    <input type="text" name="short_description" id="edit_short_desc"
                        class="form-control form-control-custom shadow-none">
                </div>

                <div class="mb-4">
                    <label class="form-label form-label-custom d-block">Full Description</label>
                    <textarea name="description" id="edit_desc"
                        class="form-control form-control-custom shadow-none products-no-resize" rows="5"></textarea>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="button" class="btn fw-bold rounded-2 px-4 shadow-none products-cancel-btn"
                        data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn fw-bold rounded-2 px-4 shadow-none products-save-btn">Update
                        Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>

<?php 
// Include Global Modals
require_once 'includes/modals/product-preview.php';
require_once 'includes/modals/delete-confirm.php';
?>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('productSearch');
        const categoryFilter = document.getElementById('categoryFilter');
        const subcategoryFilter = document.getElementById('subcategoryFilter');
        const statusFilter = document.getElementById('statusFilter');
        const categoryFilterMob = document.getElementById('categoryFilterMobile');
        const statusFilterMob = document.getElementById('statusFilterMobile');
        const rows = document.querySelectorAll('.product-row');
        const noResultsRow = document.getElementById('noResults');

        function filterProducts() {
            const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
            const selCategory = (categoryFilter && categoryFilter.value !== 'all') ? categoryFilter.value : (categoryFilterMob ? categoryFilterMob.value : 'all');
            const subVal = subcategoryFilter ? subcategoryFilter.value : 'all';
            const selSubcategory = (subVal !== 'all' && subVal !== '' && !subVal.includes('oading') && !subVal.includes('rror') && !subVal.includes('ailed')) ? subVal : 'all';
            const selStatus = (statusFilter && statusFilter.value !== 'all') ? statusFilter.value : (statusFilterMob ? statusFilterMob.value : 'all');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = (row.getAttribute('data-name') || '').toLowerCase();
                const sku = (row.getAttribute('data-sku') || '').toLowerCase();
                const status = row.getAttribute('data-status') || '';
                const categorySlug = row.getAttribute('data-category-slug') || '';
                const subcategorySlug = row.getAttribute('data-subcategory-slug') || '';

                const matchesSearch = name.includes(searchTerm) || sku.includes(searchTerm);
                const matchesStatus = (selStatus === 'all' || status === selStatus);
                const selCatLower = selCategory.toLowerCase();
                const selSubLower = selSubcategory.toLowerCase();

                const matchesCategory = (selCategory === 'all' || categorySlug.toLowerCase() === selCatLower);
                const matchesSubcategory = (selSubcategory === 'all' || subcategorySlug.toLowerCase() === selSubLower);

                if (matchesSearch && matchesStatus && matchesCategory && matchesSubcategory) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noResultsRow) {
                noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        }

        window.applyQuickFilter = function(status) {
            if (statusFilter) {
                statusFilter.value = status;
                // Highlight the filter area momentarily
                statusFilter.style.backgroundColor = '#fffbf2';
                setTimeout(() => statusFilter.style.backgroundColor = '', 500);
            }
            if (statusFilterMob) statusFilterMob.value = status;
            filterProducts();
            
            // Scroll to table on mobile
            if (window.innerWidth < 768) {
                document.querySelector('.products-table-wrapper').scrollIntoView({ behavior: 'smooth' });
            }
        };

        // Sync desktop ↔ mobile category dropdowns
        if (categoryFilter && categoryFilterMob) {
            categoryFilter.addEventListener('change', () => { categoryFilterMob.value = categoryFilter.value; filterProducts(); });
            categoryFilterMob.addEventListener('change', () => { categoryFilter.value = categoryFilterMob.value; filterProducts(); });
        }

        if (searchInput) searchInput.addEventListener('input', filterProducts);
        if (statusFilter) statusFilter.addEventListener('change', filterProducts);
        if (categoryFilter) {
            categoryFilter.addEventListener('change', function() {
                loadSubcategories(this.options[this.selectedIndex].getAttribute('data-id') || 0, 'subcategoryFilter', true);
                filterProducts();
            });
        }
        if (subcategoryFilter) subcategoryFilter.addEventListener('change', filterProducts);
        if (statusFilterMob) statusFilterMob.addEventListener('change', filterProducts);
        if (categoryFilterMob) categoryFilterMob.addEventListener('change', filterProducts);

        // Auto-apply search from URL (from Global Search)
        const urlParams = new URLSearchParams(window.location.search);
        const urlSearch = urlParams.get('search');
        if (urlSearch && searchInput) {
            searchInput.value = urlSearch;
            setTimeout(filterProducts, 100);
        }

        // --- Industrial Product Management Logic ---

        function updateFileName(input) {
            const fileName = input.files[0] ? input.files[0].name : "Choose image...";
            const wrapper = input.closest('.file-overlay-wrapper');
            if (wrapper) {
                const textEl = wrapper.querySelector('.input-group-file-text');
                if (textEl) textEl.textContent = fileName;
            }
        }
        window.updateFileName = updateFileName;

        const handleProductSubmit = async (formId) => {
            const form = document.getElementById(formId);
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                try {
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                    submitBtn.disabled = true;

                    const formData = new FormData(form);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const response = await fetch('api/v1/products.php', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        // Success Feedback
                        alert(result.message);
                        window.location.reload(); // Refresh to show new data
                    } else {
                        throw new Error(result.message || 'Operation failed');
                    }
                } catch (error) {
                    console.error('[AdminError]', error);
                    alert('Error: ' + error.message);
                } finally {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });
        };

        handleProductSubmit('addProductForm');
        handleProductSubmit('editProductForm');

        // Weight tab toggle
        document.querySelectorAll('.weight-tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.weight-tab').forEach(function(t) { t.classList.remove('active'); });
                tab.classList.add('active');
            });
        });

        // Availability status toggle (dynamic business logic)
        document.querySelectorAll('.status-toggle-custom').forEach(function(toggle) {
            toggle.addEventListener('change', async function () {
                const productId = this.getAttribute('data-id');
                const nextStatus = this.checked ? 'published' : 'out_of_stock';
                const prevChecked = !this.checked;
                const rowEl = this.closest('.product-row');
                const labelEl = this.closest('.td-status')?.querySelector('.status-toggle-label');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const deriveStatusLabel = () => {
                    if (!this.checked) {
                        return 'Out of Stock';
                    }

                    const stockCell = rowEl ? rowEl.querySelector('.td-stock .products-cell-md') : null;
                    const stockText = stockCell ? stockCell.textContent : '0';
                    const stock = parseInt((stockText || '').replace(/[^0-9]/g, ''), 10) || 0;

                    if (stock > 10) {
                        return 'In Stock';
                    }
                    if (stock > 0) {
                        return 'Low Stock';
                    }
                    return 'Out of Stock';
                };

                const updateVisualState = () => {
                    const newLabel = deriveStatusLabel();
                    if (labelEl) {
                        labelEl.textContent = newLabel;
                    }
                    if (rowEl) {
                        rowEl.setAttribute('data-status', newLabel);
                    }
                };

                this.disabled = true;

                try {
                    const formData = new FormData();
                    formData.append('action', 'toggle_status');
                    formData.append('product_id', productId);
                    formData.append('status', nextStatus);
                    formData.append('csrf_token', csrfToken);

                    const response = await fetch('api/v1/products.php', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: formData
                    });

                    const result = await response.json();
                    if (result.status !== 'success') {
                        throw new Error(result.message || 'Failed to update availability');
                    }

                    updateVisualState();
                } catch (error) {
                    this.checked = prevChecked;
                    updateVisualState();
                    alert(error.message || 'Could not update availability.');
                } finally {
                    this.disabled = false;
                }
            });
        });

        // Initial trigger for pre-selected category
        if (categoryFilter && categoryFilter.value !== 'all') {
            filterProducts();
        }

        // --- CSV Export Engine ---
        const exportButtons = document.querySelectorAll('.products-export-btn');
        exportButtons.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = 'api/v1/products.php?action=export_csv';
            });
        });
    });

    // Navigate to dedicated edit page - declared immediately in global scope
    window.openEditProduct = (data) => {
        const productId = data && data.id ? data.id : '';
        const basePath = 'edit-product.php';
        const targetUrl = productId
            ? `${basePath}?id=${encodeURIComponent(productId)}`
            : basePath;
        window.location.href = targetUrl;
    };

    // Pre-populate edit offcanvas with featured flag
    // (Kept here for fallback use if openEditProduct stays inline in future)
    document.addEventListener('DOMContentLoaded', () => {
        const editOffcanvas = document.getElementById('editProductOffcanvas');
        if (!editOffcanvas) return;
        editOffcanvas.addEventListener('show.bs.offcanvas', () => {
            // featured checkbox pre-fill is handled via openEditProductInline(data) below
        });
    });

    // Inline edit (fallback) — sets featured checkbox when data available
    window.openEditProductInline = async (data) => {
        const featuredEl = document.getElementById('edit_featured');
        if (featuredEl) featuredEl.checked = !!data.featured;
        const idEl = document.getElementById('edit_product_id');
        if (idEl) idEl.value = data.id || '';
        ['name','sku','base_price','sale_price','tax_rate','stock_quantity','status','short_description','description'].forEach(field => {
            const map = { name:'edit_name', sku:'edit_sku', base_price:'edit_base_price',
                          sale_price:'edit_sale_price', tax_rate:'edit_tax_rate',
                          stock_quantity:'edit_stock_quantity', status:'edit_status',
                          short_description:'edit_short_desc', description:'edit_desc' };
            const el = document.getElementById(map[field]);
            if (el && data[field] !== undefined) el.value = data[field];
        });
        const catEl = document.getElementById('edit_category_id');
        if (catEl && data.category_id) {
            catEl.value = data.category_id;
            // Load subcategories for this category and then select the right one
            await window.loadSubcategories(data.category_id, 'edit_subcategory_id');
            const subCatEl = document.getElementById('edit_subcategory_id');
            if (subCatEl && data.subcategory_id) {
                subCatEl.value = data.subcategory_id;
            }
        }
        const bs = bootstrap && bootstrap.Offcanvas
            ? bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('editProductOffcanvas'))
            : null;
        if (bs) bs.show();
    };
    window.loadSubcategories = async (categoryId, targetSelectId, isFilter = false) => {
        const select = document.getElementById(targetSelectId);
        if (!select) return;

        select.innerHTML = '<option value="">Loading...</option>';
        
        try {
            const response = await fetch(`api/v1/subcategories.php?category_id=${categoryId}`);
            const result = await response.json();
            
            if (result.success) {
                let html = isFilter ? '<option value="all">All Subcategories</option>' : '<option value="">Select Subcategory</option>';
                result.data.forEach(sub => {
                    const value = isFilter ? sub.slug : sub.id;
                    html += `<option value="${value}">${sub.name}</option>`;
                });
                select.innerHTML = html;
            } else {
                select.innerHTML = '<option value="">Error loading</option>';
            }
        } catch (error) {
            select.innerHTML = '<option value="">Failed to load</option>';
        }
    };
</script>

<?php require_once 'includes/footer.php'; ?>
