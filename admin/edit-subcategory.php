<?php
/**
 * Sweets Website
 * =============================================================
 * File: edit-subcategory.php
 * Description: Edit page for Subcategories (3-Tier Schema UI)
 * =============================================================
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once SERVICES_PATH . '/SubcategoryService.php';
require_once REPOS_PATH . '/CategoryRepository.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: subcategories.php");
    exit;
}

$subcatService = new SubcategoryService();
$categoryRepo = new CategoryRepository();

$subcategory = $subcatService->getSubcategoryById($id);

if (!$subcategory) {
    header("Location: subcategories.php");
    exit;
}

$rootCategories = $categoryRepo->getRootCategories();

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
                        <li class="breadcrumb-item"><a href="subcategories.php" class="text-decoration-none" style="color: #8c3333;">Subcategories</a></li>
                        <li class="breadcrumb-item active text-muted" aria-current="page">Edit Subcategory</li>
                    </ol>
                </nav>
                <h2 class="fw-bold mb-0 products-page-title">
                    Edit: <?php echo htmlspecialchars($subcategory['name']); ?>
                </h2>
            </div>
            <div class="d-flex gap-2">
                <a href="subcategories.php" class="btn rounded-2 d-flex align-items-center btn-light border shadow-none">
                    <i class="bi bi-arrow-left me-2"></i> Back
                </a>
            </div>
        </div>

        <div class="px-2 pb-5">
            <div class="mt-4">
                <div class="subcat-panel-wrapper shadow-none border" style="max-width: 700px; margin: 0 auto;">
                    <div class="subcat-panel-scroll p-4 p-md-5">
                        <form id="editSubcategoryForm" class="subcat-panel-form">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                            <input type="hidden" name="id" value="<?php echo $subcategory['id']; ?>">
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="subcat-label">Subcategory Name</label>
                                    <input type="text" name="name" class="subcat-input" value="<?php echo htmlspecialchars($subcategory['name']); ?>" required>
                                </div>
                            </div>

                            <div class="subcat-field-group">
                                <label class="subcat-label" for="parentCategorySelect">Parent Category</label>
                                <select id="parentCategorySelect" name="category_id" class="subcat-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($rootCategories as $parent): ?>
                                        <option value="<?php echo (int) $parent['id']; ?>" <?php echo ($subcategory['category_id'] == $parent['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($parent['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Image Upload Section -->
                            <div class="subcat-upload-section mb-4">
                                <div>
                                    <label class="subcat-label">Hero Image (1920x1080)</label>
                                    <div class="subcat-upload-box <?php echo !empty($subcategory['hero_image']) ? 'has-image' : ''; ?>" id="mainImageUploadBox" onclick="document.getElementById('heroImageInput').click()">
                                        <i class="bi bi-image" style="<?php echo !empty($subcategory['hero_image']) ? 'display:none' : ''; ?>"></i>
                                        <p id="heroImageText" style="<?php echo !empty($subcategory['hero_image']) ? 'display:none' : ''; ?>">Upload image</p>
                                        <input type="file" id="heroImageInput" name="hero_image" hidden accept="image/*" onchange="previewSubcatImage(this, 'heroImagePreview', 'heroImageText', 'mainImageUploadBox')">
                                        <img id="heroImagePreview" class="subcat-image-preview" src="<?php echo !empty($subcategory['hero_image']) ? BASE_URL . $subcategory['hero_image'] : ''; ?>" style="<?php echo !empty($subcategory['hero_image']) ? 'display:block' : 'display:none'; ?>">
                                    </div>
                                </div>
                                <div>
                                    <label class="subcat-label">Thumbnail Image</label>
                                    <div class="subcat-upload-box <?php echo !empty($subcategory['image_path']) ? 'has-image' : ''; ?>" id="thumbImageUploadBox" onclick="document.getElementById('thumbImageInput').click()">
                                        <i class="bi bi-images" style="<?php echo !empty($subcategory['image_path']) ? 'display:none' : ''; ?>"></i>
                                        <p id="thumbImageText" style="<?php echo !empty($subcategory['image_path']) ? 'display:none' : ''; ?>">Upload image</p>
                                        <input type="file" id="thumbImageInput" name="image" hidden accept="image/*" onchange="previewSubcatImage(this, 'thumbImagePreview', 'thumbImageText', 'thumbImageUploadBox')">
                                        <img id="thumbImagePreview" class="subcat-image-preview" src="<?php echo !empty($subcategory['image_path']) ? BASE_URL . $subcategory['image_path'] : ''; ?>" style="<?php echo !empty($subcategory['image_path']) ? 'display:block' : 'display:none'; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Price Section -->
                            <div class="subcat-price-row mt-4">
                                <div class="subcat-price-col">
                                    <label class="subcat-label">Regular Price</label>
                                    <div class="subcat-input-group">
                                        <span class="subcat-input-group-text">₹</span>
                                        <input type="number" step="0.01" name="regular_price" class="subcat-input" value="<?php echo htmlspecialchars($subcategory['regular_price'] ?? ''); ?>" placeholder="780">
                                    </div>
                                </div>
                                <div class="subcat-price-col">
                                    <label class="subcat-label">Discount Price</label>
                                    <div class="subcat-input-group">
                                        <span class="subcat-input-group-text">₹</span>
                                        <input type="number" step="0.01" name="discount_price" class="subcat-input" value="<?php echo htmlspecialchars($subcategory['discount_price'] ?? ''); ?>" placeholder="360">
                                    </div>
                                </div>
                                <div class="subcat-price-col">
                                    <label class="subcat-label">Tax Rate</label>
                                    <input type="text" name="tax_rate" class="subcat-input" value="<?php echo htmlspecialchars($subcategory['tax_rate'] ?? '5% (GST)'); ?>">
                                </div>
                            </div>

                            <!-- Weight Selection -->
                            <div class="mb-4 mt-4">
                                <label class="subcat-label">Select Weight</label>
                                <div class="subcat-weight-options">
                                    <?php $weight = $subcategory['weight'] ?? '250g'; ?>
                                    <div class="subcat-weight-option">
                                        <input type="radio" id="weight250" name="weight" value="250g" <?php echo $weight === '250g' ? 'checked' : ''; ?>>
                                        <label for="weight250"><span>250g</span></label>
                                    </div>
                                    <div class="subcat-weight-option">
                                        <input type="radio" id="weight500" name="weight" value="500g" <?php echo $weight === '500g' ? 'checked' : ''; ?>>
                                        <label for="weight500"><span>500g</span></label>
                                    </div>
                                    <div class="subcat-weight-option">
                                        <input type="radio" id="weight1kg" name="weight" value="1Kg" <?php echo $weight === '1Kg' ? 'checked' : ''; ?>>
                                        <label for="weight1kg"><span>1Kg</span></label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="subcat-label">Short Description</label>
                                <input type="text" name="short_description" class="subcat-input" value="<?php echo htmlspecialchars($subcategory['short_description'] ?? ''); ?>" placeholder="Add Short Description">
                            </div>

                            <div class="subcat-field-group text-area-large">
                                <label class="subcat-label">Full Description</label>
                                <textarea name="description" class="subcat-textarea" placeholder="Add Description"><?php echo htmlspecialchars($subcategory['description'] ?? ''); ?></textarea>
                            </div>

                            <!-- Status Toggle -->
                            <div class="mb-4 mt-4">
                                <label class="subcat-label d-flex align-items-center gap-2">
                                    Status
                                    <label class="subcat-toggle-switch">
                                        <input type="checkbox" id="statusCheckbox" <?php echo ($subcategory['status'] === 'active') ? 'checked' : ''; ?>>
                                        <span class="subcat-slider"></span>
                                    </label>
                                    <span id="statusText" class="subcat-status-text <?php echo ($subcategory['status'] === 'active') ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo ucfirst($subcategory['status']); ?>
                                    </span>
                                    <input type="hidden" name="status" id="statusHidden" value="<?php echo htmlspecialchars($subcategory['status']); ?>">
                                </label>
                            </div>

                            <!-- Add Highlight Section -->
                            <div class="mb-4">
                                <label class="subcat-label">Add highlight</label>
                                <div id="highlightsContainer">
                                    <?php 
                                    $highlights = [];
                                    if (!empty($subcategory['highlights'])) {
                                        $highlights = is_string($subcategory['highlights']) ? json_decode($subcategory['highlights'], true) : $subcategory['highlights'];
                                    }
                                    if (is_array($highlights)):
                                        foreach ($highlights as $h): ?>
                                            <div class="subcat-highlight-item">
                                                <input type="text" name="highlights[]" class="subcat-input" value="<?php echo htmlspecialchars($h); ?>">
                                                <button type="button" class="subcat-remove-btn" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
                                            </div>
                                        <?php endforeach;
                                    endif; ?>
                                </div>
                                <button type="button" class="subcat-add-highlight-btn btn btn-link p-0 text-decoration-none fw-bold" onclick="addHighlightLine()" style="color: #d97706;">
                                    <i class="bi bi-plus-lg"></i> Add highlight point
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="subcat-label">Ingredients</label>
                                    <input type="text" name="ingredients" class="subcat-input" value="<?php echo htmlspecialchars($subcategory['ingredients'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="subcat-label">Benefits</label>
                                    <input type="text" name="benefits" class="subcat-input" value="<?php echo htmlspecialchars($subcategory['benefits'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="subcat-label">Storage Instructions</label>
                                    <input type="text" name="storage_instructions" class="subcat-input" value="<?php echo htmlspecialchars($subcategory['storage_instructions'] ?? ''); ?>">
                                </div>
                            </div>

                            <div id="formAlert" class="alert d-none mt-4" role="alert"></div>

                            <div class="subcat-actions mt-5 pt-4 border-top">
                                <a href="subcategories.php" class="subcat-btn subcat-btn-cancel text-decoration-none">Cancel</a>
                                <button type="submit" id="btnUpdateSubcategory" class="subcat-btn subcat-btn-save">Update Subcategory</button>
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
        const checkbox = document.getElementById('statusCheckbox');
        const hiddenStatus = document.getElementById('statusHidden');
        const statusText = document.getElementById('statusText');
        
        if (checkbox && hiddenStatus) {
            checkbox.addEventListener('change', function() {
                const active = this.checked;
                hiddenStatus.value = active ? 'active' : 'inactive';
                if (statusText) {
                    statusText.textContent = active ? 'Active' : 'Inactive';
                    statusText.className = 'subcat-status-text ' + (active ? 'text-success' : 'text-danger');
                }
            });
        }

        window.addHighlightLine = function(val = '') {
            const container = document.getElementById('highlightsContainer');
            const div = document.createElement('div');
            div.className = 'subcat-highlight-item d-flex gap-2 mb-2';
            div.innerHTML = `
                <input type="text" name="highlights[]" class="subcat-input" placeholder="Add highlight point" value="${val}">
                <button type="button" class="subcat-remove-btn btn btn-outline-danger" onclick="this.parentElement.remove()" style="width: 42px;">
                    <i class="bi bi-x-lg"></i>
                </button>
            `;
            container.appendChild(div);
            if (!val) div.querySelector('input').focus();
        };

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

        const form = document.getElementById('editSubcategoryForm');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const btn = document.getElementById('btnUpdateSubcategory');
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
                        alert.innerHTML = `<i class="bi bi-check-circle me-2"></i> Subcategory Updated Successfully!`;
                        alert.classList.remove('d-none');
                        
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
