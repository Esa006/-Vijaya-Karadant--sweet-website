<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/includes/modals/premium-category-modal.php
 * Description: Premium Category Add/Edit Overlay Modal
 * =============================================================
 */
?>

<!-- ===== Premium Add/Edit Category Panel (Overlay) ===== -->
<div class="premium-category-overlay" id="premiumCategoryOverlay">
    <div class="premium-category-panel" id="categoryPanel">
        <!-- Right Orange Scrollbar Accent -->
        <div class="premium-scrollbar-accent">
            <div class="premium-scrollbar-thumb"></div>
        </div>

        <div class="premium-panel-body">
            <!-- Header -->
            <div class="premium-panel-header">
                <h2 id="panelTitle">Add Category</h2>
                <button class="premium-btn-close-x" id="btnCloseX" title="Close"
                    onclick="closePremiumCategoryPanel()">✕</button>
            </div>

            <!-- Form -->
            <form id="premiumCategoryForm" novalidate enctype="multipart/form-data">
                <input type="hidden" name="action" id="catAction" value="create">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="hidden" name="id" id="catId" value="">

                <!-- Category Name -->
                <div class="premium-field-group">
                    <label class="premium-form-label">Category Name</label>
                    <input type="text" class="premium-form-control" name="name" id="categoryName"
                        placeholder="Enter Category Name*" required
                        oninput="this.classList.toggle('has-value', this.value !== '')">
                </div>

                <!-- Descriptor / "Subcategory" -->
                <div class="premium-field-group">
                    <label class="premium-form-label">Category Descriptor</label>
                    <input type="text" class="premium-form-control" name="description" id="subcategory"
                        placeholder="e.g. Premium Selection / Festive Special"
                        oninput="this.classList.toggle('has-value', this.value !== '')">
                </div>

                <!-- Stock Quantity & Stock Status -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="premium-field-group">
                            <label class="premium-form-label">Stock Quantity</label>
                            <input type="number" name="stock_qty" class="premium-form-control"
                                placeholder="Add Stock Quantity" min="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="premium-field-group">
                            <label class="premium-form-label">Stock Status</label>
                            <select class="premium-form-select" name="stock_status"
                                onchange="this.classList.toggle('has-value', this.value !== '')">
                                <option value="" disabled selected>(In Stock / Low / Out)</option>
                                <option value="In Stock">In Stock</option>
                                <option value="Low Stock">Low Stock</option>
                                <option value="Out of Stock">Out of Stock</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Category Image -->
                <div class="premium-field-group">
                    <label class="premium-form-label">Category Image</label>
                    <div class="premium-upload-wrapper mb-2">
                        <input type="text" class="premium-upload-input" id="catImgText" placeholder="Upload image"
                            readonly>
                        <button type="button" class="premium-upload-btn"
                            onclick="document.getElementById('catImgFile').click()">Add Image</button>
                        <input type="file" name="image" id="catImgFile" accept="image/jpeg,image/png"
                            class="d-none">
                    </div>
                    <div id="catImgPreviewContainer" class="mt-2 text-center" style="display: none;">
                        <img id="catImgPreview" src="" alt="Preview" class="rounded-3 shadow-sm" style="max-width: 100%; height: 120px; object-fit: cover; border: 1px solid #ddd;">
                    </div>
                </div>

                <!-- Status Toggle -->
                <div class="premium-status-row">
                    <span class="premium-status-label">Status</span>
                    <label class="premium-toggle-switch">
                        <input type="checkbox" id="statusToggle" checked>
                        <span class="premium-toggle-slider"></span>
                    </label>
                    <input type="hidden" name="status" id="statusValue" value="1">
                </div>

                <!-- Buttons -->
                <div class="premium-btn-row">
                    <button type="button" class="premium-btn-cancel"
                        onclick="closePremiumCategoryPanel()">Cancel</button>
                    <button type="submit" class="premium-btn-save" id="btnSaveCategory">Save Category</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Premium Toast for Feedback -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 2000">
    <div id="categoryToast" class="toast border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header border-0" style="background: #a34316; color: #ffffff;">
            <strong class="me-auto" id="toastTitle">Sweets Admin</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body fw-bold" id="toastMessage" style="background: #ffffff; color: #1e1e1e;">Category saved
            successfully.</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Premium Category Panel Logic ---
    const overlay = document.getElementById('premiumCategoryOverlay');
    const statusValue = document.getElementById("statusValue");
    const statusToggle = document.getElementById("statusToggle");
    const catImgFile = document.getElementById('catImgFile');
    const catImgText = document.getElementById('catImgText');
    const premiumForm = document.getElementById('premiumCategoryForm');
    const saveBtn = document.getElementById('btnSaveCategory');

    window.openPremiumCategoryPanel = function (data = null) {
        const panelTitle = document.getElementById('panelTitle');
        const catAction = document.getElementById('catAction');
        const catId = document.getElementById('catId');
        const catNameInput = document.getElementById('categoryName');
        const catDescInput = document.getElementById('subcategory');
        const catStatusValue = document.getElementById('statusValue');
        const catStatusToggle = document.getElementById('statusToggle');
        const catImgText = document.getElementById('catImgText');

        if (data) {
            // Edit Mode
            panelTitle.textContent = data.name;
            catAction.value = "update";
            catId.value = data.id;
            
            if (catNameInput) {
                catNameInput.value = data.name;
                catNameInput.classList.add('has-value');
            }
            
            if (catDescInput) {
                catDescInput.value = data.description || '';
                catDescInput.classList.toggle('has-value', !!data.description);
            }

            if (catImgText) {
                catImgText.value = data.image_path ? data.image_path.split('/').pop() : '';
                catImgText.classList.toggle('has-file', !!data.image_path);
            }

            const previewContainer = document.getElementById('catImgPreviewContainer');
            const previewImg = document.getElementById('catImgPreview');
            if (previewContainer && previewImg && data.image_path) {
                previewImg.src = (window.BASE_URL || '') + data.image_path;
                previewContainer.style.display = 'block';
            } else if (previewContainer) {
                previewContainer.style.display = 'none';
            }

            const isActive = (data.status == 1 || data.status === 'active');
            if (catStatusToggle) catStatusToggle.checked = isActive;
            if (catStatusValue) catStatusValue.value = isActive ? "1" : "0";

        } else {
            // Add Mode
            panelTitle.textContent = "Add Category";
            catAction.value = "create";
            catId.value = "";
            premiumForm.reset();
            if (catNameInput) catNameInput.classList.remove('has-value');
            if (catDescInput) catDescInput.classList.remove('has-value');
            if (catImgText) catImgText.classList.remove('has-file');
            if (catStatusToggle) catStatusToggle.checked = true;
            if (catStatusValue) catStatusValue.value = "1";
            const previewContainer = document.getElementById('catImgPreviewContainer');
            if (previewContainer) previewContainer.style.display = 'none';
        }

        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    window.closePremiumCategoryPanel = function () {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        premiumForm.reset();
    };

    if (statusToggle) {
        statusToggle.addEventListener('change', function () {
            statusValue.value = this.checked ? "1" : "0";
        });
    }

    if (catImgFile) {
        catImgFile.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                catImgText.value = this.files[0].name;
                catImgText.classList.add('has-file');
                
                // Show preview for new file
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImg = document.getElementById('catImgPreview');
                    const previewContainer = document.getElementById('catImgPreviewContainer');
                    if (previewImg && previewContainer) {
                        previewImg.src = e.target.result;
                        previewContainer.style.display = 'block';
                    }
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    if (premiumForm) {
        premiumForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (!premiumForm.reportValidity()) return;

            const originalBtnText = saveBtn.textContent;
            try {
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

                const formData = new FormData(premiumForm);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch("api/v1/categories.php", {
                    method: "POST",
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    saveBtn.textContent = '✓ Saved!';
                    saveBtn.style.background = 'linear-gradient(135deg, #2e7d32, #1b5e20)';
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(result.message || "Failed to save category");
                }

            } catch (error) {
                alert('Error: ' + error.message);
                saveBtn.disabled = false;
                saveBtn.textContent = originalBtnText;
                saveBtn.style.background = '';
            }
        });
    }
});
</script>
