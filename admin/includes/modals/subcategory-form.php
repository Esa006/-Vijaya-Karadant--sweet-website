<?php
/**
 * Sweets Website
 * =============================================================
 * File: subcategory-form.php
 * Description: Subcategory Add/Edit Slide-over Panel (Mockup Matched)
 * =============================================================
 */
?>
<div class="subcat-panel-overlay" id="subcatPanel" hidden aria-hidden="true" tabindex="-1">
    <div class="subcat-panel-wrapper" role="dialog" aria-modal="true" aria-labelledby="subcatPanelTitle">
        <div class="subcat-panel-scroll" id="subcatPanelScroll">
            <div class="subcat-panel-header">
                <h2 class="subcat-panel-title" id="panelTitle">Add Subcategory</h2>
                <button type="button" class="subcat-btn-close" aria-label="Close panel" onclick="closeSubcategoryPanel()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <form id="subcatForm" class="subcat-panel-form">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="subcatId" value="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                
                <!-- Subcategory Name -->
                <div class="mb-4">
                    <label class="subcat-label" for="subcatNameInput">Subcategory Name</label>
                    <input type="text" class="subcat-input" id="subcatNameInput" name="name" placeholder="Enter subcategory name" required>
                </div>
                
                <!-- Category Dropdown -->
                <div class="mb-4">
                    <label class="subcat-label" for="parentCategorySelect">Category</label>
                    <select class="subcat-select" id="parentCategorySelect" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($parentCategoriesForSelect as $parent): ?>
                            <option value="<?php echo (int) $parent['id']; ?>">
                                <?php echo htmlspecialchars($parent['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Image Upload Section -->
                <div class="subcat-upload-section">
                    <div>
                        <label class="subcat-label">Image (Only 1920x1080)</label>
                        <div class="subcat-upload-box" id="mainImageUploadBox" onclick="document.getElementById('heroImageInput').click()">
                            <i class="bi bi-image"></i>
                            <p id="heroImageText">Upload image</p>
                            <span class="subcat-upload-btn-mock">Add Image</span>
                            <input type="file" id="heroImageInput" name="hero_image" hidden accept="image/*" onchange="previewSubcatImage(this, 'heroImagePreview', 'heroImageText', 'mainImageUploadBox')">
                            <img id="heroImagePreview" class="subcat-image-preview" alt="Hero Image Preview">
                        </div>
                    </div>
                    <div>
                        <label class="subcat-label">Image thumbnails</label>
                        <div class="subcat-upload-box" id="thumbImageUploadBox" onclick="document.getElementById('thumbImageInput').click()">
                            <i class="bi bi-images"></i>
                            <p id="thumbImageText">Upload image</p>
                            <span class="subcat-upload-btn-mock">Add Image</span>
                            <input type="file" id="thumbImageInput" name="image" hidden accept="image/*" onchange="previewSubcatImage(this, 'thumbImagePreview', 'thumbImageText', 'thumbImageUploadBox')">
                            <img id="thumbImagePreview" class="subcat-image-preview" alt="Thumbnail Preview">
                        </div>
                    </div>
                </div>
                
                <!-- Price Section -->
                <div class="subcat-price-row mt-4">
                    <div class="subcat-price-col">
                        <label class="subcat-label" for="regularPrice">Regular Price</label>
                        <div class="subcat-input-group">
                            <span class="subcat-input-group-text">₹</span>
                            <input type="number" step="0.01" name="regular_price" id="regularPrice" class="subcat-input" placeholder="780">
                        </div>
                    </div>
                    <div class="subcat-price-col">
                        <label class="subcat-label" for="discountPrice">Discount Price</label>
                        <div class="subcat-input-group">
                            <span class="subcat-input-group-text">₹</span>
                            <input type="number" step="0.01" name="discount_price" id="discountPrice" class="subcat-input" placeholder="360">
                        </div>
                    </div>
                    <div class="subcat-price-col">
                        <label class="subcat-label" for="taxRate">Tax Rate</label>
                        <input type="text" name="tax_rate" id="taxRate" class="subcat-input" placeholder="5% (GST)" value="5% (GST)">
                    </div>
                </div>
                
                <!-- Weight Selection -->
                <div class="mb-4">
                    <label class="subcat-label">Select Weight</label>
                    <div class="subcat-weight-options">
                        <div class="subcat-weight-option">
                            <input type="radio" id="weight250" name="weight" value="250g" checked>
                            <label for="weight250"><span>250g</span></label>
                        </div>
                        <div class="subcat-weight-option">
                            <input type="radio" id="weight500" name="weight" value="500g">
                            <label for="weight500"><span>500g</span></label>
                        </div>
                        <div class="subcat-weight-option">
                            <input type="radio" id="weight1kg" name="weight" value="1Kg">
                            <label for="weight1kg"><span>1Kg</span></label>
                        </div>
                    </div>
                </div>
                
                <!-- Short Description -->
                <div class="mb-4">
                    <label class="subcat-label" for="shortDescription">Short Description</label>
                    <input type="text" class="subcat-input" id="shortDescription" name="short_description" placeholder="Add Description">
                </div>
                
                <!-- Full Description -->
                <div class="mb-4">
                    <label class="subcat-label" for="fullDescription">Full Description</label>
                    <textarea class="subcat-textarea" id="fullDescription" name="description" placeholder="Add Description" style="min-height: 120px;"></textarea>
                </div>
                
                <!-- Status Toggle -->
                <div class="mb-4">
                    <label class="subcat-label d-flex align-items-center gap-2">
                        Status
                        <label class="subcat-toggle-switch">
                            <input type="checkbox" id="statusCheckbox" checked>
                            <span class="subcat-slider"></span>
                        </label>
                        <span id="statusText" class="subcat-status-text text-success">Active</span>
                        <input type="hidden" name="status" id="statusHidden" value="active">
                    </label>
                </div>
                
                <!-- Add Highlight Section -->
                <div class="mb-4">
                    <label class="subcat-label">Add highlight</label>
                    <div id="highlightsContainer">
                        <!-- Items will be added here via JS -->
                    </div>
                    <button type="button" class="subcat-add-highlight-btn" onclick="addHighlightLine()">
                        <i class="bi bi-plus-lg"></i> Add highlight point
                    </button>
                </div>
                
                <!-- Ingredients -->
                <div class="mb-4">
                    <label class="subcat-label" for="ingredients">Ingredients</label>
                    <input type="text" class="subcat-input" id="ingredients" name="ingredients" placeholder="Add Description">
                </div>
                
                <!-- Benefits -->
                <div class="mb-4">
                    <label class="subcat-label" for="benefits">Benefits</label>
                    <input type="text" class="subcat-input" id="benefits" name="benefits" placeholder="Add Benefits">
                </div>
                
                <!-- Storage Instructions -->
                <div class="mb-4">
                    <label class="subcat-label" for="storageInstructions">Storage Instructions</label>
                    <input type="text" class="subcat-input" id="storageInstructions" name="storage_instructions" placeholder="Add Storage Instructions">
                </div>
                
                <!-- Alert Box for feedback -->
                <div id="formAlert" class="alert d-none" role="alert"></div>

                <!-- Button Group -->
                <div class="subcat-actions">
                    <button type="button" class="subcat-btn subcat-btn-cancel" onclick="closeSubcategoryPanel()">Cancel</button>
                    <button type="submit" id="btnSaveCategory" class="subcat-btn subcat-btn-save">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.subcat-add-highlight-btn {
    background: none;
    border: none;
    color: var(--text-orange);
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 0;
}
.subcat-add-highlight-btn:hover {
    color: var(--primary-orange);
}
.subcat-upload-box.has-image {
    border-style: solid;
    background-color: #fff;
}
</style>
