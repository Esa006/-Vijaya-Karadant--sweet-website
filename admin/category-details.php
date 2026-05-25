<?php
/**
 * Sweets Website
 * =============================================================
 * File: category-details.php
 * Description: Premium high-fidelity Category Details View with Stats & Products
 * Author: Antigravity - Senior Backend & UI Architect
 * Version: 3.0.0
 * =============================================================
 */

$pageStyles = [
    'assets/css/admin/products.css',
    'assets/css/admin/pages/category-details.css'
];

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once REPOS_PATH . '/CategoryRepository.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: categories.php');
    exit;
}

$categoryRepo = new CategoryRepository();
$category = $categoryRepo->getWithStats($id);

if (!$category) {
    echo "<div class='main-content p-5 text-center'><h3>Category Not Found</h3><a href='categories.php' class='btn btn-primary'>Back to Categories</a></div>";
    require_once 'includes/footer.php';
    exit;
}

// Fetch subcategories
$subcategories = $categoryRepo->getSubcategories($id, 10, 0); // show top 10 subcategories
// Fetch recent products
$recentProducts = $categoryRepo->getRecentProducts($id, 10); // show up to 10 products

// Decode highlights
$highlights = !empty($category['highlights']) ? json_decode($category['highlights'], true) : [];
if (!is_array($highlights)) {
    $highlights = [];
}

// Initials for avatar
$initials = '';
$parts = explode(' ', $category['name']);
foreach ($parts as $p) {
    if (!empty($p)) $initials .= strtoupper($p[0]);
}
$initials = substr($initials, 0, 2);

function formatRupees($amount) {
    return '₹ ' . number_format((float)$amount, 2);
}
?>

