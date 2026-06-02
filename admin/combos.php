<?php
/**
 * Sweets Website - Admin Combo Offers Management (with Gallery)
 * File: admin/combos.php
 */
$pageStyles  = ['assets/css/admin/products.css'];
$pageScripts = [];
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php';
require_once SERVICES_PATH . '/ComboService.php';
require_once SERVICES_PATH . '/ProductService.php';

$comboService   = new ComboService();
$productService = new ProductService();

$combos   = $comboService->getAllCombosAdmin();
$stats    = $comboService->getComboStats();
$products = $productService->getAllProducts();
?>

<div class="main-content products-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 products-content-body">

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 mb-3 border-bottom px-4 mx-n4">
            <div>
                <h2 class="fw-bold mb-0 products-page-title">Combo Offers</h2>
                <p class="text-muted small mb-0 mt-1">Create and manage combo bundles from existing products.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn rounded-2 d-flex align-items-center products-outline-btn products-add-btn"
                    data-bs-toggle="offcanvas" data-bs-target="#addComboOffcanvas">
                    <i class="bi bi-plus-lg me-2 fs-5"></i> Add Combo
                </button>
            </div>
        </div>

        <div class="px-2 pb-5">

            <!-- Stats -->
            <div class="row g-4 mb-5">
                <div class="col-xl-4 col-md-6">
                    <div class="admin-card p-4 h-100 d-flex align-items-center gap-3" onclick="filterCombos('all')" style="cursor:pointer;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;background:linear-gradient(135deg,#7A1E1E,#C65D00);">
                            <i class="bi bi-gift text-white fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold mb-1">Total Combos</div>
                            <h3 class="fw-bolder mb-0"><?php echo $stats['total']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="admin-card p-4 h-100 d-flex align-items-center gap-3" onclick="filterCombos('active')" style="cursor:pointer;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;background:linear-gradient(135deg,#1a6b3c,#2ecc71);">
                            <i class="bi bi-check-circle text-white fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold mb-1">Active Combos</div>
                            <h3 class="fw-bolder mb-0"><?php echo $stats['active']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="admin-card p-4 h-100 d-flex align-items-center gap-3" onclick="filterCombos('all')" style="cursor:pointer;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px;background:linear-gradient(135deg,#1a3a6b,#2e86cc);">
                            <i class="bi bi-box-seam text-white fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold mb-1">Total Items Mapped</div>
                            <h3 class="fw-bolder mb-0"><?php echo $stats['items']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <h4 class="fw-bold text-dark mb-4 products-table-title">All Combo Offers</h4>
            <div class="table-responsive products-table-wrapper">
                <table class="table align-middle mb-0" id="combosTable">
                    <thead class="products-table-head">
                        <tr>
                            <th class="ps-4 py-3">Image</th>
                            <th class="py-3">Combo Name</th>
                            <th class="py-3 d-none d-md-table-cell">Category</th>
                            <th class="py-3 d-none d-md-table-cell">Fixed Price</th>
                            <th class="py-3 text-center">Items</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="py-3 text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="products-table-body">
                        <?php if (empty($combos)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state-card text-center py-5">
                                        <i class="bi bi-gift fs-1 text-muted d-block mb-3"></i>
                                        <h5 class="fw-bold">No Combos Yet</h5>
                                        <p class="text-muted">Create your first combo offer to get started.</p>
                                        <button class="btn products-outline-btn rounded-pill px-4 mt-2"
                                            data-bs-toggle="offcanvas" data-bs-target="#addComboOffcanvas">
                                            Add Combo
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($combos as $combo): ?>
                                <tr class="combo-row" data-active="<?php echo $combo['is_active'] ? '1' : '0'; ?>">
                                    <td class="ps-4 py-3">
                                        <img src="<?php echo BASE_URL . htmlspecialchars($combo['image'] ?? 'assets/images/placeholder.png'); ?>"
                                             class="rounded-2 product-thumb"
                                             style="width:56px;height:56px;object-fit:cover;"
                                             onerror="this.src='<?php echo BASE_URL; ?>assets/images/placeholder.png'">
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($combo['name']); ?></div>
                                        <div class="text-muted small mt-1" style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            <?php echo htmlspecialchars($combo['description'] ?? ''); ?>
                                        </div>
                                    </td>
                                    <td class="py-3 d-none d-md-table-cell">
                                        <span class="badge bg-light text-dark border text-capitalize">
                                            <?php echo htmlspecialchars($combo['category'] ?? '—'); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 d-none d-md-table-cell fw-bold">
                                        <?php 
                                            if ($combo['price'] > 0) {
                                                echo '₹' . number_format($combo['price'], 2);
                                            } else {
                                                echo '₹' . number_format($combo['final_price'] ?? 0, 2) . ' <br><span class="text-muted" style="font-size:10px;">(Dynamic)</span>';
                                            }
                                        ?>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge rounded-pill" style="background:#f0e6c8;color:#7A1E1E;font-size:0.85rem;">
                                            <?php echo (int)($combo['item_count'] ?? 0); ?> items
                                        </span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input combo-toggle" type="checkbox"
                                                   role="switch"
                                                   data-id="<?php echo $combo['id']; ?>"
                                                   <?php echo $combo['is_active'] ? 'checked' : ''; ?>>
                                        </div>
                                        <div class="small text-muted mt-1" style="font-size:10px;">
                                            <?php echo $combo['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </div>
                                    </td>
                                    <td class="py-3 pe-4 text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <?php
                                            $jsData = htmlspecialchars(json_encode([
                                                'id'          => $combo['id'],
                                                'name'        => $combo['name'],
                                                'description' => $combo['description'] ?? '',
                                                'category'    => $combo['category'] ?? '',
                                                'price'       => $combo['price'] ?? 0,
                                                'image'       => $combo['image'] ?? '',
                                                'is_active'   => $combo['is_active'],
                                            ]), ENT_QUOTES, 'UTF-8');
                                            ?>
                                            <button type="button"
                                                    class="btn btn-link text-dark p-0 fs-6 text-decoration-none shadow-none"
                                                    title="Edit Combo"
                                                    onclick='openEditCombo(<?php echo $jsData; ?>, <?php echo $combo['id']; ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-link text-danger p-0 fs-6 text-decoration-none shadow-none"
                                                    title="Delete Combo"
                                                    onclick='deleteCombo(<?php echo $combo['id']; ?>, <?php echo htmlspecialchars(json_encode($combo['name']), ENT_QUOTES); ?>)'>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== ADD COMBO OFFCANVAS ===== -->
    <div class="offcanvas offcanvas-end border-0 products-add-offcanvas" tabindex="-1" id="addComboOffcanvas" aria-labelledby="addComboLabel">
        <div class="offcanvas-header pt-4 pb-2 px-4">
            <h3 class="fw-bolder text-dark mb-0" id="addComboLabel">Add Combo</h3>
            <button type="button" class="btn p-0 shadow-none border-0 pe-2" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="bi bi-x-lg fs-5 text-muted"></i>
            </button>
        </div>
        <div class="offcanvas-body px-4 custom-scrollbar pb-5">
            <form id="addComboForm" action="<?php echo BASE_URL; ?>admin/api/v1/combos.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="hidden" name="items_json" id="add_items_json" value="[]">

                <div class="mb-3">
                    <label class="form-label form-label-custom d-block">Combo Name *</label>
                    <input type="text" name="name" class="form-control form-control-custom shadow-none" placeholder="e.g. Festive Sweet Box" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label form-label-custom d-block">Category</label>
                        <select name="category" class="form-select form-select-custom shadow-none">
                            <option value="">— Select —</option>
                            <option value="karadant">Karadant</option>
                            <option value="namkeen">Namkeen</option>
                            <option value="laddu">Laddu</option>
                            <option value="gifting">Gifting</option>
                            <option value="mixed">Mixed</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label form-label-custom d-block">Fixed Price (₹)</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control form-control-custom shadow-none" placeholder="0 = dynamic">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label form-label-custom d-block">Description</label>
                    <textarea name="description" rows="3" class="form-control form-control-custom shadow-none" placeholder="Short combo description..."></textarea>
                </div>

                <!-- Primary Image Upload -->
                <div class="mb-4">
                    <label class="form-label form-label-custom d-block">Primary Image</label>
                    <div class="file-overlay-wrapper input-group-file">
                        <div class="input-group-file-text">Choose image...</div>
                        <button type="button" class="input-group-file-btn">Browse</button>
                        <input type="file" name="combo_image" class="file-overlay-input" accept="image/*" onchange="updateFileName(this)">
                    </div>
                </div>

                <!-- Product Items Builder -->
                <div class="mb-4">
                    <label class="form-label form-label-custom d-block fw-bold">Products in this Combo</label>
                    <div id="add_items_list" class="d-flex flex-column gap-2 mb-2"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mb-3"
                            onclick="addProductRow('add_items_list', 'add_items_json', null, 'add_price_summary', 'price')">
                        <i class="bi bi-plus-circle me-1"></i> Add Product
                    </button>
                    <!-- Live Price Preview -->
                    <div id="add_price_summary" class="rounded-3 p-3 d-none" style="background:#fffbf2;border:1px solid #f0e6c8;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-bold">Item Total (MRP)</span>
                            <span class="fw-bold" id="add_mrp_total">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-bold">Suggested Fixed Price</span>
                            <span class="fw-bold" style="color:var(--color-primary,#7A1E1E);" id="add_suggested_price">₹0.00</span>
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <small class="text-muted">↑ Pre-filled in Fixed Price field. Edit to override.</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="button" class="btn fw-bold rounded-2 px-4 shadow-none products-cancel-btn" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn fw-bold rounded-2 px-4 shadow-none products-save-btn">Create Combo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== EDIT COMBO OFFCANVAS ===== -->
    <div class="offcanvas offcanvas-end border-0 products-add-offcanvas" tabindex="-1" id="editComboOffcanvas" aria-labelledby="editComboLabel">
        <div class="offcanvas-header pt-4 pb-2 px-4">
            <h3 class="fw-bolder text-dark mb-0" id="editComboLabel">Edit Combo</h3>
            <button type="button" class="btn p-0 shadow-none border-0 pe-2" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="bi bi-x-lg fs-5 text-muted"></i>
            </button>
        </div>
        <div class="offcanvas-body px-4 custom-scrollbar pb-5">
            <form id="editComboForm" action="<?php echo BASE_URL; ?>admin/api/v1/combos.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="hidden" name="combo_id" id="edit_combo_id">
                <input type="hidden" name="items_json" id="edit_items_json" value="[]">

                <div class="mb-3">
                    <label class="form-label form-label-custom d-block">Combo Name *</label>
                    <input type="text" name="name" id="edit_combo_name" class="form-control form-control-custom shadow-none" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label form-label-custom d-block">Category</label>
                        <select name="category" id="edit_combo_category" class="form-select form-select-custom shadow-none">
                            <option value="">— Select —</option>
                            <option value="karadant">Karadant</option>
                            <option value="namkeen">Namkeen</option>
                            <option value="laddu">Laddu</option>
                            <option value="gifting">Gifting</option>
                            <option value="mixed">Mixed</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label form-label-custom d-block">Fixed Price (₹)</label>
                        <input type="number" step="0.01" min="0" name="price" id="edit_combo_price" class="form-control form-control-custom shadow-none">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label form-label-custom d-block">Description</label>
                    <textarea name="description" id="edit_combo_desc" rows="3" class="form-control form-control-custom shadow-none"></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch d-flex align-items-center gap-3 p-3 rounded-3" style="background:#fffbf2;border:1px solid #f0e6c8;">
                        <input class="form-check-input shadow-none" type="checkbox" role="switch"
                               name="is_active" id="edit_combo_active" value="1" style="width:2.5em;height:1.4em;">
                        <label class="form-check-label fw-bold" for="edit_combo_active">Active (visible to customers)</label>
                    </div>
                </div>

                <!-- ── Gallery Manager ── -->
                <div class="mb-4">
                    <label class="form-label form-label-custom d-block fw-bold">
                        <i class="bi bi-images me-1"></i> Image Gallery
                    </label>

                    <!-- Existing images grid -->
                    <div id="edit_gallery_grid" class="combo-gallery-grid mb-3"></div>

                    <!-- Upload new images -->
                    <div class="combo-gallery-upload-zone" id="edit_gallery_drop_zone">
                        <i class="bi bi-cloud-upload fs-3 text-muted d-block mb-2"></i>
                        <div class="text-muted small mb-2">Drop images here or click to upload</div>
                        <div class="text-muted" style="font-size:11px;">Supports JPG, PNG, WebP</div>
                        <input type="file" id="edit_gallery_file_input" accept="image/*" multiple
                               style="position:absolute;inset:0;opacity:0;cursor:pointer;">
                    </div>

                    <div id="edit_gallery_upload_status" class="mt-2"></div>
                </div>

                <!-- Product Items Builder (Edit) -->
                <div class="mb-4">
                    <label class="form-label form-label-custom d-block fw-bold">Products in this Combo</label>
                    <div id="edit_items_list" class="d-flex flex-column gap-2 mb-2"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mb-3"
                            onclick="addProductRow('edit_items_list', 'edit_items_json', null, 'edit_price_summary', 'edit_combo_price')">
                        <i class="bi bi-plus-circle me-1"></i> Add Product
                    </button>
                    <!-- Live Price Preview -->
                    <div id="edit_price_summary" class="rounded-3 p-3 d-none" style="background:#fffbf2;border:1px solid #f0e6c8;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-bold">Item Total (MRP)</span>
                            <span class="fw-bold" id="edit_mrp_total">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-bold">Suggested Fixed Price</span>
                            <span class="fw-bold" style="color:var(--color-primary,#7A1E1E);" id="edit_suggested_price">₹0.00</span>
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <small class="text-muted">↑ Pre-filled in Fixed Price field. Edit to override.</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="button" class="btn fw-bold rounded-2 px-4 shadow-none products-cancel-btn" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn fw-bold rounded-2 px-4 shadow-none products-save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div><!-- .main-content -->

<!-- ── Gallery styles ── -->
<style>
.combo-gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(88px, 1fr));
    gap: 10px;
}
.combo-gallery-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    aspect-ratio: 1;
    border: 2px solid #e5e7eb;
    background: #f9fafb;
    cursor: pointer;
    transition: border-color .2s;
}
.combo-gallery-item.is-primary {
    border-color: #7A1E1E;
    box-shadow: 0 0 0 2px #7A1E1E33;
}
.combo-gallery-item img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.combo-gallery-item__overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,0.38);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    opacity: 0;
    transition: opacity .2s;
}
.combo-gallery-item:hover .combo-gallery-item__overlay { opacity: 1; }
.combo-gallery-item__overlay button {
    background: rgba(255,255,255,.92);
    border: none;
    border-radius: 50%;
    width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    cursor: pointer;
    transition: transform .15s;
}
.combo-gallery-item__overlay button:hover { transform: scale(1.12); }
.combo-gallery-item__primary-badge {
    position: absolute;
    top: 4px; left: 4px;
    background: #7A1E1E;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 3px;
    letter-spacing: .5px;
    pointer-events: none;
}
.combo-gallery-upload-zone {
    border: 2px dashed #d1d5db;
    border-radius: 10px;
    padding: 20px 12px;
    text-align: center;
    position: relative;
    cursor: pointer;
    transition: border-color .2s, background .2s;
}
.combo-gallery-upload-zone:hover,
.combo-gallery-upload-zone.drag-over {
    border-color: #7A1E1E;
    background: #fff8f5;
}
.combo-gallery-spinner {
    display: inline-block;
    width: 14px; height: 14px;
    border: 2px solid #d1d5db;
    border-top-color: #7A1E1E;
    border-radius: 50%;
    animation: spin .7s linear infinite;
    vertical-align: middle;
    margin-right: 5px;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<!-- Product catalog for JS (used in item picker) -->
<script>
const ALL_PRODUCTS = <?php echo json_encode(array_map(function($p) {
    $price = $p['sale_price'] ?? $p['base_price'] ?? $p['price'] ?? 0;
    return [
        'id'    => (int)$p['id'],
        'name'  => $p['name'],
        'price' => (float)$price,
        'image' => $p['image'] ?? 'assets/images/placeholder.png',
    ];
}, $products)); ?>;

let _editComboId = 0;   // track current combo being edited

/* ────────────────────────────────────────────────
   Product Row Builder
──────────────────────────────────────────────── */
function addProductRow(listId, jsonId, prefill = null, summaryId = null, priceInputId = null) {
    const list   = document.getElementById(listId);
    const row    = document.createElement('div');
    row.className = 'combo-item-row d-flex align-items-center gap-2 p-2 rounded-3 border bg-white';
    row.dataset.summaryId  = summaryId || '';
    row.dataset.priceInput = priceInputId || '';

    const selectHTML = ALL_PRODUCTS.map(p =>
        `<option value="${p.id}" data-price="${p.price}" ${prefill && prefill.product_id == p.id ? 'selected' : ''}>${p.name} (₹${parseFloat(p.price).toFixed(2)})</option>`
    ).join('');

    row.innerHTML = `
        <select class="form-select form-select-sm shadow-none flex-grow-1"
                onchange="syncItems('${listId}','${jsonId}','${summaryId || ''}','${priceInputId || ''}')">
            <option value="" data-price="0">— Select Product —</option>
            ${selectHTML}
        </select>
        <input type="number" min="1" value="${prefill ? prefill.quantity : 1}"
               class="form-control form-control-sm shadow-none" style="width:70px;"
               placeholder="Qty"
               onchange="syncItems('${listId}','${jsonId}','${summaryId || ''}','${priceInputId || ''}')">
        <button type="button" class="btn btn-sm btn-link text-danger p-0 shadow-none"
                onclick="this.closest('.combo-item-row').remove(); syncItems('${listId}','${jsonId}','${summaryId || ''}','${priceInputId || ''}')">  
            <i class="bi bi-x-circle fs-5"></i>
        </button>`;

    list.appendChild(row);
    syncItems(listId, jsonId, summaryId, priceInputId);
}

function syncItems(listId, jsonId, summaryId, priceInputId) {
    const rows  = document.querySelectorAll(`#${listId} .combo-item-row`);
    const items = [];
    let   total = 0;

    rows.forEach(row => {
        const select = row.querySelector('select');
        const qty    = parseInt(row.querySelector('input[type=number]')?.value || 0);
        if (select && select.value && qty > 0) {
            const price = parseFloat(select.selectedOptions[0]?.dataset?.price || 0);
            items.push({ product_id: select.value, quantity: qty });
            total += price * qty;
        }
    });

    const jsonInput = document.getElementById(jsonId);
    if (jsonInput) jsonInput.value = JSON.stringify(items);

    if (summaryId) {
        const summaryEl  = document.getElementById(summaryId);
        const mrpEl      = document.getElementById(summaryId.replace('_price_summary','_mrp_total'));
        const suggestEl  = document.getElementById(summaryId.replace('_price_summary','_suggested_price'));
        const priceInput = priceInputId ? document.getElementById(priceInputId) : null;

        if (summaryEl) summaryEl.classList.toggle('d-none', items.length === 0);
        if (mrpEl)     mrpEl.textContent     = '₹' + total.toFixed(2);

        const suggested = total > 0 ? Math.floor(total * 0.9) : 0;
        if (suggestEl) suggestEl.textContent = '₹' + suggested.toFixed(2);

        if (priceInput && (priceInput.value === '' || parseFloat(priceInput.value) === 0)) {
            priceInput.value = suggested > 0 ? suggested.toFixed(2) : '';
        }
    }
}

/* ────────────────────────────────────────────────
   Gallery Manager
──────────────────────────────────────────────── */
function renderGalleryGrid(images) {
    const grid = document.getElementById('edit_gallery_grid');
    if (!grid) return;
    grid.innerHTML = '';

    if (!images || images.length === 0) {
        grid.innerHTML = '<p class="text-muted small mb-0">No images yet. Upload below.</p>';
        return;
    }

    images.forEach(img => {
        const isPrimary = parseInt(img.is_primary) === 1;
        const item = document.createElement('div');
        item.className = 'combo-gallery-item' + (isPrimary ? ' is-primary' : '');
        item.dataset.imageId = img.id;
        item.innerHTML = `
            ${isPrimary ? '<span class="combo-gallery-item__primary-badge">PRIMARY</span>' : ''}
            <img src="${window.BASE_URL + img.image_path}" alt="" loading="lazy"
                 onerror="this.src='${window.BASE_URL}assets/images/placeholder.png'">
            <div class="combo-gallery-item__overlay">
                ${!isPrimary ? `<button type="button" title="Set as primary" onclick="gallerySetPrimary(${img.id})">
                    <i class="bi bi-star-fill text-warning"></i>
                </button>` : ''}
                <button type="button" title="Delete image" onclick="galleryDeleteImage(${img.id}, this)">
                    <i class="bi bi-trash text-danger"></i>
                </button>
            </div>`;
        grid.appendChild(item);
    });
}

function gallerySetPrimary(imageId) {
    const fd = new FormData();
    fd.append('action', 'set_primary_image');
    fd.append('combo_id', _editComboId);
    fd.append('image_id', imageId);
    fd.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
    fetch(window.BASE_URL + 'admin/api/v1/combos.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') reloadGallery();
            else alert('Error: ' + res.message);
        });
}

