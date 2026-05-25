<?php
/**
 * Sweets Website
 * =============================================================
 * File: edit-category.php
 * Description: Edit page for Categories and Subcategories (V3 Schema UI)
 * =============================================================
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once REPOS_PATH . '/CategoryRepository.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: categories.php");
    exit;
}

$categoryRepo = new CategoryRepository();
$category = $categoryRepo->getById($id);

if (!$category) {
    header("Location: categories.php");
    exit;
}

$parentCategoriesForSelect = $categoryRepo->getRootCategories();
$highlights = !empty($category['highlights']) ? json_decode($category['highlights'], true) : [];
if (!is_array($highlights)) {
    $highlights = [];
}

$pageStyles = [
    'assets/css/admin/products.css', 
    'assets/css/admin/pages/subcategory-add-panel.css'
];

require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
?>

<div class="main-content products-page" style="background: #FAF5EE; min-height: 100vh;">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 products-content-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 mb-3 border-bottom px-4 mx-n4 bg-white">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0 small fw-bold">
                        <li class="breadcrumb-item"><a href="categories.php" class="text-decoration-none" style="color: #8c3333;">Categories</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Edit</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-0 products-page-title">
                    Edit: <?php echo htmlspecialchars($category['name']); ?>
                </h2>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo empty($category['parent_id']) ? 'categories.php' : 'subcategories.php'; ?>" class="btn rounded-2 d-flex align-items-center btn-light border shadow-none">
                    <i class="bi bi-arrow-left me-2"></i> Back
                </a>
            </div>
        </div>

        <div class="px-2 pb-5">
            <div class="mt-4">
                <div class="subcat-panel-wrapper shadow-none border" style="max-width: 700px; margin: 0 auto;">
                    <div class="subcat-panel-scroll p-4 p-md-5">
                        <form id="editCategoryForm" class="subcat-panel-form">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="subcat-label">Subcategory Name</label>
                                    <input type="text" name="name" class="subcat-input" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="subcat-label">SKU</label>
                                    <input type="text" name="sku" class="subcat-input" value="<?php echo htmlspecialchars($category['sku'] ?? ''); ?>" placeholder="e.g. DWP-001">
                                </div>
                            </div>

                            <div class="subcat-field-group">
                                <label class="subcat-label" for="catSlugInput">Slug (URL)</label>
                                <input type="text" id="catSlugInput" name="slug" class="subcat-input" value="<?php echo htmlspecialchars($category['slug']); ?>">
                                <div class="form-text mt-1 text-muted small">Leave empty to keep current. Changing this may break existing links.</div>
                            </div>

                            <!-- Mapped to parent_id but styled like the mockup "Subcategory Name" -->
                            <div class="subcat-field-group">
                                <label class="subcat-label" for="parentCategorySelect">Subcategory Name</label>
                                <select id="parentCategorySelect" name="parent_id" class="subcat-select">
                                    <option value="">None (Top Level Category)</option>
                                    <?php foreach ($parentCategoriesForSelect as $parent): ?>
                                        <?php if ($parent['id'] !== $category['id']): // Prevent setting self as parent ?>
                                            <option value="<?php echo (int) $parent['id']; ?>" <?php echo ($category['parent_id'] === $parent['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($parent['name']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                             <!-- Dual Image Block -->
                             <div class="subcat-upload-row mt-4">
                                <div class="subcat-upload-col">
                                    <label class="subcat-label">Image (Only 1920x1080)</label>
                                    <?php if (!empty($category['hero_image'])): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo BASE_URL . $category['hero_image']; ?>" class="rounded bg-white border" style="height: 60px; max-width: 100%; object-fit: contain;">
                                        </div>
                                    <?php endif; ?>
                                    <div class="subcat-upload-group">
                                        <input type="text" id="heroImageNameText" class="subcat-upload-input" placeholder="Change image" readonly>
                                        <input type="file" id="heroImageInput" name="hero_image" class="subcat-file-input" accept="image/*" onchange="document.getElementById('heroImageNameText').value = this.files[0] ? this.files[0].name : '';">
                                        <button type="button" class="subcat-upload-btn" onclick="document.getElementById('heroImageInput').click()">Browse</button>
                                    </div>
                                </div>
                                <div class="subcat-upload-col">
                                    <label class="subcat-label">Image thumbnails</label>
                                    <?php if (!empty($category['image_path'])): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo BASE_URL . $category['image_path']; ?>" class="rounded bg-white border" style="height: 60px; max-width: 100%; object-fit: contain;">
                                        </div>
                                    <?php endif; ?>
                                    <div class="subcat-upload-group">
                                        <input type="text" id="thumbImageNameText" class="subcat-upload-input" placeholder="Change thumbnail" readonly>
                                        <input type="file" id="thumbImageInput" name="image" class="subcat-file-input" accept="image/*" onchange="document.getElementById('thumbImageNameText').value = this.files[0] ? this.files[0].name : '';">
                                        <button type="button" class="subcat-upload-btn" onclick="document.getElementById('thumbImageInput').click()">Browse</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing Row -->
                            <div class="subcat-price-row mt-4">
                                <div class="subcat-price-col">
                                    <label class="subcat-label">Regular Price</label>
                                    <div class="subcat-price-group">
                                        <span class="subcat-currency">₹</span>
                                        <input type="number" step="0.01" name="regular_price" class="subcat-price-input" value="<?php echo htmlspecialchars($category['regular_price'] ?? ''); ?>" placeholder="780">
                                    </div>
                                </div>
                                <div class="subcat-price-col">
                                    <label class="subcat-label">Discount Price</label>
                                    <div class="subcat-price-group">
                                        <span class="subcat-currency">₹</span>
                                        <input type="number" step="0.01" name="discount_price" class="subcat-price-input" value="<?php echo htmlspecialchars($category['discount_price'] ?? ''); ?>" placeholder="360">
                                    </div>
                                </div>
                                <div class="subcat-price-col">
                                    <label class="subcat-label">Tax Rate</label>
                                    <div class="subcat-price-group">
                                        <span class="subcat-currency">%</span>
                                        <input type="text" name="tax_rate" class="subcat-price-input" value="<?php echo htmlspecialchars($category['tax_rate'] ?? ''); ?>" placeholder="5% (GST)">
                                    </div>
                                </div>
                            </div>

                            <!-- Weight Options -->
                            <div class="subcat-field-group mt-4">
                                <label class="subcat-label">Select Weight</label>
                                <div class="subcat-weight-row">
                                    <label class="subcat-weight-option">
                                        <input type="radio" name="weight" value="250g" <?php echo ($category['weight'] === '250g') ? 'checked' : ''; ?>>
                                        <span>250g</span>
                                    </label>
                                    <label class="subcat-weight-option ms-3">
                                        <input type="radio" name="weight" value="500g" <?php echo ($category['weight'] === '500g') ? 'checked' : ''; ?>>
                                        <span>500g</span>
                                    </label>
                                    <label class="subcat-weight-option ms-3">
                                        <input type="radio" name="weight" value="1Kg" <?php echo ($category['weight'] === '1Kg') ? 'checked' : ''; ?>>
                                        <span>1Kg</span>
                                    </label>
                                </div>
                            </div>

                            <div class="subcat-field-group mt-4">
                                <label class="subcat-label">Short Description</label>
                                <input type="text" name="short_description" class="subcat-input" value="<?php echo htmlspecialchars($category['short_description'] ?? ''); ?>" placeholder="Add Description">
                            </div>

                            <div class="subcat-field-group text-area-large mt-3">
                                <label class="subcat-label">Full Description</label>
                                <textarea name="description" class="subcat-textarea" placeholder="Add Description"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="subcat-field-group w-50 mt-4">
                                <div class="subcat-toggle-row">
                                    <label class="subcat-label mb-0 fw-bold">Status</label>
                                    <label class="subcat-toggle-switch">
                                        <input type="checkbox" id="statusCheckbox" <?php echo ($category['status'] === 'active') ? 'checked' : ''; ?>>
                                        <span class="subcat-toggle-slider"></span>
                                    </label>
                                    <input type="hidden" name="status" id="statusHidden" value="<?php echo htmlspecialchars($category['status']); ?>">
                                </div>
                            </div>

                            <!-- Dynamic Highlights section -->
                            <div class="subcat-field-group mt-5">
                                <label class="subcat-label text-warning" style="color: #c4762c !important;">Add highlight</label>
                                <div id="highlightsContainer">
                                    <?php if (!empty($highlights)): ?>
                                        <?php foreach ($highlights as $hl): ?>
                                            <div class="subcat-highlight-item" style="position: relative;">
                                                <input type="text" name="highlights[]" class="subcat-input" value="<?php echo htmlspecialchars($hl); ?>">
                                                <button type="button" class="subcat-highlight-remove" aria-label="Remove highlight" onclick="this.parentElement.remove()"><i class="bi bi-x-circle-fill" style="color: #aa3333; font-size: 1.2rem; background: white; border-radius: 50%; opacity: 0.8;"></i></button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <!-- Show one blank entry if empty -->
                                        <div class="subcat-highlight-item" style="position: relative;">
                                            <input type="text" name="highlights[]" class="subcat-input" placeholder="E.g., 100% Pure Premium Cashews">
                                            <button type="button" class="subcat-highlight-remove" aria-label="Remove highlight" onclick="this.parentElement.remove()"><i class="bi bi-x-circle-fill" style="color: #aa3333; font-size: 1.2rem; background: white; border-radius: 50%; opacity: 0.8;"></i></button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="subcat-add-highlight mt-2" onclick="addHighlightLine()">
                                    <span>+</span> Add highlight point
                                </button>
                            </div>

                            <div class="subcat-field-group mt-4">
                                <label class="subcat-label">Ingredients</label>
                                <textarea name="ingredients" class="subcat-textarea" placeholder="Add Description" style="min-height: 80px;"><?php echo htmlspecialchars($category['ingredients'] ?? ''); ?></textarea>
                            </div>

                            <div class="subcat-field-group mt-4">
                                <label class="subcat-label">Benefits</label>
                                <textarea name="benefits" class="subcat-textarea" placeholder="Add Benefits" style="min-height: 80px;"><?php echo htmlspecialchars($category['benefits'] ?? ''); ?></textarea>
                            </div>

                            <div class="subcat-field-group mt-4">
                                <label class="subcat-label">Storage Instructions</label>
                                <textarea name="storage_instructions" class="subcat-textarea" placeholder="Add Storage Instructions" style="min-height: 80px;"><?php echo htmlspecialchars($category['storage_instructions'] ?? ''); ?></textarea>
                            </div>

                            <!-- Alert Box for feedback -->
                            <div id="formAlert" class="alert d-none mt-4" role="alert"></div>

                            <div class="subcat-actions mt-5 pt-4 border-top">
                                <a href="<?php echo empty($category['parent_id']) ? 'categories.php' : 'subcategories.php'; ?>" class="subcat-btn subcat-btn-cancel text-decoration-none">Cancel</a>
                                <button type="submit" id="btnUpdateCategory" class="subcat-btn subcat-btn-save">Save Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Map status toggle to hidden input
        const checkbox = document.getElementById('statusCheckbox');
        const hiddenStatus = document.getElementById('statusHidden');
        if (checkbox && hiddenStatus) {
            checkbox.addEventListener('change', function() {
                hiddenStatus.value = this.checked ? 'active' : 'inactive';
            });
        }

        // Add highlight
        window.addHighlightLine = function() {
            const container = document.getElementById('highlightsContainer');
            const div = document.createElement('div');
            div.className = 'subcat-highlight-item';
            div.style.position = 'relative';
            div.innerHTML = `
                <input type="text" name="highlights[]" class="subcat-input" placeholder="New highlight point">
                <button type="button" class="subcat-highlight-remove" aria-label="Remove highlight" onclick="this.parentElement.remove()">
                    <i class="bi bi-x-circle-fill" style="color: #aa3333; font-size: 1.2rem; background: white; border-radius: 50%; opacity: 0.8;"></i>
                </button>
            `;
            container.appendChild(div);
            div.querySelector('input').focus();
        };

        // Form Submit via API
        const form = document.getElementById('editCategoryForm');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const btn = document.getElementById('btnUpdateCategory');
                const alert = document.getElementById('formAlert');
                const originalText = btn.innerHTML;
                
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                btn.disabled = true;
                alert.classList.add('d-none');
                alert.classList.remove('alert-success', 'alert-danger');

                try {
                    const formData = new FormData(form);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const response = await fetch('api/v1/categories.php', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert.classList.add('alert-success');
                        alert.innerHTML = `<i class="bi bi-check-circle me-2"></i> Product Saved Successfully!`;
                        alert.classList.remove('d-none');
                        
                        // Wait briefly so user sees message
                        setTimeout(() => {
                           window.location.href = "subcategories.php";
                        }, 1200);
                    } else {
                        throw new Error(result.message || 'Failed to update');
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
    });
</script>

<?php require_once 'includes/footer.php'; ?>
