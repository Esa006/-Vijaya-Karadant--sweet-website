<?php
/**
 * Edit Offer Page
 */
$pageStyles = ['assets/css/admin/create-offer.css'];
require_once '../config/config.php';
require_once '../repositories/CouponRepository.php';

$couponId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$repo = new CouponRepository();

// Fetch offer early — redirect before any HTML is output if not found
$coupon = $repo->getById($couponId);
if (!$coupon) {
    $_SESSION['flash_error'] = 'Offer not found or has been deleted.';
    header('Location: coupons.php');
    exit;
}

// Handle POST request for updating BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input) {
        $updateData = [
            'description' => $input['name'],
            'type' => $input['type'],
            'value' => (float)$input['discount'],
            'min_cart_total' => (float)$input['minOrder'],
            'usage_limit' => (int)$input['usageLimit'],
            'limit_per_user' => (int)$input['perUserLimit'],
            'expires_at' => $input['endDate'] !== 'No expiry' ? date('Y-m-d H:i:s', strtotime($input['endDate'])) : null,
            'is_active' => $input['status'] === 'Active' ? 1 : 0
        ];
        
        $success = $repo->update($couponId, $updateData);
        echo json_encode(['success' => $success]);
        exit;
    }
}

// Proceed with HTML rendering for GET requests
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/sidebar.php'; 

// $coupon already loaded above

$categories = json_decode($coupon['applicable_categories'] ?? '[]', true);
if (empty($categories)) $categories = ['Festive Boxes', 'Gift Packs', 'Premium Mithai'];

?>

<div class="main-content">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 px-4 pb-5 create-offer-wrapper">
        
        <div class="create-offer-header sticky-top bg-white border-bottom shadow-sm mx-n4 px-4" style="z-index: 100;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb mb-0" style="font-size: 12px;">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Admin</a></li>
                            <li class="breadcrumb-item"><a href="coupons.php" class="text-decoration-none text-muted">Promotions</a></li>
                            <li class="breadcrumb-item active text-muted" aria-current="page">Edit Offer</li>
                        </ol>
                    </nav>
                    <h1 class="fs-4 fw-bold text-dark mb-0">Edit Offer</h1>
                </div>
                <div class="d-flex gap-2">
                    <a href="offer-details.php?id=<?= $couponId ?>" class="btn btn-light border px-4 py-2 small fw-bold">Cancel</a>
                    <button class="btn btn-dark px-4 py-2 small fw-bold btn-save-offer-publish" onclick="saveNewOffer()">Save Changes</button>
                </div>
            </div>
        </div>

        <div class="pt-3 mb-4">
            <a href="offer-details.php?id=<?= $couponId ?>" class="btn-nav-back">
                <i class="bi bi-reply-fill"></i> Back to Offer Details
            </a>
        </div>

        <div class="container-fluid px-0">
            <div class="row g-5">
                
                <div class="col-lg-7 col-xl-6">
                    
                    <h5 class="offer-section-title">Basic Offer Information</h5>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="offer-field-label">Offer Name</label>
                            <input type="text" class="offer-input-control" id="offerName" value="<?= htmlspecialchars($coupon['description'] ?: '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="offer-field-label">Coupon Code (Immutable)</label>
                            <input type="text" class="offer-input-control text-uppercase" id="couponCode" value="<?= htmlspecialchars($coupon['code']) ?>" readonly disabled>
                        </div>
                        <div class="col-12">
                            <label class="offer-field-label">Internal Description</label>
                            <textarea class="offer-input-control shadow-none" id="offerDescription" rows="3"><?= htmlspecialchars($coupon['description']) ?></textarea>
                        </div>
                    </div>

                    <h5 class="offer-section-title">Discount Details</h5>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="offer-field-label">Discount Type</label>
                            <select class="offer-input-control" id="discountType">
                                <option value="percentage" <?= $coupon['type'] == 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                <option value="fixed" <?= $coupon['type'] == 'fixed' ? 'selected' : '' ?>>Fixed Amount (₹)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="offer-field-label">Discount Value</label>
                            <input type="text" class="offer-input-control" id="discountValue" value="<?= htmlspecialchars($coupon['value']) ?>">
                        </div>
                    </div>

                    <h5 class="offer-section-title">Redemption Conditions</h5>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="offer-field-label">Min. Order Value (₹)</label>
                            <input type="number" class="offer-input-control" id="minOrderValue" value="<?= htmlspecialchars($coupon['min_cart_total']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="offer-field-label">Usage Limit Per User</label>
                            <input type="number" class="offer-input-control" id="perUserLimit" value="<?= htmlspecialchars($coupon['limit_per_user']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="offer-field-label">Total Pool Limit</label>
                            <input type="number" class="offer-input-control" id="totalUsageLimit" value="<?= htmlspecialchars($coupon['usage_limit']) ?>">
                        </div>
                    </div>

                    <h5 class="offer-section-title">Validity Window</h5>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="offer-field-label">Launch Date</label>
                            <input type="date" class="offer-input-control" id="startDate" value="<?= date('Y-m-d', strtotime($coupon['created_at'])) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="offer-field-label">Expiry Date</label>
                            <input type="date" class="offer-input-control" id="expiryDate" value="<?= $coupon['expires_at'] ? date('Y-m-d', strtotime($coupon['expires_at'])) : '' ?>">
                        </div>
                    </div>

                    <h5 class="offer-section-title">Publishing Status</h5>
                    <div class="bg-white border rounded-3 p-3 d-flex align-items-center justify-content-between mb-4 shadow-sm border-light">
                        <div>
                            <div class="fw-bold small text-dark">Active Status</div>
                            <div class="text-muted smaller">Toggle whether this offer is active or disabled.</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="activeOnPublish" <?= $coupon['is_active'] ? 'checked' : '' ?> style="width: 44px; height: 22px;">
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.saveNewOffer = function() {
        const name = document.getElementById('offerName').value.trim();
        if (!name) {
            alert('Offer name is required!');
            return;
        }

        const btn = document.querySelector('.btn-save-offer-publish');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        btn.disabled = true;

        const payload = {
            name: name,
            type: document.getElementById('discountType').value,
            discount: document.getElementById('discountValue').value,
            minOrder: document.getElementById('minOrderValue').value,
            usageLimit: document.getElementById('totalUsageLimit').value,
            perUserLimit: document.getElementById('perUserLimit').value,
            endDate: document.getElementById('expiryDate').value || 'No expiry',
            status: document.getElementById('activeOnPublish').checked ? 'Active' : 'Inactive'
        };

        fetch('edit-offer.php?id=<?= $couponId ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Offer updated successfully!');
                window.location.href = 'offer-details.php?id=<?= $couponId ?>';
            } else {
                alert('Failed to update offer.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            alert('Error updating offer');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    };
});
</script>

<?php require_once 'includes/footer.php'; ?>
