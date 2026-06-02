<?php
declare(strict_types=1);
/**
 * Sweets Website
 * =============================================================
 * File: shopping-cart.php
 * Description: Standalone shopping cart page
 * Author: Sweets Website Team
 * Version: 2.0.0
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';
require_once SERVICES_PATH . '/CartService.php';

$productService = new ProductService();
$cartService    = new CartService();

// Handle cart actions
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['action'] ?? '') : ($_GET['action'] ?? '');
$cartId = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['id'] ?? '') : ($_GET['id'] ?? '');

if ($action) {
    // CSRF Validation for state-changing actions via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!$token || $token !== ($_SESSION['csrf_token'] ?? '')) {
            die('CSRF token validation failed.');
        }
    }

    if ($action === 'add_combo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $comboId = (int)($_POST['combo_id'] ?? 0);
        require_once SERVICES_PATH . '/ComboService.php';
        $comboService = new ComboService();
        $combo = $comboService->getComboById($comboId);
        
        if ($combo) {
            if ($cartService->addCombo($combo, 1)) {
                $_SESSION['cart_success'] = htmlspecialchars($combo['name']) . " combo added to cart!";
            } else {
                $_SESSION['cart_error'] = "One or more items in this combo are out of stock.";
            }
        } else {
            $_SESSION['cart_error'] = "Combo not found.";
        }
        header('Location: shopping-cart.php');
        exit;
    }

    if ($action === 'remove' && $cartId) {
        $removed = $cartService->removeItem($cartId);
        if (!$removed) {
            $_SESSION['cart_error'] = "Could not remove item from cart.";
        } else {
            $_SESSION['cart_success'] = "Item removed successfully.";
        }
    } elseif ($action === 'update' && $cartId) {
        $qtyInput = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['qty'] ?? 1) : ($_GET['qty'] ?? 1);
        $qty = (int)$qtyInput;
        $cartService->updateQuantity($cartId, $qty);
    } elseif ($action === 'clear') {
        $cartService->clearCart();
    }
    header('Location: shopping-cart.php');
    exit;
}

$cartItems = $cartService->getItems();

// Backfill missing/invalid cart item data from product source
$productCache = [];
$isValidCartImage = static function (string $path): bool {
    $path = trim($path);
    if ($path === '') {
        return false;
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return true;
    }

    $absolutePath = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/\\'));
    return is_file($absolutePath);
};

foreach ($cartItems as $cartKey => &$item) {
    $slug = trim((string)($item['slug'] ?? ''));
    if ($slug === '') {
        continue;
    }

    if (isset($item['type']) && $item['type'] === 'combo') {
        // Enforce current stock ceiling for existing cart quantities
        $cartService->updateQuantity((string)$cartKey, (int)($item['quantity'] ?? 1));
        if (isset($_SESSION['cart'][$cartKey])) {
            $item = $_SESSION['cart'][$cartKey];
        }
        continue;
    }

    if (!isset($productCache[$slug])) {
        $productCache[$slug] = $productService->getProductBySlug($slug);
    }

    $product = $productCache[$slug];
    if (!$product) {
        continue;
    }

    if (empty($item['name'])) {
        $item['name'] = $product['name'] ?? 'Product';
    }

    $resolvedPrice = (float)($product['sale_price'] ?? $product['price'] ?? $product['base_price'] ?? 0);
    if ((float)($item['price'] ?? 0) <= 0 && $resolvedPrice > 0) {
        $item['price'] = $resolvedPrice;
    }

    $resolvedOriginal = (float)($product['base_price'] ?? $product['original_price'] ?? 0);
    if ((float)($item['original_price'] ?? 0) <= 0 && $resolvedOriginal > 0) {
        $item['original_price'] = $resolvedOriginal;
    }

    $currentImage = trim((string)($item['image'] ?? ''));
    $isPlaceholder = $currentImage === '' || $currentImage === 'assets/images/placeholder.png';
    if ($isPlaceholder || !$isValidCartImage($currentImage)) {
        $candidateImage = trim((string)($product['image_path'] ?? $product['image'] ?? ''));
        if ($isValidCartImage($candidateImage)) {
            $item['image'] = $candidateImage;
        } else {
            $item['image'] = 'assets/images/placeholder.png';
        }
    }

    $_SESSION['cart'][$cartKey] = $item;

    // Enforce current stock ceiling for existing cart quantities
    $cartService->updateQuantity((string)$cartKey, (int)($item['quantity'] ?? 1));
    if (isset($_SESSION['cart'][$cartKey])) {
        $item = $_SESSION['cart'][$cartKey];
    }
}
unset($item);

$subtotal       = $cartService->getSubtotal();
$shipping       = $cartService->getShippingCharges();
$couponDiscount = $cartService->getCouponDiscount();
$couponTitle    = $cartService->getCouponTitle();
$discount       = ($subtotal > 1500 ? 100 : 0) + $couponDiscount;
$total          = max(0, $cartService->getTotal() - ($subtotal > 1500 ? 100 : 0));

$seoContext = [
    'title' => 'Your Shopping Cart | ' . SITE_NAME,
    'description' => 'Review your selected authentic traditional sweets and namkeens before secure checkout.',
    'canonical' => BASE_URL . 'shopping-cart.php',
    'type' => 'website'
];
require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/cart.css?v=<?php echo SITE_VERSION; ?>">

<main class="p-shopping-cart-page">
    <div class="container py-5">

        <!-- ── Breadcrumb ── -->
        <nav class="sc-breadcrumb mb-3" aria-label="breadcrumb">
            <a href="index.php" class="sc-breadcrumb__link">Home</a>
            <span class="sc-breadcrumb__sep">/</span>
            <span class="sc-breadcrumb__current">Your Shopping Cart</span>
        </nav>

        <!-- ── Page Title ── -->
        <h1 class="sc-page-title mb-4">
            Shopping Cart <span class="sc-page-title__count">(<?php echo count($cartItems); ?> items)</span>
        </h1>

        <div class="row g-4 align-items-start">

            <!-- ── LEFT: Cart Items ── -->
            <div class="col-lg-8">
                <div class="sc-items-list">

                    <?php if (empty($cartItems)): ?>
                        <div class="sc-empty text-center py-5">
                            <i class="bi bi-cart-x sc-empty__icon d-block mb-3"></i>
                            <h4 class="fw-bold mb-3">Your cart is empty</h4>
                            <a href="index.php" class="sc-btn-shop">Continue Shopping</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cartItems as $cartKey => $item): ?>
                        <div class="sc-item-card">

                            <!-- Thumbnail -->
                            <a href="cart.php?slug=<?php echo htmlspecialchars((string)($item['slug'] ?? '')); ?>" class="sc-item-card__img-wrap">
                                <img src="<?php echo htmlspecialchars((string)($item['image'] ?? 'assets/images/placeholders/product-placeholder.png')); ?>"
                                     alt="<?php echo htmlspecialchars((string)($item['name'] ?? 'Combo Item')); ?>"
                                     onerror="this.onerror=null;this.src='assets/images/placeholder.png';"
                                     class="sc-item-card__img">
                            </a>

                            <!-- Info -->
                            <div class="sc-item-card__info">
                                <h5 class="sc-item-card__name">
                                    <a href="cart.php?slug=<?php echo htmlspecialchars((string)($item['slug'] ?? '')); ?>">
                                        <?php echo htmlspecialchars((string)($item['name'] ?? 'Combo Item')); ?>
                                    </a>
                                </h5>
                                <?php if (!empty($item['weight'])): ?>
                                    <p class="sc-item-card__weight">Weight : <strong><?php echo htmlspecialchars((string)$item['weight']); ?></strong></p>
                                <?php else: ?>
                                    <p class="sc-item-card__weight"><strong>Combo Pack</strong></p>
                                <?php endif; ?>
                                <?php if (isset($item['error'])): ?>
                                    <p class="text-danger small mt-1 mb-0 fw-bold"><i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($item['error']); ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Qty Controls -->
                            <div class="sc-item-card__qty">
                                <?php if ($item['quantity'] <= 1): ?>
                                    <a href="<?php echo BASE_URL; ?>shopping-cart.php?action=update&id=<?php echo urlencode($cartKey); ?>&qty=0" class="sc-qty-btn sc-qty-btn--remove text-danger js-qty-remove-btn" title="Remove item">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>shopping-cart.php?action=update&id=<?php echo urlencode($cartKey); ?>&qty=<?php echo $item['quantity'] - 1; ?>" class="sc-qty-btn">
                                        <i class="bi bi-dash"></i>
                                    </a>
                                <?php endif; ?>
                                <span class="sc-qty-val"><?php echo $item['quantity']; ?></span>
                                <a href="<?php echo BASE_URL; ?>shopping-cart.php?action=update&id=<?php echo urlencode($cartKey); ?>&qty=<?php echo $item['quantity'] + 1; ?>" class="sc-qty-btn">
                                    <i class="bi bi-plus"></i>
                                </a>
                            </div>

                            <!-- Delete -->
                            <form action="<?php echo BASE_URL; ?>shopping-cart.php" method="POST" class="m-0 js-remove-item-form">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($cartKey); ?>">
                                <button type="button" class="sc-item-card__delete js-remove-btn" aria-label="Remove item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>

                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>

            <!-- ── RIGHT: Order Summary ── -->
            <div class="col-lg-4">
                <div class="sc-summary">
                    <h3 class="sc-summary__title">Order Summary</h3>

                    <div class="sc-summary__lines">
                        <div class="sc-summary__line">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($subtotal); ?></span>
                        </div>
                        <div class="sc-summary__line">
                            <span>Delivery Charges</span>
                            <span>₹<?php echo empty($cartItems) ? '0' : number_format($shipping); ?></span>
                        </div>
                        <?php if ($discount > 0): ?>
                        <div class="sc-summary__line sc-summary__line--discount" id="discount-line">
                            <span>Discount Applied<?php echo $couponTitle ? ' (' . htmlspecialchars($couponTitle) . ')' : ''; ?></span>
                            <span>-₹<span id="discount-val"><?php echo number_format($discount); ?></span></span>
                        </div>
                        <?php else: ?>
                        <div class="sc-summary__line sc-summary__line--discount" id="discount-line" style="display:none;">
                            <span id="discount-label">Discount Applied</span>
                            <span>-₹<span id="discount-val">0</span></span>
                        </div>
                        <?php endif; ?>
                        <hr class="sc-summary__divider">
                        <div class="sc-summary__line sc-summary__line--total">
                            <span>Total Amount</span>
                            <span id="total-val">₹<?php echo empty($cartItems) ? '0' : number_format($total); ?></span>
                        </div>
                    </div>

                    <?php
                    $hasStockIssue = false;
                    foreach ($cartItems as $cItem) {
                        if (isset($cItem['error'])) {
                            $hasStockIssue = true;
                            break;
                        }
                    }
                    ?>
                    <?php if ($hasStockIssue): ?>
                        <button class="sc-summary__checkout-btn" style="opacity:0.5; cursor:not-allowed;" disabled onclick="alert('Please remove out of stock items to proceed.')">
                            Proceed to Checkout
                        </button>
                    <?php else: ?>
                        <a href="checkout.php" class="sc-summary__checkout-btn">
                            Proceed to Checkout
                        </a>
                    <?php endif; ?>

                    <?php if ($couponTitle): ?>
                    <div class="d-flex align-items-center justify-content-between mt-2 p-2 rounded-2" style="background:#fff3cd; border:1px solid #ffc107; font-size:0.85rem;">
                        <span><i class="bi bi-ticket-perforated-fill text-warning me-1"></i> <strong><?php echo htmlspecialchars($couponTitle); ?></strong> applied!</span>
                        <button id="remove-coupon-btn" class="btn btn-sm btn-link text-danger p-0 ms-2" style="font-size:0.8rem;">Remove</button>
                    </div>
                    <?php else: ?>
                    <button id="apply-coupon-btn" class="sc-summary__coupon-link w-100 mt-2" style="background:none; border:1px dashed #7b1d1d; color:#7b1d1d; border-radius:6px; padding:8px; cursor:pointer; font-weight:600;">
                        <i class="bi bi-ticket-perforated me-1"></i> Apply Coupon
                    </button>
                    <?php endif; ?>

                    <!-- Hidden clear form -->
                    <form id="clear-cart-form" action="<?php echo BASE_URL; ?>shopping-cart.php" method="POST" style="display:none;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="clear">
                    </form>

                    <!-- Trust Badges -->
                    <div class="sc-trust-row">
                        <div class="sc-trust-item">
                            <div class="sc-trust-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <span>Secure Payment</span>
                        </div>
                        <div class="sc-trust-item">
                            <div class="sc-trust-icon">
                                <i class="bi bi-patch-check"></i>
                            </div>
                            <span>Authentic Sweets</span>
                        </div>
                        <div class="sc-trust-item">
                            <div class="sc-trust-icon">
                                <i class="bi bi-lightning-charge"></i>
                            </div>
                            <span>Fast Delivery</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Remove cart item ──────────────────────────────────────
    document.querySelectorAll('.js-remove-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = this.closest('form');
            Swal.fire({
                title: 'Remove item?',
                text: 'Do you want to remove this item from your cart?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#7b1d1d',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) { form.submit(); }
            });
        });
    });

    // ── Remove item via quantity minus button ────────────────
    document.querySelectorAll('.js-qty-remove-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            Swal.fire({
                title: 'Remove item?',
                text: 'Do you want to remove this item from your cart?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#7b1d1d',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    document.body.style.opacity = '0.6';
                    document.body.style.pointerEvents = 'none';
                    window.location.href = url;
                }
            });
        });
    });

    // ── Prevent double clicks/rapid clicks on quantity buttons ────────────────
    document.querySelectorAll('.sc-qty-btn').forEach(function (btn) {
        if (!btn.classList.contains('js-qty-remove-btn')) {
            btn.addEventListener('click', function (e) {
                if (btn.classList.contains('is-loading')) {
                    e.preventDefault();
                    return;
                }
                btn.classList.add('is-loading');
                const card = btn.closest('.sc-item-card');
                if (card) {
                    card.style.opacity = '0.5';
                    card.style.pointerEvents = 'none';
                }
            });
        }
    });

    // ── Apply Coupon ──────────────────────────────────────────
    const applyBtn = document.getElementById('apply-coupon-btn');
    if (applyBtn) {
        applyBtn.addEventListener('click', function () {
            Swal.fire({
                title: '<i class="bi bi-ticket-perforated-fill" style="color:#7b1d1d;"></i> Apply Coupon',
                html:
                    '<p style="font-size:0.9rem;color:#666;margin-bottom:14px;">Enter your coupon or promo code below</p>' +
                    '<input id="swal-coupon-input" type="text" class="swal2-input" placeholder="e.g. WELCOME10" style="text-transform:uppercase;letter-spacing:2px;font-weight:bold;">' +
                    '<div id="swal-coupon-msg" style="margin-top:10px;font-size:0.85rem;padding:8px 12px;border-radius:6px;display:none;"></div>',
                confirmButtonText: 'Apply',
                confirmButtonColor: '#7b1d1d',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                showLoaderOnConfirm: true,
                didOpen: () => {
                    const inp = document.getElementById('swal-coupon-input');
                    inp.focus();
                    inp.addEventListener('input', () => { inp.value = inp.value.toUpperCase().replace(/[^A-Z0-9\-_]/g,''); });
                },
                preConfirm: () => {
                    const code = document.getElementById('swal-coupon-input').value.trim();
                    const msgEl = document.getElementById('swal-coupon-msg');

                    const showInlineError = (msg) => {
                        msgEl.style.display = 'block';
                        msgEl.style.background = '#f8d7da';
                        msgEl.style.color = '#842029';
                        msgEl.style.border = '1px solid #f5c2c7';
                        msgEl.innerHTML = '<i class="bi bi-x-circle me-1"></i>' + msg;
                    };

                    if (!code) {
                        showInlineError('Please enter a coupon code.');
                        return false;
                    }

                    const fd = new FormData();
                    fd.append('code', code);

                    return fetch('<?php echo BASE_URL; ?>api/v1/apply_coupon.php', {
                        method: 'POST',
                        body: fd
                    })
                    .then(r => {
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    })
                    .then(data => {
                        if (data.status !== 'success') {
                            showInlineError(data.message || 'Invalid coupon code.');
                            return false;
                        }
                        return data;
                    })
                    .catch(err => {
                        showInlineError('Could not connect. Please try again.');
                        console.error('Coupon API error:', err);
                        return false;
                    });
                }
            }).then(result => {
                if (result.isConfirmed && result.value) {
                    const data = result.value;
                    // Update discount row
                    const discountLine = document.getElementById('discount-line');
                    const discountVal  = document.getElementById('discount-val');
                    const discountLbl  = document.getElementById('discount-label');
                    const totalVal     = document.getElementById('total-val');
                    if (discountLine)  discountLine.style.display = '';
                    if (discountLbl)   discountLbl.textContent = 'Coupon (' + data.coupon_code + ')';
                    if (discountVal)   discountVal.textContent  = Number(data.discount_amount).toLocaleString('en-IN');
                    if (totalVal)      totalVal.textContent      = '₹' + Number(data.new_total).toLocaleString('en-IN');
                    // Replace button
                    applyBtn.outerHTML =
                        '<div class="d-flex align-items-center justify-content-between mt-2 p-2 rounded-2" style="background:#fff3cd;border:1px solid #ffc107;font-size:0.85rem;">' +
                        '<span><i class="bi bi-ticket-perforated-fill text-warning me-1"></i><strong>' + data.coupon_code + '</strong> applied!</span>' +
                        '<button id="remove-coupon-btn" class="btn btn-sm btn-link text-danger p-0 ms-2" style="font-size:0.8rem;">Remove</button>' +
                        '</div>';
                    bindRemoveCoupon();
                    Swal.fire({ icon: 'success', title: 'Coupon Applied!', text: data.message, confirmButtonColor: '#7b1d1d', timer: 2500, showConfirmButton: false });
                }
            });
        });
    }

    // ── Remove Coupon ─────────────────────────────────────────
    function bindRemoveCoupon() {
        const removeBtn = document.getElementById('remove-coupon-btn');
        if (!removeBtn) return;
        removeBtn.addEventListener('click', function () {
            fetch('<?php echo BASE_URL; ?>api/v1/remove_coupon.php', { method: 'POST' })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') { location.reload(); }
                });
        });
    }
    bindRemoveCoupon();

});
</script>
