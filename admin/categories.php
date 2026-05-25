<?php
/**
 * Sweets Website
 * =============================================================
 * File: categories.php
 * Description: Category Management – List and Add
 * =============================================================
 */

$pageStyles = ['assets/css/admin/products.css', 'assets/css/pages/admin-categories.css', 'assets/css/admin/pages/category-add-premium.css', 'assets/css/admin/pages/product-preview.css', 'assets/css/admin/pages/product-delete.css'];
$pageScripts = ['assets/js/admin/modals.js'];
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once REPOS_PATH . '/CategoryRepository.php';
require_once ROOT_PATH . '/config/Database.php';

$categoryRepo = new CategoryRepository();
$categories = $categoryRepo->getRootCategories();
?>

<div class="main-content products-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 products-content-body">
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 mb-3 border-bottom px-4 mx-n4">
            <div>
                <h2 class="fw-bold mb-0 products-page-title">Categories</h2>
            </div>
            <div class="d-flex gap-2">
                <button class="btn rounded-2 d-flex align-items-center products-outline-btn products-add-btn"
                    onclick="openPremiumCategoryPanel()">
                    <i class="bi bi-plus-lg me-2 fs-5"></i> Add Category
                </button>
            </div>
        </div>

        <div class="px-2 pb-5">
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
                            <input type="text" id="categorySearch"
                                class="form-control border-0 shadow-none bg-transparent ps-2 placeholder-muted products-filter-input"
                                placeholder="Search category name...">
                        </div>
                        <button
                            class="btn btn-light d-md-none border bg-white shadow-sm products-filter-trigger flex-shrink-0"
                            type="button" data-bs-toggle="offcanvas" data-bs-target="#categoryFilterOffcanvas">
                            <i class="bi bi-sliders"></i>
                        </button>

                        <select class="form-select shadow-none products-filter-select d-none d-md-block"
                            id="statusFilter" style="width: auto; min-width: 150px;">
                            <option value="all">Status All</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>


                    <button
                        class="btn shadow-none products-outline-btn products-export-btn d-none d-md-flex align-items-center gap-2">
                        Export CSV
                    </button>
                </div>

                <div class="offcanvas offcanvas-bottom products-filter-offcanvas" tabindex="-1"
                    id="categoryFilterOffcanvas"
                    style="max-height: 80vh; border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title fw-bold" id="categoryFilterOffcanvasLabel">Filters</h5>
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
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <hr class="my-2 text-muted">
                            <button class="btn btn-dark w-100 py-3 fw-bold border-0" data-bs-dismiss="offcanvas"
                                style="background-color: #8c3333;">Show Results</button>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-12">
                        <h4 class="fw-bold text-dark mb-4 products-table-title">Category List</h4>
                        <div class="table-responsive products-table-wrapper">
                            <table class="table align-middle mb-0 products-mobile-card-grid" id="categoriesTable">
                                <thead class="products-table-head">
                                    <tr>
                                        <th class="ps-4 py-3 products-table-th products-table-th-check">&nbsp;</th>
                                        <th class="py-3 products-table-th">Category Details</th>
                                        <th class="py-3 text-center products-table-th">Products</th>
                                        <th class="py-3 text-center products-table-th">Status</th>
                                        <th class="py-3 pe-4 text-center products-table-th">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="products-table-body">
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $index => $cat): ?>
                                            <tr class="product-row" data-name="<?php echo strtolower($cat['name']); ?>"
                                                data-status="<?php echo ($cat['status'] === 'active' || $cat['status'] == 1) ? 'Active' : 'Inactive'; ?>">
                                                <td class="ps-4 border-0 py-3 td-check">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input class="form-check-input shadow-none products-row-checkbox"
                                                            type="checkbox">
                                                    </div>
                                                </td>
                                                <td class="border-0 py-3 td-info">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <?php
                                                        $placeholder = 'assets/images/placeholders/product-placeholder.png';
                                                        $actualImg = !empty($cat['image_path']) ? $cat['image_path'] : (!empty($cat['product_image']) ? $cat['product_image'] : $placeholder);
                                                        $imgSrc = BASE_URL . $actualImg;
                                                        ?>
                                                        <img src="<?php echo $imgSrc; ?>"
                                                            alt="<?php echo htmlspecialchars($cat['name']); ?>"
                                                            class="rounded-3 product-thumb"
                                                            style="width: 50px; height: 50px; object-fit: cover;"
                                                            onerror="this.src='<?php echo BASE_URL . 'assets/images/placeholders/product-placeholder.png'; ?>'">
                                                        <div>
                                                            <div class="fw-bold text-dark products-product-name">
                                                                <a href="category-details.php?id=<?php echo urlencode($cat['id']); ?>"
                                                                    class="text-dark text-decoration-none hover-primary">
                                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                                </a>
                                                            </div>
                                                            <div class="text-uppercase text-muted mt-1 fw-bold"
                                                                style="font-size: 10px; letter-spacing: 0.5px;">
                                                                SLUG : <?php echo htmlspecialchars($cat['slug']); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="border-0 py-3 text-center td-stock" data-label="Products">
                                                    <a href="products.php?category=<?php echo urlencode($cat['slug']); ?>"
                                                        class="text-decoration-none fw-bold text-dark hover-primary"
                                                        style="font-size: 14px;" title="View in admin">
                                                        <?php echo htmlspecialchars($cat['product_count'] ?? 0); ?> Items
                                                        <i class="bi bi-box-arrow-in-right ms-1" style="font-size: 10px;"></i>
                                                    </a>
                                                </td>
                                                <td class="border-0 py-3 text-center td-status" data-label="Status">
                                                    <div class="form-check form-switch d-flex justify-content-center">
                                                        <input class="form-check-input status-toggle-custom" type="checkbox" 
                                                            role="switch"
                                                            data-id="<?php echo $cat['id']; ?>" 
                                                            data-type="category"
                                                            <?php echo ($cat['status'] === 'active' || $cat['status'] == 1) ? 'checked' : ''; ?>>
                                                    </div>
                                                </td>
                                                <td class="border-0 py-3 text-center pe-4 td-actions">
                                                    <div class="d-flex justify-content-center gap-2 td-actions-wrapper">
                                                        <?php
                                                        $catJson = htmlspecialchars(json_encode([
                                                            'id' => $cat['id'],
                                                            'name' => $cat['name'],
                                                            'image_path' => $cat['image_path'] ?? 'assets/images/placeholders/product-placeholder.png',
                                                            'product_count' => $cat['product_count'] ?? 0,
                                                            'status' => ($cat['status'] === 'active' || $cat['status'] == 1) ? 1 : 0
                                                        ]), ENT_QUOTES, 'UTF-8');
                                                        ?>
                                                        <button type="button" 
                                                            class="btn btn-link text-dark p-0 fs-6 text-decoration-none shadow-none" 
                                                            title="View Details"
                                                            onclick='openPremiumCategoryPanel(<?php echo $catJson; ?>)'>
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button type="button" 
                                                            class="btn btn-link text-dark p-0 fs-6 text-decoration-none shadow-none" 
                                                            title="Edit"
                                                            onclick='openPremiumCategoryPanel(<?php echo $catJson; ?>)'>
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                       
                                                        <button type="button"
                                                            class="btn btn-link text-dark p-0 fs-6 text-decoration-none shadow-none"
                                                            title="Delete"
                                                            onclick='openDeleteModal(<?php echo $catJson; ?>, "category")'>
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr id="emptyState">
                                            <td colspan="5" class="text-center py-5 italic text-muted border-0">
                                                <div class="empty-state-card mt-3">
                                                    <div class="empty-state-icon"><i class="bi bi-box-seam"></i></div>
                                                    <h5 class="fw-bold">No Categories Found</h5>
                                                    <p class="text-muted">Start by adding your first category.</p>
                                                    <button
                                                        class="btn btn-primary rounded-pill px-4 mt-2 products-empty-btn"
                                                        onclick="openPremiumCategoryPanel()">
                                                        Add New Category
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <tr id="noResults" class="products-no-results-row" style="display: none;">
                                        <td colspan="5">
                                             <div class="empty-state-card my-3 text-center">
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

                        <div
                            class="p-4 border-top border-light d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                            <div class="small text-muted order-2 order-md-1">
                                Page 1 of 1 &bull; 1-<?php echo count($categories); ?> of
                                <?php echo count($categories); ?> categories
                            </div>
                            <nav class="order-1 order-md-2">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item disabled"><a class="page-link border-0 text-muted" href="#"><i
                                                class="bi bi-chevron-left"></i> Back</a></li>
                                    <li class="page-item active"><a
                                            class="page-link border-0 rounded-3 mx-1 products-page-link-active"
                                            href="#">1</a></li>
                                    <li class="page-item disabled"><a class="page-link border-0 text-dark fw-bold ms-2"
                                            href="#">Next <i class="bi bi-chevron-right"></i></a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('categorySearch');
        const statusFilter = document.getElementById('statusFilter');
        const statusFilterMob = document.getElementById('statusFilterMobile');
        const rows = document.querySelectorAll('.product-row');
        const noResultsRow = document.querySelector('#noResults');

        function filterCategories() {
            const searchTerm = searchInput.value.toLowerCase();
            const selStatus = (statusFilter && statusFilter.offsetParent !== null)
                ? statusFilter.value
                : (statusFilterMob ? statusFilterMob.value : 'all');

            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const status = row.getAttribute('data-status') || '';

                const matchesSearch = name.includes(searchTerm);
                const matchesStatus = (selStatus === 'all' || status === selStatus);

                if (matchesSearch && matchesStatus) {
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

        if (searchInput) searchInput.addEventListener('input', filterCategories);
        if (statusFilter) statusFilter.addEventListener('change', filterCategories);
        if (statusFilterMob) statusFilterMob.addEventListener('change', filterCategories);

        const exportBtn = document.querySelector('.products-export-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                let csvContent = "data:text/csv;charset=utf-8,Category Name,Slug,Products Count,Status\n";
                
                rows.forEach(row => {
                    if (row.style.display !== 'none') {
                        const name = row.getAttribute('data-name') || '';
                        
                        const slugEl = row.querySelector('.text-uppercase');
                        const slug = slugEl ? slugEl.innerText.replace('SLUG :', '').trim() : '';
                        
                        const stockEl = row.querySelector('.td-stock');
                        const products = stockEl ? stockEl.innerText.replace('Items', '').trim() : '0';
                        
                        const status = row.getAttribute('data-status') || '';
                        
                        const safeName = '"' + name.replace(/"/g, '""') + '"';
                        csvContent += `${safeName},${slug},${products},${status}\n`;
                    }
                });
                
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "categories_export.csv");
                document.body.appendChild(link);
                link.click();
                link.remove();
            });
        }

        // Handle Status Toggles dynamically
        document.querySelectorAll('.status-toggle-custom').forEach(toggle => {
            toggle.addEventListener('change', async function() {
                const id = this.getAttribute('data-id');
                const nextStatus = this.checked ? 'active' : 'inactive';
                const rowEl = this.closest('.product-row');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                this.disabled = true;
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'toggle_status');
                    formData.append('id', id);
                    formData.append('status', nextStatus);
                    if (csrfToken) formData.append('csrf_token', csrfToken);

                    const response = await fetch('api/v1/categories.php', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        if (rowEl) {
                            rowEl.setAttribute('data-status', nextStatus === 'active' ? 'Active' : 'Inactive');
                        }
                    } else {
                        throw new Error(result.message || 'Failed to update status');
                    }
                } catch (error) {
                    this.checked = !this.checked; // Revert visually
                    alert('Error: ' + error.message);
                } finally {
                    this.disabled = false;
                }
            });
        });

    });
</script>

<?php
// Include Global Modals
require_once 'includes/modals/product-preview.php';
require_once 'includes/modals/delete-confirm.php';
require_once 'includes/modals/category-premium-modal.php';
?>

<?php require_once 'includes/footer.php'; ?>