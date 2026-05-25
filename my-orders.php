<?php
/**
 * Sweets Website
 * =============================================================
 * File: my-orders.php
 * Description: My Orders Page
 * =============================================================
 */
require_once 'config/config.php';
require_once SERVICES_PATH . '/CustomerService.php';
require_once SERVICES_PATH . '/OrderService.php';

$customerService = new CustomerService();
$orderService = new OrderService();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=my-orders.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$profileData = $customerService->getProfileData($userId);
$user = $profileData['profile'] ?? [];

$validStatuses = ['all', 'processing', 'shipped', 'delivered', 'cancelled', 'pending'];
$validTimeRanges = ['last_6_months', 'last_3_months', 'last_1_month', 'all_time'];

$activeStatus = strtolower(trim((string)($_GET['status'] ?? 'all')));
if (!in_array($activeStatus, $validStatuses, true)) {
    $activeStatus = 'all';
}

$timeRange = strtolower(trim((string)($_GET['time_range'] ?? 'last_6_months')));
if (!in_array($timeRange, $validTimeRanges, true)) {
    $timeRange = 'last_6_months';
}

$searchQuery = trim((string)($_GET['q'] ?? ''));
if (strlen($searchQuery) > 120) {
    $searchQuery = substr($searchQuery, 0, 120);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel_order') {
    $token = (string)($_POST['csrf_token'] ?? '');
    $isValidToken = !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);

    $redirectStatus = strtolower(trim((string)($_POST['status'] ?? $activeStatus)));
    if (!in_array($redirectStatus, $validStatuses, true)) {
        $redirectStatus = 'all';
    }

    $redirectTime = strtolower(trim((string)($_POST['time_range'] ?? $timeRange)));
    if (!in_array($redirectTime, $validTimeRanges, true)) {
        $redirectTime = 'last_6_months';
    }

    $redirectSearch = trim((string)($_POST['q'] ?? $searchQuery));
    if (strlen($redirectSearch) > 120) {
        $redirectSearch = substr($redirectSearch, 0, 120);
    }

    if (!$isValidToken) {
        $_SESSION['orders_flash'] = ['type' => 'danger', 'message' => 'Security check failed. Please try again.'];
    } else {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $cancelResult = $orderService->cancelCustomerOrder($userId, $orderId);
        $_SESSION['orders_flash'] = [
            'type' => !empty($cancelResult['success']) ? 'success' : 'danger',
            'message' => (string)($cancelResult['message'] ?? 'Unable to process request.')
        ];
    }

    $redirectParams = [
        'status' => $redirectStatus,
        'time_range' => $redirectTime
    ];
    if ($redirectSearch !== '') {
        $redirectParams['q'] = $redirectSearch;
    }

    header('Location: my-orders.php?' . http_build_query($redirectParams));
    exit;
}

$orders = $orderService->getCustomerOrders($userId, [
    'status' => $activeStatus,
    'search' => $searchQuery,
    'time_range' => $timeRange,
    'limit' => 100
]);

$orderCounts = $orderService->getCustomerOrderCounts($userId, $timeRange);
$orderCounts['processing'] = (int)($orderCounts['processing'] ?? 0) + (int)($orderCounts['paid'] ?? 0);

$flash = $_SESSION['orders_flash'] ?? null;
unset($_SESSION['orders_flash']);

$tabLabels = [
    'all' => 'All Orders',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled'
];

$timeRangeLabels = [
    'last_6_months' => 'Last 6 Months',
    'last_3_months' => 'Last 3 Months',
    'last_1_month' => 'Last 1 Month',
    'all_time' => 'All Time'
];

$buildOrdersUrl = static function (array $overrides = []) use ($activeStatus, $timeRange, $searchQuery): string {
    $params = [
        'status' => $activeStatus,
        'time_range' => $timeRange
    ];

    if ($searchQuery !== '') {
        $params['q'] = $searchQuery;
    }

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
            continue;
        }
        $params[$key] = $value;
    }

    return 'my-orders.php?' . http_build_query($params);
};

$userName = (string)($user['name'] ?? $user['user_full_name'] ?? 'Customer');
$userEmail = (string)($user['email'] ?? '');
$avatarUrl = (string)($user['avatar_url'] ?? 'assets/images/profile/avatar.png');

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/my-orders.css?v=<?php echo SITE_VERSION; ?>">