function galleryDeleteImage(imageId, btn) {
    if (!confirm('Delete this image?')) return;
    btn.disabled = true;
    const fd = new FormData();
    fd.append('action', 'delete_image');
    fd.append('image_id', imageId);
    fd.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
    fetch(window.BASE_URL + 'admin/api/v1/combos.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') reloadGallery();
            else { btn.disabled = false; alert('Error: ' + res.message); }
        });
}

function reloadGallery() {
    if (!_editComboId) return;
    fetch(window.BASE_URL + `admin/api/v1/combos.php?action=get_images&combo_id=${_editComboId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') renderGalleryGrid(res.images);
    });
}

function galleryUploadFiles(files, comboId) {
    const statusEl = document.getElementById('edit_gallery_upload_status');
    const uploads  = Array.from(files);
    if (uploads.length === 0) return;

    statusEl.innerHTML = `<span class="combo-gallery-spinner"></span> Uploading ${uploads.length} image(s)...`;

    const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';

    Promise.all(uploads.map(file => {
        const fd = new FormData();
        fd.append('action', 'upload_image');
        fd.append('combo_id', comboId);
        fd.append('gallery_image', file);
        fd.append('csrf_token', csrfToken);
        return fetch(window.BASE_URL + 'admin/api/v1/combos.php', { method: 'POST', body: fd })
            .then(r => r.json());
    })).then(results => {
        const failed = results.filter(r => r.status !== 'success');
        if (failed.length > 0) {
            statusEl.innerHTML = `<span class="text-danger small">⚠ ${failed.length} upload(s) failed.</span>`;
        } else {
            statusEl.innerHTML = `<span class="text-success small">✓ ${uploads.length} image(s) uploaded.</span>`;
        }
        setTimeout(() => statusEl.innerHTML = '', 3000);
        reloadGallery();
    });
}

// Wire drop zone
document.addEventListener('DOMContentLoaded', function() {
    const zone  = document.getElementById('edit_gallery_drop_zone');
    const input = document.getElementById('edit_gallery_file_input');

    if (zone && input) {
        input.addEventListener('change', function() {
            if (this.files.length && _editComboId) {
                galleryUploadFiles(this.files, _editComboId);
                this.value = ''; // reset
            }
        });

        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            if (_editComboId && e.dataTransfer.files.length) {
                galleryUploadFiles(e.dataTransfer.files, _editComboId);
            }
        });
    }
});

/* ────────────────────────────────────────────────
   Open Edit Offcanvas
──────────────────────────────────────────────── */
function openEditCombo(data, comboId) {
    _editComboId = comboId;

    document.getElementById('edit_combo_id').value         = data.id;
    document.getElementById('edit_combo_name').value       = data.name;
    document.getElementById('edit_combo_desc').value       = data.description;
    document.getElementById('edit_combo_price').value      = data.price;
    document.getElementById('edit_combo_active').checked   = data.is_active == 1;

    const catSel = document.getElementById('edit_combo_category');
    if (catSel) catSel.value = data.category;

    // Clear product items
    const listEl = document.getElementById('edit_items_list');
    listEl.innerHTML = '';
    document.getElementById('edit_items_json').value = '[]';

    // Clear gallery
    document.getElementById('edit_gallery_grid').innerHTML = '<p class="text-muted small">Loading images...</p>';

    // Load items + gallery
    fetch(window.BASE_URL + `admin/api/v1/combos.php?action=get_items&combo_id=${data.id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.items) res.items.forEach(item => addProductRow('edit_items_list', 'edit_items_json', item, 'edit_price_summary', 'edit_combo_price'));
        renderGalleryGrid(res.gallery || []);
    })
    .catch(() => {
        document.getElementById('edit_gallery_grid').innerHTML = '<p class="text-danger small">Failed to load images.</p>';
    });

    const canvas = new bootstrap.Offcanvas(document.getElementById('editComboOffcanvas'));
    canvas.show();
}

