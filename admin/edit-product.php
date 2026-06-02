<?php
/**
 * Sweets Website
 * =============================================================
 * File: edit-product.php
 * Description: Admin Edit Product - Page to edit an existing product
 * =============================================================
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once 'includes/auth.php'; // Protect this page
require_once SERVICES_PATH . '/ProductService.php';
require_once SERVICES_PATH . '/SubcategoryService.php';
require_once REPOS_PATH . '/CategoryRepository.php';

$productId = (int)($_GET['id'] ?? 0);
if ($productId <= 0) {
    header('Location: products.php');
    exit;
}

$productService = new ProductService();
$subcatService  = new SubcategoryService();
$categoryRepo   = new CategoryRepository();

$product = $productService->getProductById($productId);
if (!$product) {
    header('Location: products.php?error=product_not_found');
    exit;
}

$rootCategories = $categoryRepo->getRootCategories();
$subCategories  = $subcatService->getSubcategories((int)($product['category_id'] ?? 0));
$productImages = $productService->getProductImages($productId);

$productName = $product['name'] ?? 'Product';
$productSku = $product['sku'] ?? '';
$previewSkuDisplay = $productSku !== '' ? $productSku : 'N/A';
$productStatus = $product['status'] ?? 'draft';
$stockQuantity = (int)($product['stock_qty'] ?? $product['stock_quantity'] ?? 0);
$weightValue = $product['short_description'] ?? '';
$weightTabs = ['250g', '500g', '1kg'];
$activeWeight = '';
foreach ($weightTabs as $tab) {
    if ($weightValue !== '' && stripos($weightValue, $tab) !== false) {
        $activeWeight = $tab;
        break;
    }
}
$defaultWeightTab = $activeWeight !== '' ? $activeWeight : $weightTabs[0];

$primaryImagePath = $product['image_path'] ?? 'assets/images/placeholders/product-placeholder.png';
$primaryImageUrl = preg_match('/^https?:/i', (string)$primaryImagePath)
    ? $primaryImagePath
    : BASE_URL . ltrim((string)$primaryImagePath, '/');

$previewPriceValue = (isset($product['sale_price']) && $product['sale_price'] !== null && $product['sale_price'] !== '')
    ? $product['sale_price']
    : ($product['base_price'] ?? 0);
$previewPriceDisplay = '₹ ' . number_format((float)$previewPriceValue, 2);
$previewStatusText = $productStatus === 'published' ? 'Active' : ucwords(str_replace('_', ' ', (string)$productStatus));
$previewStatusClass = $productStatus === 'published' ? 'badge-active' : 'badge bg-secondary';
$statusHeading = 'Active Product';
$statusDescription = 'Product is visible and purchasable on the store.';
if ($productStatus === 'draft') {
    $statusHeading = 'Draft Product';
    $statusDescription = 'Product is hidden until you publish it.';
} elseif ($productStatus === 'out_of_stock') {
    $statusHeading = 'Out of Stock';
    $statusDescription = 'Product is hidden because inventory reached zero.';
}
$soldCountDisplay = isset($product['sold_count']) ? (int)$product['sold_count'] : '--';

$pageStyles = ['assets/css/admin/pages/edit-product.css', 'assets/css/admin/pages/product-preview.css'];
$pageScripts = ['assets/js/admin/modals.js'];

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="main-content">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body edit-product-content" style="min-height: calc(100vh - 80px);">
        <form id="editProductForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            <input type="hidden" name="admin_id" value="<?php echo $_SESSION['user_id'] ?? 0; ?>">

            <div class="edit-product-wrapper container-xl px-3 px-md-4 py-4">
                <div class="breadcrumb-custom mb-1">
                    <a href="products.php">Products</a>
                    <span class="separator">&gt;</span>
                    <span>Save Product</span>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                    <h1 class="page-title mb-0">Save Product</h1>
                    <div class="d-flex align-items-center gap-3 header-actions">
                    <button type="button" class="preview-link" id="btnPreviewProduct">
                        <i class="bi bi-eye"></i> Preview Product
                    </button>
                        <a href="products.php" class="btn btn-cancel-top text-decoration-none d-flex align-items-center justify-content-center" type="button">Cancel</a>
                        <button class="btn btn-update" type="submit" id="saveProductBtnTop">Update Product</button>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <section class="section-card">
                            <h5 class="section-title">Basic Information</h5>

                            <div class="mb-3">
                                <label class="form-label form-label-custom">Product Name</label>
                                <input type="text" name="name" class="form-control form-control-custom" value="<?php echo htmlspecialchars($productName); ?>" required>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label form-label-custom">SKU / Product ID</label>
                                    <input type="text" name="sku" class="form-control form-control-custom" value="<?php echo htmlspecialchars($productSku); ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label form-label-custom">Weight / Unit (Optional)</label>
                                    <input type="text" name="short_description" class="form-control form-control-custom" value="<?php echo htmlspecialchars($weightValue); ?>" placeholder="e.g. 500g, 1kg">
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label form-label-custom">Category</label>
                                    <select name="category_id" id="edit_category_id" class="form-select form-select-custom" required onchange="loadSubcategories(this.value, 'edit_subcategory_id')">
                                        <option value="">Select Category</option>
                                        <?php foreach ($rootCategories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo ((int)$product['category_id'] === (int)$cat['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label form-label-custom">Subcategory</label>
                                    <select name="subcategory_id" id="edit_subcategory_id" class="form-select form-select-custom">
                                        <option value="">Select Category First</option>
                                        <?php foreach ($subCategories as $sub): ?>
                                            <option value="<?php echo $sub['id']; ?>" <?php echo ((int)($product['subcategory_id'] ?? 0) === (int)$sub['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sub['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="section-card">
                            <h5 class="section-title">Product Images</h5>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted mb-2">Primary Image</label>
                                <div class="file-overlay-wrapper input-group-file mb-3">
                                    <div class="input-group-file-text">Replace current image...</div>
                                    <button type="button" class="input-group-file-btn">Browse</button>
                                    <input type="file" name="product_image" class="file-overlay-input" accept="image/*"
                                        onchange="updateFileName(this)">
                                </div>
                            </div>
                            
                            <label class="form-label fw-bold small text-muted mb-2">Image Gallery</label>

                            <div class="mb-3 product-thumbs" id="productThumbs">
                                <?php foreach ($productImages as $img): ?>
                                    <?php
                                        $imageId = isset($img['id']) ? (int)$img['id'] : null;
                                        $imagePath = $img['image_path'] ?? '';
                                        if ($imagePath === '') {
                                            continue;
                                        }
                                        $imageUrl = BASE_URL . ltrim($imagePath, '/');
                                    ?>
                                    <div class="product-thumb-wrapper"<?php echo $imageId ? ' data-image-id="' . $imageId . '"' : ''; ?>>
                                        <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Product" class="product-thumb <?php echo (!empty($img['is_main'])) ? 'is-main' : ''; ?>">
                                        <div class="thumb-actions">
                                            <?php if ($imageId): ?>
                                                <?php if (empty($img['is_main'])): ?>
                                                    <span class="thumb-action-btn set-main" title="Set as Primary" onclick="setPrimaryImage(<?php echo $imageId; ?>, this)"><i class="bi bi-star"></i></span>
                                                <?php else: ?>
                                                    <span class="thumb-action-btn is-main-badge" title="Primary Image"><i class="bi bi-star-fill text-warning"></i></span>
                                                <?php endif; ?>
                                                <span class="thumb-action-btn remove" title="Remove" onclick="removeProductImage(<?php echo $imageId; ?>, this)"><i class="bi bi-trash"></i></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($productImages) && !empty($product['image_path'])): ?>
                                    <?php $fallbackUrl = BASE_URL . ltrim($product['image_path'], '/'); ?>
                                    <div class="product-thumb-wrapper">
                                        <img src="<?php echo htmlspecialchars($fallbackUrl); ?>" alt="Product" class="product-thumb">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="drop-zone" id="productDropZone">
                                <div class="upload-icon">
                                    <i class="bi bi-cloud-arrow-down"></i>
                                </div>
                                <p>Drag and drop images here, or <button type="button" class="browse-link" id="triggerProductBrowse">browse</button></p>
                                <p class="file-support">Supports JPG, PNG (Max 5MB)</p>
                                <input type="file" id="productFileInput" name="product_images[]" accept="image/jpeg,image/png" multiple hidden>
                            </div>
                            <div id="selectedFilesList" class="mt-2 small text-muted"></div>
                        </section>

                        <section class="section-card">
                            <h5 class="section-title">Content</h5>

                            <div class="mb-1">
                                <label class="form-label form-label-custom">Description</label>
                                <textarea name="description" class="form-control form-control-custom textarea-desc"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                            </div>
                        </section>

                        <div class="d-flex justify-content-center gap-3 mb-4 mt-2 flex-wrap">
                            <a href="products.php" class="btn btn-cancel-bottom text-decoration-none d-flex align-items-center justify-content-center" type="button">Cancel</a>
                            <button class="btn btn-save" type="submit" id="saveProductBtnBottom">Save Edit</button>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <section class="section-card">
                            <h5 class="right-section-title">Status</h5>
                            <div class="status-card">
                                <div>
                                    <div class="status-label"><?php echo htmlspecialchars($statusHeading); ?></div>
                                    <div class="status-desc"><?php echo htmlspecialchars($statusDescription); ?></div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input form-check-input-custom" name="status" type="checkbox" role="switch" value="published" <?php echo ($productStatus === 'published') ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </section>

                        <!-- Featured / Best Seller -->
                        <section class="section-card">
                            <h5 class="right-section-title"><i class="bi bi-star-fill text-warning me-1"></i>Best Seller</h5>
                            <div class="status-card">
                                <div>
                                    <div class="status-label">Feature in Best Sellers</div>
                                    <div class="status-desc">Show this product in the homepage "Our Best Sellers" section.</div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <!-- Hidden fallback so featured=0 is sent when unchecked -->
                                    <input type="hidden" name="featured" value="0">
                                    <input class="form-check-input form-check-input-custom" name="featured" id="featuredToggle"
                                        type="checkbox" role="switch" value="1"
                                        <?php echo (!empty($product['featured'])) ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </section>

                        <section class="section-card">
                            <h5 class="right-section-title">Pricing</h5>
                            <div class="mb-3">
                                <label class="form-label form-label-custom">Regular Price</label>
                                <div class="price-input-group">
                                    <span class="currency-symbol">&#8377;</span>
                                    <input type="number" name="base_price" step="0.01" class="form-control form-control-custom" value="<?php echo (float)$product['base_price']; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label form-label-custom">Sale Price (Optional)</label>
                                <div class="price-input-group">
                                    <span class="currency-symbol">&#8377;</span>
                                    <input type="number" name="sale_price" step="0.01" class="form-control form-control-custom" value="<?php echo $product['sale_price'] ? (float)$product['sale_price'] : ''; ?>">
                                </div>
                            </div>
                            <div>
                                <label class="form-label form-label-custom">Tax Rate (%)</label>
                                <input type="number" name="tax_rate" step="0.01" class="form-control form-control-custom" value="<?php echo (float)($product['tax_rate'] ?? 0); ?>">
                            </div>
                        </section>

                        <section class="section-card">
                            <h5 class="right-section-title">Inventory</h5>
                            <div class="mb-3">
                                <label class="form-label form-label-custom">Stock Quantity</label>
                                <input type="number" name="stock_quantity" class="form-control form-control-custom" value="<?php echo (int)$stockQuantity; ?>">
                            </div>
                        </section>

                        <!-- ── Weight Variants ── -->
                        <section class="section-card" id="variantsSection">
                            <h5 class="right-section-title d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-layers me-1"></i> Weight Variants</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary shadow-none py-0" id="btnAddVariantRow"
                                    style="font-size:12px; border-radius:6px;">
                                    <i class="bi bi-plus"></i> Add Row
                                </button>
                            </h5>
                            <p class="text-muted small mb-3">Define per-weight pricing and stock (250g / 500g / 1kg). These show as selector buttons on the product page.</p>

                            <div id="variantsTableWrapper" style="overflow-x:auto;">
                                <table class="table table-sm align-middle mb-2" id="variantsTable">
                                    <thead>
                                        <tr>
                                            <th class="text-muted" style="font-size:11px;font-weight:600;">Weight</th>
                                            <th class="text-muted" style="font-size:11px;font-weight:600;">Label</th>
                                            <th class="text-muted" style="font-size:11px;font-weight:600;">Price (₹)</th>
                                            <th class="text-muted" style="font-size:11px;font-weight:600;">Stock</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="variantRows">
                                        <tr id="variantLoadingRow">
                                            <td colspan="5" class="text-muted small py-3 text-center">
                                                <div class="spinner-border spinner-border-sm me-1"></div> Loading...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-sm shadow-none fw-bold px-3 py-1"
                                    id="btnSaveVariants"
                                    style="background:#8c3333; color:#fff; border-radius:8px; font-size:13px;">
                                    <i class="bi bi-check2-circle me-1"></i> Save Variants
                                </button>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php 
// Include Global Modals
require_once 'includes/modals/product-preview.php';
?>

<!-- Success/Error Toast -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="feedbackToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto" id="toastTitle">Product Update</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropZone = document.getElementById('productDropZone');
        const fileInput = document.getElementById('productFileInput');
        const browseButton = document.getElementById('triggerProductBrowse');
        const thumbsContainer = document.getElementById('productThumbs');
        const filesListLabel = document.getElementById('selectedFilesList');
        const editForm = document.getElementById('editProductForm');
        const toastEl = document.getElementById('feedbackToast');
        const feedbackToast = new bootstrap.Toast(toastEl);

        if (!editForm) {
            return;
        }

        // --- Preview Mode Handling ---
        const btnPreview = document.getElementById('btnPreviewProduct');
        if (btnPreview) {
            btnPreview.addEventListener('click', function(e) {
                e.preventDefault();
                const formData = new FormData(editForm);
                const data = {
                    name: formData.get('name') || 'New Product',
                    base_price: formData.get('base_price') || 0,
                    sale_price: formData.get('sale_price') || '',
                    sku: formData.get('sku') || 'N/A',
                    stock_quantity: formData.get('stock_quantity') || 0,
                    description: formData.get('description') || '',
                    status: formData.get('status') === 'published' ? 'published' : 'draft',
                    image: document.querySelector('.product-thumb')?.src.replace(window.BASE_URL, '') || ''
                };
                
                if (typeof window.openPreviewMode === 'function') {
                    window.openPreviewMode(data);
                }
            });
        }

        function showFeedback(message, title = 'Product Update', isError = false) {
            document.getElementById('toastTitle').textContent = title;
            document.getElementById('toastMessage').textContent = message;
            toastEl.classList.toggle('text-bg-danger', isError);
            toastEl.classList.toggle('text-bg-success', !isError);
            feedbackToast.show();
        }

        if (!dropZone || !fileInput || !browseButton || !thumbsContainer) return;

        // --- File Upload UI Handling ---
        const preventDefaults = (event) => {
            event.preventDefault();
            event.stopPropagation();
        };

        // Track files in a central DataTransfer object
        const dt = new DataTransfer();

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (event) => {
                preventDefaults(event);
                dropZone.classList.add('is-dragging');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (event) => {
                preventDefaults(event);
                dropZone.classList.remove('is-dragging');
            });
        });

        dropZone.addEventListener('drop', (event) => {
            const files = event.dataTransfer && event.dataTransfer.files;
            if (files && files.length > 0) {
                // Add new files to our central DataTransfer object
                for (let i = 0; i < files.length; i++) {
                    dt.items.add(files[i]);
                }
                fileInput.files = dt.files;
                handleFiles(files);
            }
        });

        // Make entire drop zone clickable
        dropZone.addEventListener('click', (e) => {
            if (e.target !== browseButton) {
                fileInput.click();
            }
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                // Sync manual selection with our DataTransfer if needed
                // For simplicity, we'll just handle the new files
                handleFiles(fileInput.files);
            }
        });

        browseButton.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.click();
        });

        function handleFiles(files) {
            if (filesListLabel) {
                const totalFiles = dt.files.length > 0 ? dt.files.length : files.length;
                filesListLabel.textContent = `${totalFiles} file(s) selected for upload`;
            }
            
            // Preview locally
            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'product-thumb-wrapper local-preview';
                    wrapper.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="product-thumb">
                        <span class="thumb-remove" role="button" tabindex="0">&times;</span>
                    `;
                    thumbsContainer.appendChild(wrapper);
                    
                    wrapper.querySelector('.thumb-remove').addEventListener('click', (ev) => {
                        ev.stopPropagation();
                        wrapper.remove();
                        // Note: Removing from FileList is complex, for now we just remove the UI.
                        // In a full implementation, we'd rebuild 'dt' excluding this file.
                    });
                };
                reader.readAsDataURL(file);
            });
        }

        // --- Form Submission ---
        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            
            const submitBtns = [document.getElementById('saveProductBtnTop'), document.getElementById('saveProductBtnBottom')];
            submitBtns.forEach(btn => btn.disabled = true);
            
            try {
                const formData = new FormData(editForm);
                
                // If status switch is OFF, it won't be in FormData, so we add it as 'draft'
                if (!formData.has('status')) {
                    formData.append('status', 'draft');
                }

                // Featured: the hidden input sends 0 by default;
                // if the checkbox is checked, FormData will have two 'featured' entries —
                // the server reads the last one (1). No extra handling needed.

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const response = await fetch('api/v1/products.php', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    showFeedback(result.message);
                    setTimeout(() => window.location.href = 'products.php', 1500);
                } else {
                    showFeedback(result.message, 'Error', true);
                }
            } catch (error) {
                showFeedback('Failed to update product. Please check console.', 'Error', true);
                console.error(error);
            } finally {
                submitBtns.forEach(btn => btn.disabled = false);
            }
        });

        // ── Variants Management ──
        const PRODUCT_ID    = <?php echo $productId; ?>;
        const CSRF_TOKEN    = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
        const ALLOWED_WEIGHTS = ['250g', '500g', '1kg'];

        function variantRowHTML(v) {
            const id     = v.id    || 0;
            const weight = v.weight || '';
            const label  = v.label  || (weight + ' Pack');
            const price  = v.price  || '';
            const stock  = v.stock !== undefined ? v.stock : 0;
            const isSaved = id > 0;

            const weightOpts = ALLOWED_WEIGHTS.map(w =>
                `<option value="${w}" ${w === weight ? 'selected' : ''}>${w}</option>`
            ).join('');

            return `
            <tr data-variant-id="${id}">
                <td style="min-width:80px;">
                    ${isSaved
                        ? `<span class="badge bg-secondary" style="font-size:12px;">${weight}</span>
                           <input type="hidden" name="variant_id[]" value="${id}">
                           <input type="hidden" name="variant_weight[]" value="${weight}">`
                        : `<select name="variant_weight[]" class="form-select form-select-sm shadow-none" style="min-width:70px;">${weightOpts}</select>
                           <input type="hidden" name="variant_id[]" value="0">`
                    }
                </td>
                <td>
                    <input type="text" name="variant_label[]" value="${label}"
                        class="form-control form-control-sm shadow-none" placeholder="e.g. 500g Pack">
                </td>
                <td style="min-width:90px;">
                    <input type="number" name="variant_price[]" value="${price}"
                        class="form-control form-control-sm shadow-none" step="0.01" min="0" placeholder="₹">
                </td>
                <td style="min-width:75px;">
                    <input type="number" name="variant_stock[]" value="${stock}"
                        class="form-control form-control-sm shadow-none" min="0" placeholder="0">
                </td>
                <td>
                    ${isSaved
                        ? `<button type="button" class="btn btn-link text-danger p-0 btn-delete-variant"
                               data-id="${id}" title="Delete Variant"><i class="bi bi-trash"></i></button>`
                        : `<button type="button" class="btn btn-link text-danger p-0 btn-remove-row" title="Remove Row"><i class="bi bi-x-circle"></i></button>`
                    }
                </td>
            </tr>`;
        }

        async function loadVariants() {
            const variantRows = document.getElementById('variantRows');
            try {
                const res  = await fetch(`api/get_variants.php?product_id=${PRODUCT_ID}`);
                const data = await res.json();
                renderVariants(data.variants || []);
            } catch(e) {
                renderVariants([]);
            }
        }

        function renderVariants(variants) {
            const variantRows = document.getElementById('variantRows');
            if (variants.length === 0) {
                variantRows.innerHTML = `<tr><td colspan="5" class="text-muted small text-center py-3">No variants yet. Click "Add Row" to create one.</td></tr>`;
                return;
            }
            variantRows.innerHTML = variants.map(variantRowHTML).join('');
        }

        loadVariants();

        document.getElementById('btnAddVariantRow')?.addEventListener('click', () => {
            const variantRows = document.getElementById('variantRows');
            const usedWeights = [...variantRows.querySelectorAll('[name="variant_weight[]"]')].map(el => el.value);
            const nextWeight = ALLOWED_WEIGHTS.find(w => !usedWeights.includes(w)) || '250g';
            const dummy = document.createElement('tbody');
            dummy.innerHTML = variantRowHTML({ id: 0, weight: nextWeight, label: '', price: '', stock: 0 });
            variantRows.appendChild(dummy.querySelector('tr'));
        });

        document.getElementById('variantRows')?.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.btn-remove-row');
            if (removeBtn) { removeBtn.closest('tr').remove(); }
        });

        document.getElementById('variantRows')?.addEventListener('click', async function(e) {
            const deleteBtn = e.target.closest('.btn-delete-variant');
            if (!deleteBtn) return;
            if (!confirm('Delete this variant? This cannot be undone.')) return;

            const variantId = deleteBtn.dataset.id;
            const formData  = new FormData();
            formData.append('action', 'delete_variant');
            formData.append('product_id', PRODUCT_ID);
            formData.append('variant_id', variantId);
            formData.append('csrf_token', CSRF_TOKEN);

            deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            deleteBtn.disabled  = true;

            try {
                const res  = await fetch('api/v1/products.php', {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')},
                    body: formData
                });
                const data = await res.json();
                if (data.status === 'success') {
                    deleteBtn.closest('tr').remove();
                    const vr = document.getElementById('variantRows');
                    if (vr && vr.querySelectorAll('tr[data-variant-id]').length === 0) renderVariants([]);
                } else {
                    alert(data.message || 'Failed to delete variant.');
                    deleteBtn.innerHTML = '<i class="bi bi-trash"></i>';
                    deleteBtn.disabled  = false;
                }
            } catch {
                alert('Network error while deleting.');
                deleteBtn.innerHTML = '<i class="bi bi-trash"></i>';
                deleteBtn.disabled  = false;
            }
        });

        document.getElementById('btnSaveVariants')?.addEventListener('click', async () => {
            const btn         = document.getElementById('btnSaveVariants');
            const variantRows = document.getElementById('variantRows');
            const rows        = variantRows.querySelectorAll('tr[data-variant-id]');
            if (rows.length === 0) { alert('Add at least one variant before saving.'); return; }

            const formData = new FormData();
            formData.append('action', 'save_variants');
            formData.append('product_id', PRODUCT_ID);
            formData.append('csrf_token', CSRF_TOKEN);

            rows.forEach(row => {
                formData.append('variant_weight[]', row.querySelector('[name="variant_weight[]"]')?.value || '');
                formData.append('variant_label[]',  row.querySelector('[name="variant_label[]"]')?.value  || '');
                formData.append('variant_price[]',  row.querySelector('[name="variant_price[]"]')?.value  || '0');
                formData.append('variant_stock[]',  row.querySelector('[name="variant_stock[]"]')?.value  || '0');
                formData.append('variant_id[]',     row.querySelector('[name="variant_id[]"]')?.value     || '0');
            });

            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
            btn.disabled  = true;

            try {
                const res  = await fetch('api/v1/products.php', {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')},
                    body: formData
                });
                const data = await res.json();
                if (data.status === 'success') {
                    btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Saved!';
                    setTimeout(() => {
                        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Variants';
                        btn.disabled  = false;
                        loadVariants();
                    }, 1500);
                } else {
                    alert(data.message || 'Failed to save variants.');
                    btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Variants';
                    btn.disabled  = false;
                }
            } catch {
                alert('Network error while saving variants.');
                btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Variants';
                btn.disabled  = false;
            }
        });

    });

    // --- UI Helpers ---
    function updateFileName(input) {
        const fileName = input.files[0] ? input.files[0].name : "Replace current image...";
        const wrapper = input.closest('.file-overlay-wrapper');
        if (wrapper) {
            const textEl = wrapper.querySelector('.input-group-file-text');
            if (textEl) textEl.textContent = fileName;
        }
    }

    // --- Image Deletion (Existing) ---
    async function removeProductImage(imageId, element) {
        if (!confirm('Are you sure you want to remove this image from the gallery?')) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'delete_gallery_image');
            formData.append('image_id', imageId);
            formData.append('product_id', <?php echo $productId; ?>);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('api/v1/products.php', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });

            const result = await response.json();
            if (result.status === 'success') {
                element.closest('.product-thumb-wrapper').remove();
            } else {
                alert(result.message || 'Failed to delete image');
            }
        } catch (error) {
            console.error('Delete error:', error);
            alert('A system error occurred while deleting the image.');
        }
    }

    async function setPrimaryImage(imageId, element) {
        try {
            const formData = new FormData();
            formData.append('action', 'set_primary_image');
            formData.append('image_id', imageId);
            formData.append('product_id', <?php echo $productId; ?>);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('api/v1/products.php', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });

            const result = await response.json();
            if (result.status === 'success') {
                // Simplest way is to reload to refresh all thumbnail states
                window.location.reload();
            } else {
                alert(result.message || 'Failed to set primary image');
            }
        } catch (error) {
            console.error('Update error:', error);
            alert('A system error occurred.');
        }
    }
    window.loadSubcategories = async (categoryId, targetSelectId) => {
        const select = document.getElementById(targetSelectId);
        if (!select) return;

        select.innerHTML = '<option value="">Loading...</option>';
        
        try {
            const response = await fetch(`api/v1/subcategories.php?category_id=${categoryId}`);
            const result = await response.json();
            
            if (result.success) {
                let html = '<option value="">Select Subcategory</option>';
                result.data.forEach(sub => {
                    html += `<option value="${sub.id}">${sub.name}</option>`;
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