<main class="mo-page">
    <div class="container py-4">

        <nav class="mo-breadcrumb" aria-label="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <a href="profile.php">My Profile</a>
            <span>/</span>
            <span>My Orders</span>
        </nav>

        <h1 class="mo-page-title">My Orders</h1>
        <p class="mo-page-sub">Track and manage your orders in one place</p>

        <?php if (!empty($flash['message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars((string)($flash['type'] ?? 'info')); ?> mb-3" role="alert">
                <?php echo htmlspecialchars((string)$flash['message']); ?>
            </div>
        <?php endif; ?>

        <div class="mo-profile-strip">
            <div class="mo-profile-strip__left">
                <div class="mo-profile-strip__avatar">
                    <img src="<?php echo htmlspecialchars($avatarUrl); ?>"
                        onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=7b1d1d&color=fff&size=80'"
                        alt="<?php echo htmlspecialchars($userName); ?>">
                </div>
                <div>
                    <h3 class="mo-profile-strip__name"><?php echo htmlspecialchars($userName); ?></h3>
                    <p class="mo-profile-strip__email"><?php echo htmlspecialchars($userEmail); ?></p>
                </div>
            </div>
            <div class="mo-profile-strip__right">
                <form method="get" id="timeRangeForm">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($activeStatus); ?>">
                    <?php if ($searchQuery !== ''): ?>
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <?php endif; ?>
                    <select class="mo-filter-select" id="timeFilter" name="time_range">
                        <?php foreach ($timeRangeLabels as $rangeKey => $rangeLabel): ?>
                            <option value="<?php echo htmlspecialchars($rangeKey); ?>" <?php echo $timeRange === $rangeKey ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rangeLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <div class="mo-tabs">
            <div class="mo-tabs__nav">
                <?php foreach ($tabLabels as $tabKey => $tabLabel): ?>
                    <a href="<?php echo htmlspecialchars($buildOrdersUrl(['status' => $tabKey])); ?>"
                        class="mo-tab <?php echo $activeStatus === $tabKey ? 'active' : ''; ?> text-decoration-none">
                        <?php echo htmlspecialchars($tabLabel); ?>
                        <span class="ms-1">(<?php echo (int)($orderCounts[$tabKey] ?? 0); ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="mo-tabs__search">
                <form method="get" id="orderSearchForm" class="d-flex align-items-center position-relative">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($activeStatus); ?>">
                    <input type="hidden" name="time_range" value="<?php echo htmlspecialchars($timeRange); ?>">
                    <input type="text" class="mo-search-input" id="orderSearch" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search Order">
                    <button type="submit" class="border-0 bg-transparent p-0" aria-label="Search order">
                        <i class="bi bi-search mo-search-icon"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="mo-orders" id="ordersContainer">
            <?php
            $statusClass = [
                'pending' => 'mo-badge--processing',
                'processing' => 'mo-badge--processing',
                'shipped' => 'mo-badge--shipped',
                'delivered' => 'mo-badge--delivered',
                'cancelled' => 'mo-badge--cancelled',
                'paid' => 'mo-badge--shipped',
            ];

            if (!empty($orders)):
                foreach ($orders as $order):
                    $slug = strtolower((string)$order['status']);
                    $badgeCls = $statusClass[$slug] ?? 'mo-badge--processing';
                    $formattedDate = date('F j, Y', strtotime((string)$order['created_at']));
            ?>
                    <div class="mo-order-card" data-status="<?php echo htmlspecialchars($slug); ?>" id="order-<?php echo (int)$order['id']; ?>">
                        <div class="mo-order-card__header">
                            <div class="mo-order-card__meta">
                                <span class="mo-order-id">Order #<?php echo htmlspecialchars((string)($order['order_number'] ?: $order['id'])); ?></span>
                                <span class="mo-badge <?php echo $badgeCls; ?>"><?php echo ucfirst((string)$order['status']); ?></span>
                                <span class="mo-order-date"><?php echo htmlspecialchars($formattedDate); ?></span>
                            </div>
                            <div class="mo-order-card__total-top">
                                ₹<?php echo number_format((float)$order['total_amount']); ?>
                            </div>
                        </div>

                        <div class="mo-order-card__product">
                            <div class="mo-order-card__product-img">
                                <img src="<?php echo htmlspecialchars((string)($order['main_image'] ?: 'assets/images/homepage/Best Sellers (1).png')); ?>"
                                    alt="<?php echo htmlspecialchars((string)($order['product_names'] ?? 'Order item')); ?>"
                                    onerror="this.src='assets/images/homepage/Best Sellers (1).png'">
                            </div>
                            <div class="mo-order-card__product-info">
                                <h5><?php echo htmlspecialchars((string)($order['product_names'] ?: 'Custom Sweets Box')); ?></h5>
                                <p>Status: <?php echo htmlspecialchars(ucfirst((string)$order['status'])); ?></p>
                            </div>
                            <div class="mo-order-card__product-total">
                                <span class="mo-order-card__total-label">Total</span>
                                <span class="mo-order-card__total-price">₹<?php echo number_format((float)$order['total_amount']); ?></span>
                            </div>
                        </div>

                        <div class="mo-order-card__footer">
                            <div class="mo-order-card__delivery">
                                <span><i class="bi bi-geo-alt-fill"></i> Delivery to registered address</span>
                            </div>
                            <div class="mo-order-card__actions">
                                <a href="order-details.php?id=<?php echo (int)$order['id']; ?>" class="mo-btn mo-btn--outline text-decoration-none text-center">View Order</a>
                                <?php if ($slug === 'shipped'): ?>
                                    <button class="mo-btn mo-btn--outline" type="button">Track Order</button>
                                <?php endif; ?>
                                <?php if ($slug === 'pending'): ?>
                                    <form method="post" class="d-inline-block" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="action" value="cancel_order">
                                        <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($activeStatus); ?>">
                                        <input type="hidden" name="time_range" value="<?php echo htmlspecialchars($timeRange); ?>">
                                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>">
                                        <button type="submit" class="mo-btn mo-btn--cancel">Cancel Order</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3"><i class="bi bi-bag-x" style="font-size: 3rem; color: #ccc;"></i></div>
                    <h4>No orders found</h4>
                    <p class="text-muted">No orders match your selected filters.</p>
                    <a href="index.php" class="btn btn-primary mt-3" style="background-color: var(--clr-secondary); border: none;">Start Shopping</a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const timeFilter = document.getElementById('timeFilter');
    const timeRangeForm = document.getElementById('timeRangeForm');
    const searchInput = document.getElementById('orderSearch');
    const orderSearchForm = document.getElementById('orderSearchForm');

    if (timeFilter && timeRangeForm) {
        timeFilter.addEventListener('change', function () {
            timeRangeForm.submit();
        });
    }

    if (!searchInput || !orderSearchForm) {
        return;
    }

    let debounceTimer = null;
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            orderSearchForm.submit();
        }, 450);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
