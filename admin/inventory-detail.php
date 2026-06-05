<?php
/**
 * Sweets Website
 * =============================================================
 * File: inventory-detail.php
 * Description: Inventory Product Detail – Stock History & Management
 * =============================================================
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once 'includes/auth.php';
require_once REPOS_PATH . '/ProductRepository.php';
require_once REPOS_PATH . '/InventoryRepository.php';
require_once ROOT_PATH . '/config/Database.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$productId) {
    $productRepo = new ProductRepository();
    $firstProduct = $productRepo->getAllProducts(1, 0); // Get just the first one
    if (!empty($firstProduct)) {
        $productId = $firstProduct[0]['id'];
    } else {
        header("Location: inventory.php");
        exit;
    }
}

$productRepo = new ProductRepository();
$inventoryRepo = new InventoryRepository();
$db = Database::getInstance();

$product = $productRepo->getProductById($productId);
if (!$product) {
    header("Location: inventory.php");
    exit;
}

$pageStyles = ['assets/css/admin/products.css', 'assets/css/admin/pages/admin-inventory.css', 'assets/css/admin/pages/admin-inventory-detail.css'];
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$inventory = $inventoryRepo->getByProductId($productId);
$currentStock = $inventory ? (int)$inventory['stock'] : 0;
$reservedStock = $inventory ? (int)$inventory['reserved_stock'] : 0;

try {
    $totalAdded30d = $inventoryRepo->getTotalAdded($productId, 30);
    $totalRemoved30d = $inventoryRepo->getTotalRemoved($productId, 30);
} catch (Exception $e) {
    $totalAdded30d = 0;
    $totalRemoved30d = 0;
}

if ($currentStock <= 0) {
    $statusIndicator = 'Out of Stock';
    $statusClass = 'products-status-out';
} elseif ($currentStock <= 10) {
    $statusIndicator = 'Low Stock';
    $statusClass = 'products-status-low';
} else {
    $statusIndicator = 'Healthy';
    $statusClass = 'products-status-in';
}

$perPage = 8;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

try {
    $totalItems = $inventoryRepo->countActivityHistory($productId);
    $totalPages = $totalItems > 0 ? ceil($totalItems / $perPage) : 1;
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;
    $activityHistory = $inventoryRepo->getActivityHistory($productId, $perPage, $offset);
} catch (Exception $e) {
    $totalItems = 0;
    $totalPages = 1;
    $offset = 0;
    $activityHistory = [];
    $stockActivityTableMissing = true;
}

$startItem = $totalItems > 0 ? $offset + 1 : 0;
$endItem = min($offset + $perPage, $totalItems);

function buildDetailPageUrl(int $newPage, int $productId): string {
    return '?id=' . $productId . '&page=' . $newPage;
}

function formatActivityDate(string $datetime): string {
    $ts = strtotime($datetime);
    $now = time();
    $today = strtotime('today');
    $yesterday = strtotime('yesterday');

    if ($ts >= $today) {
        return 'Today, ' . date('h:i A', $ts);
    } elseif ($ts >= $yesterday) {
        return 'Yesterday, ' . date('h:i A', $ts);
    } else {
        return date('M d, Y, h:i A', $ts);
    }
}

function getInitials(string $name): string {
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    }
    return strtoupper(substr($parts[0], 0, 2));
}

function formatQuantity(string $actionType, int $quantityChange, ?int $previousStock, ?int $newStock): array {
    if ($actionType === 'updated' && $newStock !== null) {
        return ['display' => 'Set to ' . $newStock, 'raw' => $newStock, 'color' => '#6c757d'];
    }
    if ($actionType === 'added') {
        return ['display' => '+' . $quantityChange, 'raw' => $quantityChange, 'color' => '#198754'];
    }
    return ['display' => '-' . abs($quantityChange), 'raw' => -$quantityChange, 'color' => '#dc3545'];
}
?>

<div class="main-content products-page">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 products-content-body">
        <div class="px-4 pt-4">
            <!-- Breadcrumb -->
            <div class="breadcrumb-custom">
                <a href="inventory.php">Inventory</a>
                <span class="separator"><i class="bi bi-chevron-right"></i></span>
                <span class="current">Stock Details</span>
            </div>

            <!-- Title Row -->
            <div class="title-row">
                <h1>Inventory Details</h1>
                <a href="edit-product.php?id=<?php echo $productId; ?>" class="btn-edit text-decoration-none">
                    <i class="bi bi-pencil"></i> Edit Product
                </a>
            </div>

            <!-- Product Card -->
            <div class="product-card">
                <?php
                $rawImg = $product['image_path'] ?? $product['image'] ?? '';
                $imgSrc = !empty($rawImg) ? BASE_URL . $rawImg : BASE_URL . 'assets/images/placeholders/product-placeholder.png';
                ?>
                <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img" onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>assets/images/placeholders/product-placeholder.png'">
                <div class="product-info">
                    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                    <div class="product-meta">
                        <span class="sku">#SKU: <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></span>
                        <span class="cat"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                    </div>
                    <span class="badge-instock"><?php echo $statusIndicator; ?></span>
                </div>
                <div class="product-actions">
                    <button class="btn-reduce-stock" data-bs-toggle="modal" data-bs-target="#reduceStockModal">
                        <span class="dash">—</span> Reduce Stock
                    </button>
                    <button class="btn-add-stock-btn" data-bs-toggle="modal" data-bs-target="#addStockModal">
                        <span class="plus">+</span> In Stock
                    </button>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Current Stock</div>
                        <div class="stat-value"><?php echo $currentStock; ?> units</div>
                    </div>
                    <div class="stat-icon box-icon">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Status Indicator</div>
                        <div class="stat-value"><?php echo $statusIndicator; ?></div>
                    </div>
                    <div class="stat-icon health-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Added (30d)</div>
                        <div class="stat-value"><?php echo $totalAdded30d; ?></div>
                    </div>
                    <div class="stat-icon up-icon">
                        <i class="bi bi-arrow-up-circle-fill"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div>
                        <div class="stat-label">Total Removed (30d)</div>
                        <div class="stat-value"><?php echo $totalRemoved30d; ?></div>
                    </div>
                    <div class="stat-icon down-icon">
                        <i class="bi bi-arrow-down-circle-fill"></i>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════
                 WEIGHT VARIANTS MANAGEMENT
            ══════════════════════════════════════════════════════ -->
            <?php
            $variantStmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = :pid ORDER BY id ASC");
            $variantStmt->execute([':pid' => $productId]);
            $variants = $variantStmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="overflow:hidden;">
                <div class="card-header d-flex justify-content-between align-items-center py-3 px-4" style="background:#fff; border-bottom:2px solid #f0ebe5;">
                    <div>
                        <h5 class="mb-0 fw-bold" style="color:#3d1c02;"><i class="bi bi-tags-fill me-2" style="color:#b5451b;"></i>Weight Variants</h5>
                        <p class="mb-0 text-muted small">Manage all size/weight options, prices, and stock levels for this product.</p>
                    </div>
                    <button class="btn btn-sm fw-bold text-white px-3 py-2 rounded-3" style="background:#b5451b;" data-bs-toggle="modal" data-bs-target="#addVariantModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Weight
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle" id="variantTable">
                            <thead style="background:#fdf6f0;">
                                <tr style="font-size:0.8rem; color:#888; text-transform:uppercase; letter-spacing:.04em;">
                                    <th class="px-4 py-3">Weight</th>
                                    <th class="py-3">Label</th>
                                    <th class="py-3">Price (₹)</th>
                                    <th class="py-3">Stock</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3 text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="variantTableBody">
                                <?php if (empty($variants)): ?>
                                <tr id="no-variants-row">
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox" style="font-size:2rem;opacity:.3;display:block;margin-bottom:8px;"></i>
                                        No weight variants yet. Click <strong>+ Add Weight</strong> to create one.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($variants as $v): ?>
                                <tr data-vid="<?php echo $v['id']; ?>">
                                    <td class="px-4 fw-bold" style="color:#3d1c02;">
                                        <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background:#fdf0e8;color:#b5451b;font-size:.85rem;">
                                            <?php echo htmlspecialchars($v['weight']); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small" data-label="Label">
                                        <span class="var-label-text"><?php echo htmlspecialchars($v['label']); ?></span>
                                    </td>
                                    <td class="fw-semibold" style="color:#2d6a4f;" data-label="Price (&#8377;)">
                                        &#8377;<span class="var-price-text"><?php echo number_format($v['price'], 2); ?></span>
                                    </td>
                                    <td data-label="Stock">
                                        <span class="var-stock-badge fw-bold <?php echo $v['stock'] <= 0 ? 'text-danger' : ($v['stock'] <= 10 ? 'text-warning' : 'text-success'); ?>">
                                            <?php echo $v['stock']; ?> units
                                        </span>
                                    </td>
                                    <td data-label="Status">
                                        <?php if ($v['stock'] <= 0): ?>
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Out of Stock</span>
                                        <?php elseif ($v['stock'] <= 10): ?>
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill">Low Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">In Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-secondary me-1 btn-edit-variant" data-vid="<?php echo $v['id']; ?>"
                                            data-weight="<?php echo htmlspecialchars($v['weight']); ?>"
                                            data-label="<?php echo htmlspecialchars($v['label']); ?>"
                                            data-price="<?php echo $v['price']; ?>"
                                            data-stock="<?php echo $v['stock']; ?>" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-delete-variant" data-vid="<?php echo $v['id']; ?>"
                                            data-weight="<?php echo htmlspecialchars($v['weight']); ?>" title="Delete">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── Add Variant Modal ── -->
            <div class="modal fade" id="addVariantModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold" style="color:#3d1c02;"><i class="bi bi-plus-circle-fill me-2" style="color:#b5451b;"></i>Add Weight Variant</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body pt-3">
                            <div id="addVariantError" class="alert alert-danger d-none small py-2"></div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Weight <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2 flex-wrap mb-2" id="quickWeightBtns">
                                    <?php
                                    $existingWeights = array_column($variants, 'weight');
                                    $quickWeights = ['250g','500g','1kg'];
                                    foreach ($quickWeights as $qw):
                                        $disabled = in_array($qw, $existingWeights) ? 'opacity-50 pe-none' : '';
                                    ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill quick-weight-btn <?php echo $disabled; ?>" data-weight="<?php echo $qw; ?>"><?php echo $qw; ?></button>
                                    <?php endforeach; ?>
                                </div>
                                <input type="text" id="av-weight" class="form-control" placeholder="e.g. 250g, 500g, 1kg" maxlength="20">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Label <span class="text-muted">(optional)</span></label>
                                <input type="text" id="av-label" class="form-control" placeholder="e.g. 250g Trial Pack">
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Price (₹) <span class="text-danger">*</span></label>
                                    <input type="number" id="av-price" class="form-control" placeholder="0.00" step="0.01" min="1">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Initial Stock</label>
                                    <input type="number" id="av-stock" class="form-control" placeholder="0" min="0" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn fw-bold text-white rounded-3 px-4" style="background:#b5451b;" id="btnSaveVariant">
                                <i class="bi bi-check-lg me-1"></i> Save Variant
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Edit Variant Modal ── -->
            <div class="modal fade" id="editVariantModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold" style="color:#3d1c02;"><i class="bi bi-pencil-fill me-2" style="color:#b5451b;"></i>Edit Variant — <span id="ev-weight-title"></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body pt-3">
                            <div id="editVariantError" class="alert alert-danger d-none small py-2"></div>
                            <input type="hidden" id="ev-id">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Weight</label>
                                <input type="text" id="ev-weight" class="form-control" maxlength="20">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Label</label>
                                <input type="text" id="ev-label" class="form-control">
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Price (₹)</label>
                                    <input type="number" id="ev-price" class="form-control" step="0.01" min="1">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">Stock</label>
                                    <input type="number" id="ev-stock" class="form-control" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn fw-bold text-white rounded-3 px-4" style="background:#b5451b;" id="btnUpdateVariant">
                                <i class="bi bi-check-lg me-1"></i> Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Activity History -->
            <h3 class="activity-title">Stock Activity History</h3>

            <!-- Desktop Table -->
            <div class="activity-table-wrap desktop-table mb-4">
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Action Type</th>
                            <th>Quantity Changed</th>
                            <th>Updated By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody class="products-table-body">
                        <?php if (!empty($activityHistory)): ?>
                            <?php foreach ($activityHistory as $row): ?>
                                <?php
                                $qtyInfo = formatQuantity($row['action_type'], (int)$row['quantity_change'], $row['previous_stock'] ?? null, $row['new_stock'] ?? null);
                                $actionLabels = ['added' => 'Added', 'reduced' => 'Reduced', 'updated' => 'Updated', 'reserved' => 'Reserved', 'released' => 'Released', 'finalized' => 'Finalized'];
                                $actionClass = strtolower($row['action_type']);
                                $actionLabel = $actionLabels[$row['action_type']] ?? ucfirst($row['action_type']);
                                $performedBy = $row['performed_by'] ?? 'System';
                                $initials = $row['performed_by_id'] ? getInitials($performedBy) : '';
                                ?>
                                <tr class="activity-row">
                                    <td class="date-cell"><?php echo formatActivityDate($row['created_at']); ?></td>
                                    <td><span class="action-badge <?php echo $actionClass; ?>"><?php echo $actionLabel; ?></span></td>
                                    <td><span class="qty-<?php echo $actionClass; ?>"><?php echo htmlspecialchars($qtyInfo['display']); ?></span></td>
                                    <td>
                                        <div class="user-cell">
                                            <?php if ($initials): ?>
                                                <span class="user-avatar avatar-ar"><?php echo $initials; ?></span>
                                            <?php else: ?>
                                                <span class="user-avatar avatar-sys"><i class="bi bi-gear-fill" style="font-size:0.7rem;color:#999;"></i></span>
                                            <?php endif; ?>
                                            <span class="user-name"><?php echo htmlspecialchars($performedBy); ?></span>
                                        </div>
                                    </td>
                                    <td class="notes-cell"><?php echo htmlspecialchars($row['notes'] ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No activity found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Activity Cards -->
            <div class="mobile-activity mb-4">
                <?php if (!empty($activityHistory)): ?>
                    <?php foreach ($activityHistory as $row): ?>
                        <?php
                        $qtyInfo = formatQuantity($row['action_type'], (int)$row['quantity_change'], $row['previous_stock'] ?? null, $row['new_stock'] ?? null);
                        $actionClass = strtolower($row['action_type']);
                        $actionLabel = $actionLabels[$row['action_type']] ?? ucfirst($row['action_type']);
                        $performedBy = $row['performed_by'] ?? 'System';
                        $initials = $row['performed_by_id'] ? getInitials($performedBy) : '';
                        ?>
                        <div class="mobile-card border-<?php echo $actionClass; ?>">
                            <table class="mobile-card-table">
                                <tr>
                                    <td class="mc-label">Date & Time</td>
                                    <td class="mc-value"><?php echo formatActivityDate($row['created_at']); ?></td>
                                </tr>
                                <tr>
                                    <td class="mc-label">Action Type</td>
                                    <td class="mc-value"><span class="action-badge <?php echo $actionClass; ?>"><?php echo $actionLabel; ?></span></td>
                                </tr>
                                <tr>
                                    <td class="mc-label">Quantity Changed</td>
                                    <td class="mc-value"><span class="qty-<?php echo $actionClass; ?>"><?php echo htmlspecialchars($qtyInfo['display']); ?></span></td>
                                </tr>
                                <tr>
                                    <td class="mc-label">Updated By</td>
                                    <td class="mc-value">
                                        <div class="user-cell">
                                            <?php if ($initials): ?>
                                                <span class="user-avatar avatar-ar" style="width:26px;height:26px;font-size:0.58rem;"><?php echo $initials; ?></span>
                                            <?php else: ?>
                                                <span class="user-avatar avatar-sys" style="width:26px;height:26px;"><i class="bi bi-gear-fill" style="font-size:0.6rem;color:#999;"></i></span>
                                            <?php endif; ?>
                                            <span class="user-name"><?php echo htmlspecialchars($performedBy); ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="mc-label">Notes</td>
                                    <td class="mc-value mc-notes-val"><?php echo htmlspecialchars($row['notes'] ?? '—'); ?></td>
                                </tr>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">No activity found.</div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-top border-light d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="small text-muted order-2 order-md-1">
                    Page <?php echo $page; ?> of <?php echo $totalPages; ?> &bull; <?php echo $startItem; ?>-<?php echo $endItem; ?> of <?php echo $totalItems; ?> entries
                </div>
                <nav class="order-1 order-md-2">
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($page <= 1): ?>
                            <li class="page-item disabled"><a class="page-link border-0 text-muted" href="#"><i class="bi bi-chevron-left"></i> Back</a></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link border-0 text-dark" href="<?php echo buildDetailPageUrl($page - 1, $productId); ?>"><i class="bi bi-chevron-left"></i> Back</a></li>
                        <?php endif; ?>

                        <?php
                        $range = 2;
                        $start = max(1, $page - $range);
                        $end = min($totalPages, $page + $range);
                        for ($i = $start; $i <= $end; $i++): ?>
                            <?php if ($i === $page): ?>
                                <li class="page-item active"><a class="page-link border-0 rounded-3 mx-1" href="#" style="background: #8c3333; color: #fff;"><?php echo $i; ?></a></li>
                            <?php else: ?>
                                <li class="page-item"><a class="page-link border-0 text-dark fw-bold ms-2" href="<?php echo buildDetailPageUrl($i, $productId); ?>"><?php echo $i; ?></a></li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page >= $totalPages): ?>
                            <li class="page-item disabled"><span class="page-link border-0 text-muted">Next <i class="bi bi-chevron-right"></i></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link border-0 text-dark fw-bold ms-2" href="<?php echo buildDetailPageUrl($page + 1, $productId); ?>">Next <i class="bi bi-chevron-right"></i></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 16px;">
            <div class="modal-header border-0 pt-4 pb-2 px-4">
                <h5 class="fw-bold mb-0">Add Stock</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <form id="addStockForm">
                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Quantity to Add</label>
                        <input type="number" name="quantity" class="form-control form-control-custom shadow-none bg-white fw-bold" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Notes</label>
                        <textarea name="notes" class="form-control form-control-custom shadow-none bg-white" rows="2" placeholder="Reason for stock addition..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn fw-bold rounded-2 shadow-none px-4" style="background-color: #dcdfe3; color: #4b5563;" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn fw-bold rounded-2 px-4 shadow-none" id="submitAddStock" style="background: linear-gradient(135deg, #8c3333, #b85c00); color: #fff;">Add Stock</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reduceStockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 16px;">
            <div class="modal-header border-0 pt-4 pb-2 px-4">
                <h5 class="fw-bold mb-0">Reduce Stock</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <form id="reduceStockForm">
                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Quantity to Reduce</label>
                        <input type="number" name="quantity" class="form-control form-control-custom shadow-none bg-white fw-bold" min="1" max="<?php echo $currentStock; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Notes</label>
                        <textarea name="notes" class="form-control form-control-custom shadow-none bg-white" rows="2" placeholder="Reason for stock reduction..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn fw-bold rounded-2 shadow-none px-4" style="background-color: #dcdfe3; color: #4b5563;" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn fw-bold rounded-2 px-4 shadow-none" id="submitReduceStock" style="color: #8c3333; border: 1.5px solid #8c3333;">Reduce Stock</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const productId = <?php echo $productId; ?>;

    document.getElementById('submitAddStock').addEventListener('click', function () {
        const form = document.getElementById('addStockForm');
        const formData = new FormData(form);
        formData.append('action', 'add');

        fetch('api/v1/inventory.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to update stock');
            }
        })
        .catch(() => alert('An error occurred'));
    });

    document.getElementById('submitReduceStock').addEventListener('click', function () {
        const form = document.getElementById('reduceStockForm');
        const formData = new FormData(form);
        formData.append('action', 'reduce');

        fetch('api/v1/inventory.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to update stock');
            }
        })
        .catch(() => alert('An error occurred'));
    });
});
</script>

<script>
/* ═══════════════════════════════════════════════════════════
   Weight Variants CRUD JavaScript
═══════════════════════════════════════════════════════════ */
(function () {
    const PRODUCT_ID = <?php echo $productId; ?>;
    const API_URL    = '<?php echo BASE_URL; ?>api/v1/product_variants.php';

    // Quick weight shortcut buttons
    document.querySelectorAll('.quick-weight-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('av-weight').value = btn.dataset.weight;
            document.querySelectorAll('.quick-weight-btn').forEach(b => b.classList.remove('btn-secondary', 'text-white'));
            btn.classList.add('btn-secondary', 'text-white');
        });
    });

    // ── ADD VARIANT ─────────────────────────────────────────
    document.getElementById('btnSaveVariant')?.addEventListener('click', function () {
        const errEl  = document.getElementById('addVariantError');
        const weight = document.getElementById('av-weight').value.trim();
        const label  = document.getElementById('av-label').value.trim();
        const price  = parseFloat(document.getElementById('av-price').value);
        const stock  = parseInt(document.getElementById('av-stock').value) || 0;

        errEl.classList.add('d-none');

        if (!weight || !price || price <= 0) {
            errEl.textContent = 'Weight and a valid Price are required.';
            errEl.classList.remove('d-none');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        const fd = new FormData();
        fd.append('product_id', PRODUCT_ID);
        fd.append('weight',     weight);
        fd.append('label',      label || weight + ' Pack');
        fd.append('price',      price);
        fd.append('stock',      stock);

        fetch(API_URL, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.status !== 'success') {
                    errEl.textContent = data.message;
                    errEl.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Variant';
                    return;
                }
                // Inject new row into table
                const tbody = document.getElementById('variantTableBody');
                const noRow = document.getElementById('no-variants-row');
                if (noRow) noRow.remove();

                const stockClass = stock <= 0 ? 'text-danger' : (stock <= 10 ? 'text-warning' : 'text-success');
                const statusBadge = stock <= 0
                    ? '<span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Out of Stock</span>'
                    : (stock <= 10
                        ? '<span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill">Low Stock</span>'
                        : '<span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">In Stock</span>');

                tbody.insertAdjacentHTML('beforeend', `
                <tr data-vid="${data.id}">
                    <td class="px-4 fw-bold" style="color:#3d1c02;">
                        <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background:#fdf0e8;color:#b5451b;font-size:.85rem;">${weight}</span>
                    </td>
                    <td class="text-muted small" data-label="Label"><span class="var-label-text">${label || weight + ' Pack'}</span></td>
                    <td class="fw-semibold" style="color:#2d6a4f;" data-label="Price (₹)">&#8377;<span class="var-price-text">${parseFloat(price).toFixed(2)}</span></td>
                    <td data-label="Stock"><span class="var-stock-badge fw-bold ${stockClass}">${stock} units</span></td>
                    <td data-label="Status">${statusBadge}</td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-secondary me-1 btn-edit-variant"
                            data-vid="${data.id}" data-weight="${weight}" data-label="${label || weight + ' Pack'}"
                            data-price="${price}" data-stock="${stock}" title="Edit">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-delete-variant"
                            data-vid="${data.id}" data-weight="${weight}" title="Delete">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </td>
                </tr>`);

                bindRowActions();
                bootstrap.Modal.getInstance(document.getElementById('addVariantModal')).hide();
                // Reset form
                ['av-weight','av-label','av-price','av-stock'].forEach(id => document.getElementById(id).value = id === 'av-stock' ? '0' : '');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Variant';

                Swal.fire({ icon: 'success', title: 'Variant Added!', text: `${weight} variant saved successfully.`, timer: 2000, showConfirmButton: false });
            })
            .catch(() => {
                errEl.textContent = 'Server error. Please try again.';
                errEl.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Save Variant';
            });
    });

    // ── EDIT / DELETE BINDINGS ───────────────────────────────
    function bindRowActions() {
        // Edit
        document.querySelectorAll('.btn-edit-variant').forEach(btn => {
            btn.replaceWith(btn.cloneNode(true)); // Remove old listeners
        });
        document.querySelectorAll('.btn-edit-variant').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('ev-id').value         = this.dataset.vid;
                document.getElementById('ev-weight').value     = this.dataset.weight;
                document.getElementById('ev-label').value      = this.dataset.label;
                document.getElementById('ev-price').value      = this.dataset.price;
                document.getElementById('ev-stock').value      = this.dataset.stock;
                document.getElementById('ev-weight-title').textContent = this.dataset.weight;
                document.getElementById('editVariantError').classList.add('d-none');
                new bootstrap.Modal(document.getElementById('editVariantModal')).show();
            });
        });

        // Delete
        document.querySelectorAll('.btn-delete-variant').forEach(btn => {
            btn.replaceWith(btn.cloneNode(true));
        });
        document.querySelectorAll('.btn-delete-variant').forEach(btn => {
            btn.addEventListener('click', function () {
                const vid    = this.dataset.vid;
                const weight = this.dataset.weight;
                const row    = this.closest('tr');

                Swal.fire({
                    title: `Delete "${weight}" variant?`,
                    text: 'This will permanently remove this weight option. Orders referencing it will not be affected.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it'
                }).then(result => {
                    if (!result.isConfirmed) return;
                    fetch(API_URL, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id=${vid}`
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success') {
                            row.style.transition = 'opacity .3s';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                                if (!document.querySelector('#variantTableBody tr')) {
                                    document.getElementById('variantTableBody').innerHTML =
                                        '<tr id="no-variants-row"><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox" style="font-size:2rem;opacity:.3;display:block;margin-bottom:8px;"></i>No weight variants yet.</td></tr>';
                                }
                            }, 300);
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                });
            });
        });
    }

    // Initial bind
    bindRowActions();

    // ── UPDATE VARIANT ───────────────────────────────────────
    document.getElementById('btnUpdateVariant')?.addEventListener('click', function () {
        const errEl  = document.getElementById('editVariantError');
        const id     = document.getElementById('ev-id').value;
        const weight = document.getElementById('ev-weight').value.trim();
        const label  = document.getElementById('ev-label').value.trim();
        const price  = parseFloat(document.getElementById('ev-price').value);
        const stock  = parseInt(document.getElementById('ev-stock').value) || 0;

        errEl.classList.add('d-none');
        if (!weight || !price || price <= 0) {
            errEl.textContent = 'Weight and a valid Price are required.';
            errEl.classList.remove('d-none');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';

        fetch(API_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&weight=${encodeURIComponent(weight)}&label=${encodeURIComponent(label)}&price=${price}&stock=${stock}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.status !== 'success') {
                errEl.textContent = data.message;
                errEl.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Update';
                return;
            }
            // Update row in-place
            const row = document.querySelector(`tr[data-vid="${id}"]`);
            if (row) {
                row.querySelector('.badge').textContent = weight;
                row.querySelector('.var-label-text').textContent = label;
                row.querySelector('.var-price-text').textContent = parseFloat(price).toFixed(2);
                const stockBadge = row.querySelector('.var-stock-badge');
                stockBadge.textContent = stock + ' units';
                stockBadge.className = 'var-stock-badge fw-bold ' + (stock <= 0 ? 'text-danger' : (stock <= 10 ? 'text-warning' : 'text-success'));

                // Update action button data attributes
                const editBtn = row.querySelector('.btn-edit-variant');
                if (editBtn) {
                    editBtn.dataset.weight = weight;
                    editBtn.dataset.label  = label;
                    editBtn.dataset.price  = price;
                    editBtn.dataset.stock  = stock;
                }
                const delBtn = row.querySelector('.btn-delete-variant');
                if (delBtn) delBtn.dataset.weight = weight;
            }

            bootstrap.Modal.getInstance(document.getElementById('editVariantModal')).hide();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Update';

            Swal.fire({ icon: 'success', title: 'Updated!', text: `${weight} variant updated.`, timer: 1800, showConfirmButton: false });
        })
        .catch(() => {
            errEl.textContent = 'Server error.';
            errEl.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Update';
        });
    });

})();
</script>

<?php require_once 'includes/footer.php'; ?>