<div class="main-content category-details-wrapper">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 px-4 py-4">
        <!-- Breadcrumb -->
        <div class="crumb-nav mb-2">
            <a href="categories.php">Categories</a>
            <i class="bi bi-chevron-right mx-1 small"></i>
            <span class="text-muted">Category Details</span>
        </div>

        <!-- Heading / Header Row -->
        <div class="heading-row pb-3 mb-4 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <?php if (!empty($category['image_path'])): ?>
                    <img src="<?php echo BASE_URL . $category['image_path']; ?>" 
                         alt="<?php echo htmlspecialchars($category['name']); ?>" 
                         class="category-avatar-large"
                         onerror="this.style.display='none'; document.getElementById('initialsAvatar').style.display='flex';">
                <?php endif; ?>
                <div id="initialsAvatar" class="category-avatar-large" style="display: <?php echo empty($category['image_path']) ? 'flex' : 'none'; ?>;">
                    <?php echo $initials; ?>
                </div>
                
                <div>
                    <h1 class="fw-bold mb-1"><?php echo htmlspecialchars($category['name']); ?></h1>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="text-muted small fw-bold">SLUG: <?php echo htmlspecialchars($category['slug']); ?></span>
                        <span class="text-muted small fw-bold">•</span>
                        <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="categoryStatusToggle" 
                                   data-id="<?php echo $category['id']; ?>"
                                   <?php echo ($category['status'] === 'active' || $category['status'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label small fw-bold" for="categoryStatusToggle" id="statusToggleLabel">
                                <?php echo ($category['status'] === 'active' || $category['status'] == 1) ? 'Active' : 'Inactive'; ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <a href="products.php?category=<?php echo urlencode($category['slug']); ?>" class="btn-action-outline">
                    <i class="bi bi-box-seam"></i> View Products
                </a>
                <a href="edit-category.php?id=<?php echo $category['id']; ?>" class="btn-action-accent">
                    <i class="bi bi-pencil"></i> Edit Category
                </a>
                <a href="categories.php" class="btn-action-outline">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row g-4 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card-details">
                    <div>
                        <div class="stat-details-label">Total Products</div>
                        <div class="stat-details-value"><?php echo $category['product_count']; ?></div>
                    </div>
                    <div class="stat-details-icon"><i class="bi bi-box"></i></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card-details">
                    <div>
                        <div class="stat-details-label">Total Stock</div>
                        <div class="stat-details-value"><?php echo number_format($category['total_stock']); ?></div>
                    </div>
                    <div class="stat-details-icon gold"><i class="bi bi-stack"></i></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card-details">
                    <div>
                        <div class="stat-details-label">Stock Value</div>
                        <div class="stat-details-value"><?php echo formatRupees($category['stock_value']); ?></div>
                    </div>
                    <div class="stat-details-icon"><i class="bi bi-currency-rupee"></i></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card-details">
                    <div>
                        <div class="stat-details-label">Total Revenue</div>
                        <div class="stat-details-value"><?php echo formatRupees($category['total_revenue']); ?></div>
                    </div>
                    <div class="stat-details-icon gold"><i class="bi bi-graph-up-arrow"></i></div>
                </div>
            </div>
        </div>

        <!-- Columns Grid -->
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Specifications Card -->
                <div class="panel-details mb-4">
                    <div class="panel-details-title">
                        <span><i class="bi bi-info-circle me-2"></i>Specifications</span>
                    </div>
                    <div class="panel-details-body p-0">
                        <table class="kv-list-details">
                            <tbody>
                                <tr>
                                    <td class="label-col">Category ID</td>
                                    <td class="value-col">#<?php echo $category['id']; ?></td>
                                </tr>
                                <tr>
                                    <td class="label-col">SKU Code</td>
                                    <td class="value-col"><?php echo htmlspecialchars($category['sku'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td class="label-col">Default Weight</td>
                                    <td class="value-col"><?php echo htmlspecialchars($category['weight'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td class="label-col">GST Tax Rate</td>
                                    <td class="value-col"><?php echo htmlspecialchars($category['tax_rate'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td class="label-col">Parent Category</td>
                                    <td class="value-col">
                                        <?php if (!empty($category['parent'])): ?>
                                            <a href="category-details.php?id=<?php echo $category['parent']['id']; ?>" class="text-decoration-none fw-bold text-accent" style="color: var(--accent);">
                                                <?php echo htmlspecialchars($category['parent']['name']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">None (Top Level Category)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-col">Created Date</td>
                                    <td class="value-col"><?php echo date('M d, Y \a\t h:i A', strtotime($category['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <td class="label-col">Last Updated</td>
                                    <td class="value-col"><?php echo date('M d, Y \a\t h:i A', strtotime($category['updated_at'])); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Descriptions Card -->
                <div class="panel-details mb-4">
                    <div class="panel-details-title">
                        <span><i class="bi bi-file-text me-2"></i>Descriptions</span>
                    </div>
                    <div class="panel-details-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing: 0.5px;">Short Description</label>
                            <div class="details-text-block">
                                <?php echo htmlspecialchars($category['short_description'] ?? 'No short description provided.'); ?>
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing: 0.5px;">Full Description</label>
                            <div class="details-text-block" style="min-height: 100px;">
                                <?php echo htmlspecialchars($category['description'] ?? 'No description provided.'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ingredients & storage instructions -->
                <div class="panel-details mb-4">
                    <div class="panel-details-title">
                        <span><i class="bi bi-bookmark-star me-2"></i>Ingredients & Storage Insights</span>
                    </div>
                    <div class="panel-details-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing: 0.5px;">Ingredients</label>
                            <div class="details-text-block">
                                <?php echo nl2br(htmlspecialchars($category['ingredients'] ?? 'Not specified.')); ?>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing: 0.5px;">Health Benefits</label>
                            <div class="details-text-block">
                                <?php echo nl2br(htmlspecialchars($category['benefits'] ?? 'Not specified.')); ?>
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing: 0.5px;">Storage Instructions</label>
                            <div class="details-text-block">
                                <?php echo nl2br(htmlspecialchars($category['storage_instructions'] ?? 'Not specified.')); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Media Cards -->
                <div class="panel-details mb-4">
                    <div class="panel-details-title">
                        <span><i class="bi bi-images me-2"></i>Category Media</span>
                    </div>
                    <div class="panel-details-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted d-block mb-2" style="letter-spacing: 0.5px;">Hero Banner</label>
                            <div class="category-hero-container">
                                <?php if (!empty($category['hero_image'])): ?>
                                    <img src="<?php echo BASE_URL . $category['hero_image']; ?>" class="category-hero-img" alt="Hero Banner">
                                    <div class="category-hero-overlay">
                                        <h5 class="m-0 font-weight-bold" style="font-size: 1rem;"><?php echo htmlspecialchars($category['name']); ?> Banner</h5>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted bg-light">
                                        <i class="bi bi-image fs-3 mb-1"></i>
                                        <span class="small">No Hero Banner Uploaded</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div>
                            <label class="form-label fw-bold small text-uppercase text-muted d-block mb-2" style="letter-spacing: 0.5px;">Thumbnail Image</label>
                            <div class="d-flex align-items-center justify-content-center border rounded-3 p-3 bg-light" style="min-height: 120px;">
                                <?php if (!empty($category['image_path'])): ?>
                                    <img src="<?php echo BASE_URL . $category['image_path']; ?>" 
                                         class="rounded" 
                                         style="max-height: 100px; max-width: 100%; object-fit: contain;" 
                                         alt="Thumbnail">
                                <?php else: ?>
                                    <div class="text-center text-muted">
                                        <i class="bi bi-image fs-4 d-block mb-1"></i>
                                        <span class="small">No Thumbnail Uploaded</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Highlights Card -->
                <div class="panel-details mb-4">
                    <div class="panel-details-title">
                        <span><i class="bi bi-stars me-2"></i>Product Highlights</span>
                    </div>
                    <div class="panel-details-body">
                        <?php if (!empty($highlights)): ?>
                            <ul class="highlights-list">
                                <?php foreach ($highlights as $hl): ?>
                                    <li><?php echo htmlspecialchars($hl); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted italic mb-0 small">No highlight points added.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Subcategories Card -->
                <div class="panel-details mb-4">
                    <div class="panel-details-title">
                        <span><i class="bi bi-diagram-3 me-2"></i>Subcategories</span>
                        <span class="tag-count"><?php echo $category['subcategory_count']; ?></span>
                    </div>
                    <div class="panel-details-body p-0">
                        <?php if ($category['subcategory_count'] > 0 && !empty($subcategories)): ?>
                            <table class="details-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th class="text-center">Products</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subcategories as $sub): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php
                                                    $subImg = !empty($sub['image_path']) ? $sub['image_path'] : 'assets/images/placeholders/product-placeholder.png';
                                                    ?>
                                                    <img src="<?php echo BASE_URL . $subImg; ?>" class="thumbnail-cell" style="width: 32px; height: 32px;" onerror="this.src='<?php echo BASE_URL; ?>assets/images/placeholders/product-placeholder.png'">
                                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($sub['name']); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center fw-bold"><?php echo $sub['product_count'] ?? 0; ?></td>
                                            <td class="text-end">
                                                <a href="category-details.php?id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-link text-accent p-0 shadow-none" style="color: var(--accent);"><i class="bi bi-eye"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if ($category['subcategory_count'] > count($subcategories)): ?>
                                <div class="p-3 text-center border-top">
                                    <a href="subcategories.php?category_id=<?php echo $category['id']; ?>" class="small fw-bold text-decoration-none" style="color: var(--accent);">View All Subcategories <i class="bi bi-arrow-right"></i></a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-folder2-open fs-3 d-block mb-1 text-muted opacity-50"></i>
                                <span class="small d-block">No subcategories found</span>
                                <a href="subcategories.php?category_id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3 mt-2 font-weight-bold" style="font-size: 0.75rem;">Create Subcategory</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Products Section -->
        <div class="row g-4 mt-2">
            <div class="col-lg-12">
                <div class="panel-details">
                    <div class="panel-details-title">
                        <span><i class="bi bi-collection me-2"></i>Recent Products in this Category</span>
                        <span class="tag-count"><?php echo count($recentProducts); ?> Displayed</span>
                    </div>
                    <div class="panel-details-body p-0">
                        <?php if (!empty($recentProducts)): ?>
                            <div class="table-responsive">
                                <table class="details-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Base Price</th>
                                            <th>Sale Price</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentProducts as $prod): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <?php
                                                        $prodImg = !empty($prod['image_path']) ? $prod['image_path'] : 'assets/images/placeholders/product-placeholder.png';
                                                        ?>
                                                        <img src="<?php echo BASE_URL . $prodImg; ?>" 
                                                             class="thumbnail-cell" 
                                                             alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                                             onerror="this.src='<?php echo BASE_URL; ?>assets/images/placeholders/product-placeholder.png'">
                                                        <div>
                                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($prod['name']); ?></div>
                                                            <div class="text-muted small">SKU: <?php echo htmlspecialchars(strtoupper($prod['slug'])); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>₹ <?php echo number_format($prod['base_price'], 2); ?></td>
                                                <td>
                                                    <?php if ($prod['sale_price'] > 0): ?>
                                                        <span class="text-success fw-bold">₹ <?php echo number_format($prod['sale_price'], 2); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted italic">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center fw-bold">
                                                    <?php if ($prod['stock_quantity'] > 0): ?>
                                                        <?php echo $prod['stock_quantity']; ?> Units
                                                    <?php else: ?>
                                                        <span class="text-danger fw-bold">Out of stock</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="status-pill <?php echo (strtolower($prod['status']) === 'active' || $prod['status'] == 1) ? 'active' : 'inactive'; ?>">
                                                        <?php echo (strtolower($prod['status']) === 'active' || $prod['status'] == 1) ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="edit-product.php?id=<?php echo $prod['id']; ?>" class="btn btn-sm btn-action-outline px-2 py-1 fs-7" title="Edit Product">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-box-seam fs-2 d-block mb-2 text-muted opacity-50"></i>
                                <span>No products found in this category.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div id="toastContainer" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Toast Notification helper
    function showToast(msg, type = 'success') {
        const container = document.getElementById('toastContainer');
        const el = document.createElement('div');
        const bg = type === 'error' ? '#dc2626' : '#16a34a';
        el.style.cssText = `background:${bg};color:#fff;padding:12px 18px;border-radius:10px;
                             box-shadow:0 4px 15px rgba(0,0,0,.15);display:flex;align-items:center;
                             gap:10px;font-size:.9rem;font-weight:600;min-width:280px; transition: opacity 0.4s;`;
        el.innerHTML = `<span>${msg}</span>`;
        container.appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 3500);
    }

    // Status Toggle Switch
    const statusToggle = document.getElementById('categoryStatusToggle');
    const statusLabel = document.getElementById('statusToggleLabel');
    
    if (statusToggle && statusLabel) {
        statusToggle.addEventListener('change', async function() {
            const id = this.getAttribute('data-id');
            const nextStatus = this.checked ? 'active' : 'inactive';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            this.disabled = true;
            statusLabel.textContent = 'Updating...';
            
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
                    showToast('Category status updated successfully!');
                    statusLabel.textContent = nextStatus === 'active' ? 'Active' : 'Inactive';
                } else {
                    throw new Error(result.message || 'Failed to update status');
                }
            } catch (error) {
                this.checked = !this.checked; // Revert visually
                statusLabel.textContent = this.checked ? 'Active' : 'Inactive';
                showToast(error.message || 'Error updating status', 'error');
            } finally {
                this.disabled = false;
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
