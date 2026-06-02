<?php
/**
 * Sweets Website
 * =============================================================
 * File: subcategories.php
 * Description: Subcategory Management Table (V3 Schema UI)
 * =============================================================
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once SERVICES_PATH . '/SubcategoryService.php';
require_once REPOS_PATH . '/CategoryRepository.php';

$subcategoryService = new SubcategoryService();
$categoryRepo = new CategoryRepository();

$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;
$currentCategory = null;

if ($categoryId) {
    $currentCategory = $categoryRepo->getById($categoryId);
}

$totalItems = $subcategoryService->countSubcategories($categoryId > 0 ? $categoryId : null);

$totalPages = $totalItems > 0 ? ceil($totalItems / $perPage) : 1;
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$subcategories = $subcategoryService->getPaginatedSubcategories($perPage, $offset, $categoryId > 0 ? $categoryId : null);

$startItem = $totalItems > 0 ? $offset + 1 : 0;
$endItem = min($offset + $perPage, $totalItems);

function buildPageUrl(int $newPage, int $parentId): string
{
    $params = ['page' => $newPage];
    if ($parentId > 0) {
        $params['parent_id'] = $parentId;
    }
    return '?' . http_build_query($params);
}

$parentCategoriesForSelect = $categoryRepo->getRootCategories();

$pageStyles = [
    'assets/css/admin/products.css', 
    'assets/css/admin/pages/subcategory-add-panel.css', 
    'assets/css/admin/pages/product-preview.css', 
    'assets/css/admin/pages/product-delete.css'
];
$pageScripts = ['assets/js/admin/modals.js', 'assets/js/admin/subcategories-bulk.js'];
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
?>

<div class="main-content products-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 products-content-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 mb-3 border-bottom px-4 mx-n4">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0 small fw-bold">
                        <li class="breadcrumb-item"><a href="categories.php" class="text-decoration-none" style="color: #8c3333;">Categories</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Subcategories</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-0 products-page-title">
                    <?php echo $currentCategory ? htmlspecialchars($currentCategory['name']) : 'All Subcategories'; ?>
                </h2>
            </div>
            <div class="d-flex gap-2">
                <button class="btn rounded-2 d-flex align-items-center products-outline-btn products-add-btn" type="button" onclick="openSubcategoryPanel()">
                    <i class="bi bi-plus-lg me-2 fs-5"></i> Add Subcategory
                </button>
                <button class="btn rounded-2 d-flex align-items-center products-outline-btn products-bulk-btn" type="button">
                    Bulk Update
                </button>
            </div>
        </div>

        <div class="px-2 pb-5">
            <div class="mt-4">
                <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 mb-5">
                    <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 0;">
                        <div class="input-group products-search-group" style="max-width: 280px; min-width: 160px; flex: 1 1 160px;">
                            <span class="input-group-text bg-transparent border-0 pe-1 products-search-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" id="subcategorySearch" class="form-control border-0 shadow-none bg-transparent ps-2 placeholder-muted products-filter-input" placeholder="Search by name or SKU.....">
                        </div>
                    </div>

                    <select class="form-select shadow-none products-filter-select" id="dateFilter" style="width: auto; min-width: 130px;">
                        <option value="all">All Time</option>
                        <option value="30">Last 30 Days</option>
                        <option value="90" selected>Last 90 Days</option>
                        <option value="180">Last 6 Months</option>
                        <option value="365">Last 1 Year</option>
                    </select>

                    <select class="form-select shadow-none products-filter-select" id="statusFilter" style="width: auto; min-width: 130px;">
                        <option value="all">Status All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <h4 class="fw-bold text-dark mb-4 products-table-title">Subcategory List</h4>
                <div class="table-responsive products-table-wrapper">
                    <table class="table align-middle mb-0 products-mobile-card-grid" id="subcategoriesTable">
                        <thead class="products-table-head">
                            <tr>
                                <th class="ps-4 py-3 products-table-th products-table-th-check">
                                    <div class="form-check">
                                        <input class="form-check-input products-row-checkbox" type="checkbox" id="selectAllSubcats">
                                    </div>
                                </th>
                                <th class="py-3 products-table-th">Subcategory Details</th>
                                <th class="py-3 d-none d-lg-table-cell products-table-th">Description</th>
                                <th class="py-3 text-center products-table-th">Total Products</th>
                                <th class="py-3 text-center products-table-th">Status</th>
                                <th class="py-3 pe-4 text-center products-table-th">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="products-table-body">
                            <?php if (!empty($subcategories)): ?>
                                <?php foreach ($subcategories as $index => $sub): ?>
                                    <tr class="product-row" data-name="<?php echo htmlspecialchars(strtolower($sub['name'])); ?>" data-slug="<?php echo htmlspecialchars(strtolower($sub['slug'] ?? '')); ?>" data-status="<?php echo htmlspecialchars(strtolower($sub['status'])); ?>" data-created="<?php echo !empty($sub['created_at']) ? strtotime($sub['created_at']) : time(); ?>">
                                        <td class="border-0 ps-4 py-3 td-check">
                                            <div class="form-check">
                                                <input class="form-check-input products-row-checkbox" type="checkbox" value="<?php echo $sub['id']; ?>">
                                            </div>
                                        </td>
                                        <td class="border-0 py-3 td-info">
                                            <div class="d-flex align-items-center gap-3">
                                                <?php
                                                $placeholder = 'assets/images/placeholders/product-placeholder.png';
                                                $actualImg = $placeholder;
                                                
                                                if (!empty($sub['image_path'])) {
                                                    $actualImg = $sub['image_path'];
                                                } elseif (!empty($sub['product_image'])) {
                                                    $actualImg = $sub['product_image'];
                                                } elseif (!empty($sub['category_image'])) {
                                                    $actualImg = $sub['category_image'];
                                                }
                                                
                                                $imgSrc = BASE_URL . $actualImg;
                                                ?>
                                                <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                                    alt="<?php echo htmlspecialchars($sub['name']); ?>"
                                                    class="rounded-3 product-thumb"
                                                    style="width: 50px; height: 50px; object-fit: cover;"
                                                    onerror="this.src='<?php echo BASE_URL . 'assets/images/placeholders/product-placeholder.png'; ?>'">
                                                <div>
                                                    <div class="fw-bold text-dark products-product-name">
                                                        <?php echo htmlspecialchars($sub['name']); ?>
                                                    </div>
                                                    <div class="text-uppercase text-muted mt-1 fw-bold products-product-sku" style="font-size: 10px; letter-spacing: 0.5px;">
                                                        SKU: <?php echo htmlspecialchars($sub['sku'] ?? 'DWP-' . str_pad((string)$sub['id'], 3, '0', STR_PAD_LEFT)); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="border-0 py-3 d-none d-lg-table-cell td-desc" style="max-width: 250px;">
                                            <p class="mb-0 text-muted small text-truncate-2">
                                                <?php echo htmlspecialchars($sub['description'] ?? 'No description available.'); ?>
                                            </p>
                                        </td>
                                        <td class="border-0 py-3 text-center td-stock" data-label="Total Products">
                                            <a href="products.php?category=<?php echo urlencode($sub['name']); ?>" class="text-decoration-none fw-bold text-dark hover-primary" style="font-size: 14px;">
                                                <?php echo $sub['product_count'] ?? 0; ?> Items
                                            </a>
                                        </td>
                                        <td class="border-0 py-3 text-center td-status" data-label="Status">
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input class="form-check-input status-toggle-custom" type="checkbox" 
                                                    role="switch"
                                                    data-id="<?php echo $sub['id']; ?>" 
                                                    data-type="subcategory"
                                                    <?php echo (strtolower($sub['status']) === 'active') ? 'checked' : ''; ?>>
                                            </div>
                                        </td>
                                        <td class="border-0 py-3 text-center pe-4 td-actions">
                                                <div class="d-flex justify-content-center gap-2 td-actions-wrapper">
                                                    <?php
                                                    $subJson = htmlspecialchars(json_encode([
                                                        'id' => $sub['id'],
                                                        'name' => $sub['name'],
                                                        'category_id' => $sub['category_id'],
                                                        'image_path' => $sub['image_path'] ?? '',
                                                        'hero_image' => $sub['hero_image'] ?? '',
                                                        'regular_price' => $sub['regular_price'] ?? '',
                                                        'discount_price' => $sub['discount_price'] ?? '',
                                                        'tax_rate' => $sub['tax_rate'] ?? '5% (GST)',
                                                        'weight' => $sub['weight'] ?? '250g',
                                                        'short_description' => $sub['short_description'] ?? '',
                                                        'description' => $sub['description'] ?? '',
                                                        'highlights' => $sub['highlights'] ?? '[]',
                                                        'ingredients' => $sub['ingredients'] ?? '',
                                                        'benefits' => $sub['benefits'] ?? '',
                                                        'storage_instructions' => $sub['storage_instructions'] ?? '',
                                                        'product_count' => $sub['product_count'] ?? 0,
                                                        'product_image' => $sub['product_image'] ?? '',
                                                        'category_image' => $sub['category_image'] ?? '',
                                                        'status' => $sub['status']
                                                    ]), ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                    <button type="button" class="btn btn-link text-dark p-0 fs-5 text-decoration-none shadow-none" title="Edit" onclick='editSubcategory(<?php echo $subJson; ?>)'>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-link text-dark p-0 fs-5 text-decoration-none shadow-none" title="Preview" onclick='openPreviewMode(<?php echo $subJson; ?>, "subcategory")'>
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-link text-dark p-0 fs-5 text-decoration-none shadow-none" title="Delete" onclick='openDeleteModal(<?php echo $subJson; ?>, "subcategory")'>
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr id="emptyState">
                                    <td colspan="6" class="text-center py-5 italic text-muted border-0">
                                        <div class="empty-state-card mt-3">
                                            <div class="empty-state-icon"><i class="bi bi-box-seam"></i></div>
                                            <h5 class="fw-bold">No Subcategories Found</h5>
                                            <p class="text-muted">Start by adding your first subcategory<?php echo $currentCategory ? ' for ' . htmlspecialchars($currentCategory['name']) : ''; ?>.</p>
                                            <button class="btn btn-primary rounded-pill px-4 mt-2 products-empty-btn" onclick="openSubcategoryPanel()">
                                                Add New Subcategory
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <tr id="noResults" class="products-no-results-row" style="display: none;">
                                <td colspan="6">
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

                <div class="p-4 border-top border-light d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="small text-muted order-2 order-md-1">
                        Page <?php echo $page; ?> of <?php echo $totalPages; ?> &bull; <?php echo $startItem; ?>-<?php echo $endItem; ?> of <?php echo $totalItems; ?> subcategories
                    </div>
                    <nav class="order-1 order-md-2">
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page <= 1): ?>
                                <li class="page-item disabled"><a class="page-link border-0 text-muted" href="#"><i class="bi bi-chevron-left"></i> Back</a></li>
                            <?php else: ?>
                                <li class="page-item"><a class="page-link border-0 text-dark" href="<?php echo buildPageUrl($page - 1, $categoryId); ?>"><i class="bi bi-chevron-left"></i> Back</a></li>
                            <?php endif; ?>

                            <li class="page-item active"><a class="page-link border-0 rounded-3 mx-1 products-page-link-active" href="#"><?php echo $page; ?></a></li>

                            <?php if ($page >= $totalPages): ?>
                                <li class="page-item disabled"><span class="page-link border-0 text-muted">Next <i class="bi bi-chevron-right"></i></span></li>
                            <?php else: ?>
                                <li class="page-item"><a class="page-link border-0 text-dark fw-bold ms-2" href="<?php echo buildPageUrl($page + 1, $categoryId); ?>">Next <i class="bi bi-chevron-right"></i></a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/modals/subcategory-form.php'; ?>
<?php require_once 'includes/modals/subcategory-bulk-modal.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('subcategorySearch');
        const statusFilter = document.getElementById('statusFilter');
        const rows = document.querySelectorAll('.product-row');
        const noResultsRow = document.querySelector('#noResults');
        const panelOverlay = document.getElementById('subcatPanel');
        const body = document.body;

        // Filtering
        const dateFilter = document.getElementById('dateFilter');

        function filterTable() {
            if (!searchInput) return;
            const searchTerm = searchInput.value.toLowerCase();
            const selStatus = statusFilter ? statusFilter.value : 'all';
            const selDays   = dateFilter  ? dateFilter.value  : 'all';

            const nowTs = Math.floor(Date.now() / 1000);
            const cutoff = selDays !== 'all' ? nowTs - (parseInt(selDays) * 86400) : 0;

            let visibleCount = 0;

            rows.forEach(row => {
                const name      = row.getAttribute('data-name')    || '';
                const slug      = row.getAttribute('data-slug')    || '';
                const status    = row.getAttribute('data-status')  || '';
                const createdTs = parseInt(row.getAttribute('data-created') || '0');

                const matchesSearch = name.includes(searchTerm) || slug.includes(searchTerm);
                const matchesStatus = (selStatus === 'all' || status === selStatus);
                const matchesDate   = (selDays === 'all' || createdTs >= cutoff);

                if (matchesSearch && matchesStatus && matchesDate) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noResultsRow) {
                noResultsRow.style.display = visibleCount === 0 && rows.length > 0 ? '' : 'none';
            }
        }

        if (searchInput) searchInput.addEventListener('input', filterTable);
        if (statusFilter) statusFilter.addEventListener('change', filterTable);
        if (dateFilter)   dateFilter.addEventListener('change', filterTable);

        // Apply initial filter on load
        filterTable();

        // Map status toggle to hidden input and text (For Add/Edit Modal)
        const checkbox = document.getElementById('statusCheckbox');
        const hiddenStatus = document.getElementById('statusHidden');
        const statusText = document.getElementById('statusText');
        if (checkbox && hiddenStatus && statusText) {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    hiddenStatus.value = 'active';
                    statusText.textContent = 'Active';
                    statusText.className = 'subcat-status-text text-success';
                } else {
                    hiddenStatus.value = 'inactive';
                    statusText.textContent = 'Inactive';
                    statusText.className = 'subcat-status-text text-danger';
                }
            });
        }

        // Handle Select All Checkbox
        const selectAllBtn = document.getElementById('selectAllSubcats');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('tbody .products-row-checkbox');
                checkboxes.forEach(cb => {
                    const row = cb.closest('tr');
                    if (row && row.style.display !== 'none') {
                        cb.checked = this.checked;
                    }
                });
            });
        }

        // Handle Status Toggles dynamically (For Inline Table Toggles)
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

                    const response = await fetch('api/v1/subcategories.php', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        if (rowEl) {
                            rowEl.setAttribute('data-status', nextStatus);
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

        // Form Submit via API
        const form = document.getElementById('subcatForm');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const btn = document.getElementById('btnSaveCategory');
                const alert = document.getElementById('formAlert');
                const originalText = btn.innerHTML;
                
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                btn.disabled = true;
                alert.classList.add('d-none');
                alert.classList.remove('alert-success', 'alert-danger');

                try {
                    const formData = new FormData(form);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const response = await fetch('api/v1/subcategories.php', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert.classList.add('alert-success');
                        alert.innerHTML = `<i class="bi bi-check-circle me-2"></i> Subcategory Saved!`;
                        alert.classList.remove('d-none');
                        
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        throw new Error(result.message || 'Failed to save subcategory');
                    }
                } catch (error) {
                    alert.classList.add('alert-danger');
                    alert.innerHTML = `<i class="bi bi-exclamation-circle me-2"></i> ${error.message}`;
                    alert.classList.remove('d-none');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            });
        }

        window.openSubcategoryPanel = (isEdit = false) => {
            const panelOverlay = document.getElementById('subcatPanel');
            if (panelOverlay) {
                if (!isEdit && form) {
                    form.reset();
                    document.getElementById('subcatId').value = '';
                    document.getElementById('formAction').value = 'create';
                    document.getElementById('panelTitle').textContent = 'Add Subcategory';
                    document.getElementById('btnSaveCategory').textContent = 'Save Subcategory';
                    document.getElementById('thumbImagePreview').style.display = 'none';
                    document.getElementById('thumbImageText').style.display = 'block';
                }
                panelOverlay.removeAttribute('hidden');
                panelOverlay.setAttribute('aria-hidden', 'false');
                document.body.classList.add('subcat-panel-open');
            }
        };

        window.editSubcategory = (data) => {
            form.reset();
            document.getElementById('subcatId').value = data.id;
            document.getElementById('formAction').value = 'update';
            document.getElementById('panelTitle').textContent = 'Edit Subcategory';
            document.getElementById('btnSaveCategory').textContent = 'Update Subcategory';
            
            document.querySelector('[name="name"]').value = data.name || '';
            document.querySelector('[name="category_id"]').value = data.category_id || '';
            document.querySelector('[name="description"]').value = data.description || '';
            document.querySelector('[name="short_description"]').value = data.short_description || '';
            document.querySelector('[name="regular_price"]').value = data.regular_price || '';
            document.querySelector('[name="discount_price"]').value = data.discount_price || '';
            document.querySelector('[name="tax_rate"]').value = data.tax_rate || '5% (GST)';
            document.querySelector('[name="ingredients"]').value = data.ingredients || '';
            document.querySelector('[name="benefits"]').value = data.benefits || '';
            document.querySelector('[name="storage_instructions"]').value = data.storage_instructions || '';

            // Handle Weight Radio
            if (data.weight) {
                const weightRadio = document.querySelector(`input[name="weight"][value="${data.weight}"]`);
                if (weightRadio) weightRadio.checked = true;
            }

            // Handle Highlights
            const container = document.getElementById('highlightsContainer');
            container.innerHTML = '';
            if (data.highlights) {
                try {
                    const highlights = typeof data.highlights === 'string' ? JSON.parse(data.highlights) : data.highlights;
                    if (Array.isArray(highlights)) {
                        highlights.forEach(h => addHighlightLine(h));
                    }
                } catch (e) {
                    console.warn('Failed to parse highlights', e);
                }
            }
            
            const checkbox = document.getElementById('statusCheckbox');
            const hiddenStatus = document.getElementById('statusHidden');
            const statusText = document.getElementById('statusText');
            
            if (data.status === 'active') {
                checkbox.checked = true;
                hiddenStatus.value = 'active';
                statusText.textContent = 'Active';
                statusText.className = 'subcat-status-text text-success';
            } else {
                checkbox.checked = false;
                hiddenStatus.value = 'inactive';
                statusText.textContent = 'Inactive';
                statusText.className = 'subcat-status-text text-danger';
            }

            const preview = document.getElementById('thumbImagePreview');
            const text = document.getElementById('thumbImageText');
            if (data.image_path) {
                preview.src = (window.BASE_URL || '') + data.image_path;
                preview.style.display = 'block';
                text.style.display = 'none';
            } else {
                preview.style.display = 'none';
                text.style.display = 'block';
            }

            window.openSubcategoryPanel(true);
        };
    });

    window.addHighlightLine = function(val = '') {
        const container = document.getElementById('highlightsContainer');
        const div = document.createElement('div');
        div.className = 'subcat-highlight-item';
        div.innerHTML = `
            <input type="text" name="highlights[]" class="subcat-input" placeholder="Add highlight point" value="${val}">
            <button type="button" class="subcat-remove-btn" onclick="this.parentElement.remove()">
                <i class="bi bi-x-lg"></i>
            </button>
        `;
        container.appendChild(div);
        if (!val) div.querySelector('input').focus();
    };

    // Global Image Preview
    window.previewSubcatImage = function(input, previewId, textId, boxId) {
        const preview = document.getElementById(previewId);
        const textElement = document.getElementById(textId);
        const box = document.getElementById(boxId);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (textElement) textElement.style.display = 'none';
                if (box) box.classList.add('has-image');
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    window.closeSubcategoryPanel = () => {
        const panelOverlay = document.getElementById('subcatPanel');
        if (panelOverlay) {
            panelOverlay.setAttribute('aria-hidden', 'true');
            panelOverlay.setAttribute('hidden', 'hidden');
            document.body.classList.remove('subcat-panel-open');
            const alert = document.getElementById('formAlert');
            if(alert) alert.classList.add('d-none');
        }
    };
</script>

<?php 
require_once 'includes/modals/delete-confirm.php';
require_once 'includes/modals/product-preview.php';
require_once 'includes/footer.php'; 
?>