/* ────────────────────────────────────────────────
   Delete Combo
──────────────────────────────────────────────── */
function deleteCombo(id, name) {
    if (!confirm(`Delete combo "${name}"? It will be hidden from the storefront.`)) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('combo_id', id);
    fd.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
    fetch(window.BASE_URL + 'admin/api/v1/combos.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') location.reload();
            else alert('Error: ' + res.message);
        });
}

/* ────────────────────────────────────────────────
   Status Toggle
──────────────────────────────────────────────── */
document.querySelectorAll('.combo-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const fd = new FormData();
        fd.append('action', 'toggle_status');
        fd.append('combo_id', this.dataset.id);
        fd.append('is_active', this.checked ? 1 : 0);
        fd.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
        fetch(window.BASE_URL + 'admin/api/v1/combos.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => { if (res.status !== 'success') this.checked = !this.checked; });
    });
});

/* ────────────────────────────────────────────────
   Form Submissions (AJAX)
──────────────────────────────────────────────── */
function handleComboForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    form.addEventListener('submit', async e => {
        e.preventDefault();
        const btn  = form.querySelector('button[type=submit]');
        const orig = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
        btn.disabled  = true;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const formData  = new FormData(form);
        if (csrfToken && !formData.has('csrf_token')) formData.append('csrf_token', csrfToken);

        try {
            const res  = await fetch(form.getAttribute('action'), {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
            });
            const text = await res.text();
            let data;
            try { data = JSON.parse(text); }
            catch(e) { throw new Error('Server response: ' + text.substring(0, 200)); }

            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
                btn.innerHTML = orig;
                btn.disabled  = false;
            }
        } catch(err) {
            alert(err.message || 'Network error. Please try again.');
            btn.innerHTML = orig;
            btn.disabled  = false;
        }
    });
}

/* ────────────────────────────────────────────────
   Stat Card Filtering
──────────────────────────────────────────────── */
window.filterCombos = function(filter) {
    document.querySelectorAll('.combo-row').forEach(row => {
        const isActive = row.dataset.active === '1';
        row.style.display = (filter === 'all' || (filter === 'active' && isActive)) ? '' : 'none';
    });
    document.querySelector('.products-table-wrapper').scrollIntoView({ behavior: 'smooth' });
};

function updateFileName(input) {
    const el = input.closest('.file-overlay-wrapper')?.querySelector('.input-group-file-text');
    if (el) el.textContent = input.files[0] ? input.files[0].name : 'Choose image...';
}

handleComboForm('addComboForm');
handleComboForm('editComboForm');
</script>

<?php require_once 'includes/footer.php'; ?>
