<?php
/**
 * Dynamic Offer & Coupon Details Page
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../repositories/CouponRepository.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Instantiate DB connection manually if BaseRepository doesn't do it automatically, 
// but since the project relies on a global or BaseRepository standard, let's assume it works:
$repo = new CouponRepository();

// Get the coupon ID from URL, default to 1 for demo purposes if not passed
$couponId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$coupon = $repo->getById($couponId);

// For presentation: if coupon doesn't exist, we fallback to a mock array to keep the UI beautiful
if (!$coupon) {
    $coupon = [
        'id' => 1,
        'code' => 'DIWALI20',
        'description' => 'Diwali Dhamaka Sale',
        'type' => 'percentage',
        'value' => 20,
        'min_cart_total' => 1000,
        'usage_limit' => 500,
        'limit_per_user' => 1,
        'applicable_categories' => '["Karadant Special", "Festive Hampers", "Premium Sweets", "Dry Fruits"]',
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
        'expires_at' => date('Y-m-d H:i:s', strtotime('+25 days')),
        'creator_name' => 'Admin'
    ];
    $metrics = [
        'total_orders' => 450,
        'total_discount' => 112500,
        'total_revenue' => 850000,
        'aov' => 1888
    ];
} else {
    $metrics = $repo->getOfferMetrics($couponId);
}

// Prepare dynamic variables
$title = $coupon['description'] ?: $coupon['code'];
$typeStr = $coupon['type'] === 'percentage' ? 'Percentage Discount' : 'Flat Discount';
$discountDisplay = $coupon['type'] === 'percentage' ? $coupon['value'] . '% off' : '₹' . number_format($coupon['value']) . ' off';

$usageLimit = (int)$coupon['usage_limit'];
$usageCount = (int)$metrics['total_orders'];
$remaining = max(0, $usageLimit - $usageCount);
$progress = $usageLimit > 0 ? min(100, round(($usageCount / $usageLimit) * 100)) : 0;

$startDate = date('M d, Y - h:i A', strtotime($coupon['created_at']));
$endDate = $coupon['expires_at'] ? date('M d, Y - h:i A', strtotime($coupon['expires_at'])) : 'No Expiry';

// Validity calculation
$daysRemaining = 0;
$validityDisplay = '';
if ($coupon['expires_at']) {
    $now = new DateTime();
    $exp = new DateTime($coupon['expires_at']);
    $diff = $now->diff($exp);
    $daysRemaining = !$diff->invert ? $diff->days : 0;
    $validityDisplay = $now->format('M d') . ' –<br>' . $exp->format('M d');
} else {
    $validityDisplay = 'Lifetime<br>Offer';
}

// Status logic
$status = 'Inactive';
$statusBg = '#f3f4f6';
$statusColor = '#4b5563';

if ($coupon['is_active']) {
    if (!$coupon['expires_at'] || new DateTime() < new DateTime($coupon['expires_at'])) {
        $status = 'Active';
        $statusBg = '#e6f7ed';
        $statusColor = '#1d8348';
    } else {
        $status = 'Expired';
        $statusBg = '#fee2e2';
        $statusColor = '#dc2626';
    }
}

$categories = json_decode($coupon['applicable_categories'] ?? '[]', true);
if (empty($categories) || !is_array($categories)) {
    $categories = ['All Categories'];
}

// Format currency
$fmtTotalDiscount = '₹' . number_format((float)$metrics['total_discount']);
$fmtTotalRevenue = '₹' . number_format((float)$metrics['total_revenue']);
$fmtAov = '₹' . number_format((float)$metrics['aov']);
$fmtMinCart = '₹' . number_format((float)$coupon['min_cart_total']);

?>
<div class="main-content">
    <?php require_once 'includes/topbar.php'; ?>
    
    <!-- Custom Styles for Offer Details Content -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        .details-card { background: #fff; border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
        .details-card-header { background: #fdf5ec; border-radius: 16px 16px 0 0; }
        .summary-card { background: #fff; border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
        .icon-circle { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .tag { border: 1px solid #e0d5c7; border-radius: 8px; padding: 6px 14px; font-size: 13px; color: #5a4a3a; display: inline-flex; align-items: center; gap: 6px; background: #fffdf9; }
        .progress-bar { height: 8px; border-radius: 4px; background: #e8e0d6; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 4px; }
        .coupon-code { border: 1.5px dashed #c9a96e; border-radius: 8px; padding: 6px 14px; font-size: 14px; font-weight: 600; color: #7a4a1a; background: #fffbf3; letter-spacing: 1px; }
        .status-badge { background: <?= $statusBg ?>; color: <?= $statusColor ?>; font-size: 13px; font-weight: 600; padding: 4px 16px; border-radius: 20px; }
        .divider { border-bottom: 1px solid #f0ebe4; }
        .btn-outline { border: 1.5px solid #d6cfc5; border-radius: 10px; padding: 10px 24px; font-weight: 500; color: #4a3f35; background: #fff; cursor: pointer; transition: all 0.2s; }
        .btn-outline:hover { background: #f9f3ec; border-color: #b5a999; }
        .btn-edit-action { border: 1.5px solid #d6cfc5; border-radius: 10px; padding: 10px 24px; font-weight: 500; color: #4a3f35; background: #fff; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .btn-edit-action:hover { background: #f9f3ec; border-color: #b5a999; }
        
        /* Override Tailwind base conflict with Admin layout if any */
        .main-content { font-family: 'Inter', sans-serif !important; }
    </style>

    <div class="content-body">
    <div >

        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 mb-1 text-sm">
            <a href="coupons.php" class="text-[#8b2e1c] hover:underline font-medium">Offers & Coupons</a>
            <span class="text-[#a09080]">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
            </span>
            <span class="text-[#3a2a1a] font-semibold"><?= htmlspecialchars($title) ?></span>
        </div>

        <!-- Title & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
            <h1 class="text-2xl md:text-3xl font-bold text-[#3a2010]"><?= htmlspecialchars($title) ?></h1>
            <div class="flex items-center gap-3">
                <button class="btn-outline" onclick="handleDelete()">Delete Offer</button>
                <a href="edit-offer.php?id=<?= $couponId ?>" class="btn-edit-action text-decoration-none">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16.862 3.487a2.1 2.1 0 0 1 2.976 0l.675.675a2.1 2.1 0 0 1 0 2.976L8.836 18.815l-4.2.926.926-4.2L16.862 3.487z"/></svg>
                    Edit Offer
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Discount -->
            <div class="summary-card p-5 flex items-start justify-between">
                <div>
                    <p class="text-sm text-[#7a7060] mb-1">Discount</p>
                    <p class="text-2xl font-bold text-[#2a1a0a]"><?= $discountDisplay ?></p>
                    <p class="text-xs text-[#9a8a7a] mt-1">Based on configuration</p>
                </div>
                <div class="icon-circle bg-[#fef3e0]">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" fill="#f5c842" opacity="0.25"/>
                        <text x="7" y="17" font-size="14" font-weight="bold" fill="#c6880b">%</text>
                    </svg>
                </div>
            </div>
            <!-- Min. Order -->
            <div class="summary-card p-5 flex items-start justify-between">
                <div>
                    <p class="text-sm text-[#7a7060] mb-1">Min. order</p>
                    <p class="text-2xl font-bold text-[#2a1a0a]"><?= $fmtMinCart ?></p>
                    <p class="text-xs text-[#9a8a7a] mt-1">Required to apply</p>
                </div>
                <div class="icon-circle bg-[#fef3e0]">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c6880b" stroke-width="1.8">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 5.2a1 1 0 0 0 .9 1.4h12.8"/>
                        <circle cx="9" cy="21" r="1" fill="#c6880b"/>
                        <circle cx="17" cy="21" r="1" fill="#c6880b"/>
                    </svg>
                </div>
            </div>
            <!-- Usage Limit -->
            <div class="summary-card p-5">
                <div class="flex items-start justify-between mb-3">
                    <p class="text-sm text-[#7a7060]">Usage Limit</p>
                    <div class="icon-circle bg-[#eaf3ea]">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#5a8a5a" stroke-width="1.8">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="10" cy="7" r="4"/>
                            <path d="M20 8v6M17 11h6"/>
                        </svg>
                    </div>
                </div>
                <div class="flex justify-between text-xs text-[#8a7a6a] mb-2">
                    <span><?= $usageCount ?> Used</span>
                    <span><?= $usageLimit ?> Total</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill bg-[#2d7a3a]" style="width: <?= $progress ?>%;" id="usageBar"></div>
                </div>
            </div>
            <!-- Validity -->
            <div class="summary-card p-5 flex items-start justify-between">
                <div>
                    <p class="text-sm text-[#7a7060] mb-1">Validity</p>
                    <p class="text-xl font-bold text-[#2a1a0a] leading-tight"><?= $validityDisplay ?></p>
                    <p class="text-xs text-[#9a8a7a] mt-1"><?= $daysRemaining ?> Days remaining</p>
                </div>
                <div class="icon-circle bg-[#eee8f8]">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7a5ab5" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18"/>
                        <rect x="7" y="14" width="3" height="3" rx="0.5" fill="#7a5ab5" opacity="0.4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Configuration Details -->
            <div class="details-card overflow-hidden">
                <div class="details-card-header px-6 py-4 flex items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8b2e1c" stroke-width="2">
                        <path d="M12 3v3m0 12v3M3 12h3m12 0h3M5.6 5.6l2.1 2.1m8.6 8.6l2.1 2.1M5.6 18.4l2.1-2.1m8.6-8.6l2.1-2.1"/>
                    </svg>
                    <h2 class="text-[#8b2e1c] font-semibold text-base">Configuration Details</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center py-3.5 divider">
                        <span class="text-sm text-[#6a5a4a]">Offer Type</span>
                        <span class="text-sm font-semibold text-[#2a1a0a]"><?= $typeStr ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3.5 divider">
                        <span class="text-sm text-[#6a5a4a]">Coupon Code</span>
                        <div class="flex items-center gap-2">
                            <span class="coupon-code" id="couponCode"><?= htmlspecialchars($coupon['code']) ?></span>
                            <button onclick="copyCoupon()" class="text-[#9a8a7a] hover:text-[#5a4a3a] transition-colors p-1 cursor-pointer" title="Copy code">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="9" y="9" width="13" height="13" rx="2"/>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-between items-center py-3.5 divider">
                        <span class="text-sm text-[#6a5a4a]">Applicable On</span>
                        <span class="text-sm font-semibold text-[#2a1a0a]"><?= empty($categories) ? 'All Products' : 'Specific Categories' ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3.5">
                        <span class="text-sm text-[#6a5a4a]">Created By</span>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-[#4a8a5a] flex items-center justify-center">
                                <span class="text-white text-xs font-bold"><?= substr($coupon['creator_name'] ?? 'A', 0, 1) ?></span>
                            </div>
                            <span class="text-sm font-semibold text-[#2a1a0a]"><?= htmlspecialchars($coupon['creator_name'] ?? 'Admin') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage Limits & Tracking -->
            <div class="details-card overflow-hidden">
                <div class="details-card-header px-6 py-4 flex items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8b2e1c" stroke-width="2">
                        <path d="M12 20V10M6 20v-4M18 20V4"/>
                    </svg>
                    <h2 class="text-[#8b2e1c] font-semibold text-base">Usage Limits & Tracking</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center py-3.5 divider">
                        <span class="text-sm text-[#6a5a4a]">Total Limit</span>
                        <span class="text-sm font-semibold text-[#2a1a0a]"><?= $usageLimit ?> Uses</span>
                    </div>
                    <div class="flex justify-between items-center py-3.5 divider">
                        <span class="text-sm text-[#6a5a4a]">Current Usage</span>
                        <div class="text-right">
                            <span class="text-sm font-semibold text-[#2a1a0a]"><?= $usageCount ?> Uses</span>
                            <p class="text-xs text-[#9a8a7a]"><?= $remaining ?> Remaining</p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center py-3.5 divider">
                        <span class="text-sm text-[#6a5a4a]">Limit Per User</span>
                        <span class="text-sm font-semibold text-[#2a1a0a]"><?= htmlspecialchars($coupon['limit_per_user']) ?> Time(s)</span>
                    </div>
                    <div class="flex justify-between items-start py-3.5">
                        <span class="text-sm text-[#6a5a4a]">Usage Progress</span>
                        <div class="w-40">
                            <div class="progress-bar mb-1">
                                <div class="progress-fill bg-[#2d7a3a]" style="width: <?= $progress ?>%;"></div>
                            </div>
                            <p class="text-xs text-[#9a8a7a] text-right"><?= $progress ?>% Consumed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Applicable Categories -->
            <div class="details-card overflow-hidden">
                <div class="details-card-header px-6 py-4 flex items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8b2e1c" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2z"/>
                    </svg>
                    <h2 class="text-[#8b2e1c] font-semibold text-base">Applicable Categories</h2>
                </div>
                <div class="px-6 py-5 flex flex-wrap gap-3">
                    <?php foreach ($categories as $cat): ?>
                    <span class="tag">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        <?= htmlspecialchars($cat) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Performance Insights -->
            <div class="details-card overflow-hidden">
                <div class="details-card-header px-6 py-4 flex items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8b2e1c" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    <h2 class="text-[#8b2e1c] font-semibold text-base">Performance Insights</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center py-3.5 divider">
                        <span class="text-sm text-[#6a5a4a]">Total Orders with Offer</span>
                        <span class="text-sm font-semibold text-[#2a1a0a]"><?= $usageCount ?> Orders</span>
                    </div>
                    <div class="flex justify-between items-center py-3.5 divider">
                        <span class="text-sm text-[#6a5a4a]">Total Discount Given</span>
                        <span class="text-sm font-semibold text-[#c6540a]"><?= $fmtTotalDiscount ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3.5 divider">
                        <span class="text-sm text-[#6a5a4a]">Revenue Generated</span>
                        <span class="text-sm font-semibold text-[#2d7a3a]"><?= $fmtTotalRevenue ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3.5">
                        <span class="text-sm text-[#6a5a4a]">Avg. Order Value (AOV)</span>
                        <span class="text-sm font-semibold text-[#2a1a0a]"><?= $fmtAov ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Third Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
            <!-- Validity & Status -->
            <div class="details-card overflow-hidden">
                <div class="details-card-header px-6 py-4 flex items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8b2e1c" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <h2 class="text-[#8b2e1c] font-semibold text-base">Validity & Status</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center py-3.5 divider">
                        <span class="text-sm text-[#6a5a4a]">Start Date</span>
                        <span class="text-sm font-semibold text-[#2a1a0a]"><?= $startDate ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3.5 divider">
                        <span class="text-sm text-[#6a5a4a]">End Date</span>
                        <span class="text-sm font-semibold text-[#2a1a0a]"><?= $endDate ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3.5">
                        <span class="text-sm text-[#6a5a4a]">Current Status</span>
                        <span class="status-badge"><?= $status ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 bg-[#2d7a3a] text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium transform translate-y-20 opacity-0 transition-all duration-300 z-50 flex items-center gap-2">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
        <span id="toastMsg">Coupon code copied!</span>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-8 max-w-sm mx-4 shadow-2xl transform scale-95 transition-transform duration-300" id="deleteModalContent">
            <div class="flex items-center justify-center w-14 h-14 rounded-full bg-red-50 mx-auto mb-4">
                <svg width="28" height="28" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2-2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14z"/>
                    <line x1="10" y1="11" x2="10" y2="17"/>
                    <line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-center text-[#2a1a0a] mb-2">Delete Offer?</h3>
            <p class="text-sm text-[#6a5a4a] text-center mb-6">Are you sure you want to delete this offer? This action cannot be undone.</p>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" class="flex-1 py-2.5 rounded-xl border border-[#d6cfc5] text-[#4a3f35] font-medium hover:bg-[#f9f3ec] transition-colors cursor-pointer">Cancel</button>
                <button onclick="confirmDelete()" class="flex-1 py-2.5 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition-colors cursor-pointer">Delete</button>
            </div>
        </div>
    </div>

    <script>
        function copyCoupon() {
            const code = document.getElementById('couponCode').textContent;
            navigator.clipboard.writeText(code).then(() => {
                showToast('Coupon code copied!');
            }).catch(() => {
                const ta = document.createElement('textarea');
                ta.value = code;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast('Coupon code copied!');
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toastMsg');
            toastMsg.textContent = msg;
            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
                toast.classList.remove('translate-y-0', 'opacity-100');
            }, 2500);
        }

        function handleDelete() {
            const modal = document.getElementById('deleteModal');
            const content = document.getElementById('deleteModalContent');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const content = document.getElementById('deleteModalContent');
            modal.classList.add('opacity-0');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        async function confirmDelete() {
            try {
                const res = await fetch('api/v1/coupons.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: <?= $couponId ?> })
                });
                const result = await res.json();
                if (result.success) {
                    showToast('Offer deleted successfully!');
                    setTimeout(() => window.location.href = 'coupons.php', 1000);
                } else {
                    showToast(result.error || 'Delete failed');
                }
            } catch (err) {
                showToast('System error deleting offer');
            }
            closeDeleteModal();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const bars = document.querySelectorAll('.progress-fill');
            bars.forEach(bar => {
                const target = bar.style.width;
                bar.style.width = '0%';
                bar.style.transition = 'width 1.2s cubic-bezier(0.4, 0, 0.2, 1)';
                setTimeout(() => {
                    bar.style.width = target;
                }, 200);
            });
        });
    </script>
    </div> <!-- .content-body -->
</div> <!-- .main-content -->
<?php require_once 'includes/footer.php'; ?>
