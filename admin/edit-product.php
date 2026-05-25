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
