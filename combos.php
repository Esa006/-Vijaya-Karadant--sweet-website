<?php
/**
 * Sweets Website
 * =============================================================
 * File: combos.php
 * Description: Dedicated Combo Offers grid page
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/ComboService.php';
require_once SERVICES_PATH . '/ProductService.php';

$comboService = new ComboService();
$category = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : 'all';

if ($category === 'all') {
    $combos = $comboService->getAllCombos();
} else {
    $combos = $comboService->getCombosByCategory($category);
}

// Flash messages
$flashSuccess = $_SESSION['cart_success'] ?? null;
$flashError   = $_SESSION['cart_error']   ?? null;
unset($_SESSION['cart_success'], $_SESSION['cart_error']);

$seoContext = [
    'title' => 'Special Combo Offers | ' . SITE_NAME,
    'description' => 'Save big with our exclusive traditional sweets and namkeen combos. Handpicked and packed for maximum value.',
    'canonical' => BASE_URL . 'combos.php',
    'type' => 'website'
];
require_once 'includes/header.php';
require_once 'sections/category-strip.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/combos.css?v=<?php echo SITE_VERSION; ?>">

<!-- ── Flash Toasts ── -->
<?php if ($flashSuccess || $flashError): ?>
<div class="p-combo-toast-wrap" aria-live="polite">
    <?php if ($flashSuccess): ?>
    <div class="p-combo-toast p-combo-toast--success" id="flashToast">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?php echo htmlspecialchars($flashSuccess); ?>
    </div>
    <?php elseif ($flashError): ?>
    <div class="p-combo-toast p-combo-toast--error" id="flashToast">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars($flashError); ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Page Header ── -->
<div class="p-combo-header">
    <div class="container">
        <div class="p-combo-header__inner">
            <div>
                <span class="p-combo-header__eyebrow">Bundle &amp; Save</span>
                <h1 class="p-combo-header__title">Exclusive Combo Offers</h1>
                <p class="p-combo-header__subtitle">Handpicked combos that give you more for less. Every item included is selected for quality and value.</p>
            </div>
            <!-- Stats strip -->
            <div class="p-combo-header__stats">
                <div class="p-combo-stat">
                    <span class="p-combo-stat__val"><?php echo count($combos); ?>+</span>
                    <span class="p-combo-stat__label">Combos</span>
                </div>
                <div class="p-combo-stat">
                    <span class="p-combo-stat__val">₹<?php
                        $maxSave = 0;
                        foreach ($combos as $c) { $maxSave = max($maxSave, (float)$c['savings_amount']); }
                        echo number_format($maxSave, 0);
                    ?></span>
                    <span class="p-combo-stat__label">Max Savings</span>
                </div>
                <div class="p-combo-stat">
                    <span class="p-combo-stat__val">Free</span>
                    <span class="p-combo-stat__label">Delivery above ₹999</span>
                </div>
            </div>
        </div>

        <!-- Category Filters -->
        <div class="p-combo-filters">
            <?php
            $filterCats = [
                'all'      => 'All Combos',
                'karadant' => 'Karadant Combos',
                'namkeen'  => 'Namkeen Combos',
                'laddu'    => 'Laddu Combos',
                'gifting'  => 'Gift Boxes',
                'mixed'    => 'Mixed Combos',
            ];
            foreach ($filterCats as $slug => $label):
                $active = ($category === $slug) ? 'p-combo-filter-btn--active' : '';
            ?>
            <a href="combos.php?category=<?php echo $slug; ?>" class="p-combo-filter-btn <?php echo $active; ?>">
                <?php echo $label; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ── Combos Grid ── -->
<section class="p-combo-grid-section py-5">
    <div class="container">
        <?php if (empty($combos)): ?>
        <div class="p-combo-empty text-center py-5">
            <i class="bi bi-box-seam p-combo-empty__icon"></i>
            <h4 class="mt-3 mb-2">No combos in this category</h4>
            <p class="text-muted mb-4">Check back soon or browse all our combos.</p>
            <a href="combos.php" class="p-combo-cta-btn">View All Combos</a>
        </div>
        <?php else: ?>

        <!-- Results count -->
        <p class="p-combo-results-count mb-4">
            Showing <strong><?php echo count($combos); ?></strong> combo<?php echo count($combos) !== 1 ? 's' : ''; ?>
            <?php if ($category !== 'all'): ?>
            in <strong><?php echo ucfirst(htmlspecialchars($category)); ?></strong>
            <?php endif; ?>
        </p>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php foreach ($combos as $combo):
                $img        = $combo['image'] ?? 'assets/images/placeholder.png';
                $gallery    = $combo['gallery'] ?? [];
                $savings    = (float)$combo['savings_amount'];
                $finalPrice = (float)$combo['final_price'];
                $origPrice  = (float)$combo['original_price'];
                $savingsPct = ($origPrice > 0) ? round(($savings / $origPrice) * 100) : 0;
                $cardId     = 'combo-' . (int)$combo['id'];
                $hasGallery = count($gallery) > 1;
                $swiperId   = 'cswiper-' . (int)$combo['id'];
            ?>
            <div class="col">
                <article class="p-combo-card" id="<?php echo $cardId; ?>">

                    <!-- ── Image / Gallery ── -->
                    <div class="p-combo-card__img-wrap">
                        <?php if ($combo['stock_status'] === 'out_of_stock'): ?>
                        <div class="p-combo-card__oos-overlay">Out of Stock</div>
                        <span class="p-combo-card__badge" style="background: #ef4444; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);">Out of Stock</span>
                        <?php elseif ($savingsPct > 0): ?>
                        <span class="p-combo-card__badge"><?php echo $savingsPct; ?>% OFF</span>
                        <?php endif; ?>

                        <?php if ($hasGallery): ?>
                        <!-- Swiper gallery -->
                        <div class="swiper combo-card-swiper" id="<?php echo $swiperId; ?>">
                            <div class="swiper-wrapper">
                                <?php foreach ($gallery as $gi): ?>
                                <div class="swiper-slide">
                                    <img src="<?php echo BASE_URL . htmlspecialchars($gi['image_path']); ?>"
                                         alt="<?php echo htmlspecialchars($combo['name']); ?>"
                                         class="p-combo-card__img"
                                         loading="lazy"
                                         onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>assets/images/placeholder.png';">
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-pagination combo-card-pagination"></div>
                        </div>
                        <?php else: ?>
                        <img src="<?php echo BASE_URL . htmlspecialchars($img); ?>"
                             alt="<?php echo htmlspecialchars($combo['name']); ?>"
                             class="p-combo-card__img"
                             loading="lazy"
                             onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>assets/images/placeholder.png';">
                        <?php endif; ?>
                    </div>

                    <!-- ── Body ── -->
                    <div class="p-combo-card__body">

                        <h2 class="p-combo-card__name"><?php echo htmlspecialchars($combo['name']); ?></h2>

                        <?php if (!empty($combo['description'])): ?>
                        <p class="p-combo-card__desc"><?php echo htmlspecialchars($combo['description']); ?></p>
                        <?php endif; ?>

                        <!-- Mini product images strip -->
                        <?php if (!empty($combo['items'])): ?>
                        <div class="p-combo-card__items-strip">
                            <?php foreach (array_slice($combo['items'], 0, 5) as $ci): ?>
                            <div class="p-combo-item-thumb" title="<?php echo htmlspecialchars($ci['name']); ?> ×<?php echo (int)$ci['quantity']; ?>">
                                <img src="<?php echo BASE_URL . htmlspecialchars($ci['image'] ?? 'assets/images/placeholder.png'); ?>"
                                     alt="<?php echo htmlspecialchars($ci['name']); ?>"
                                     onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>assets/images/placeholder.png';">
                                <span class="p-combo-item-thumb__qty">×<?php echo (int)$ci['quantity']; ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php if (count($combo['items']) > 5): ?>
                            <span class="p-combo-item-more">+<?php echo count($combo['items']) - 5; ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- "What's Inside" expandable -->
                        <?php if (!empty($combo['items'])): ?>
                        <div class="p-combo-card__accordion">
                            <button class="p-combo-accordion-toggle"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#inside-<?php echo (int)$combo['id']; ?>"
                                    aria-expanded="false">
                                <i class="bi bi-box-seam me-1"></i> What's inside
                                <i class="bi bi-chevron-down p-combo-chevron ms-auto"></i>
                            </button>
                            <div id="inside-<?php echo (int)$combo['id']; ?>" class="collapse">
                                <ul class="p-combo-items-list">
                                    <?php foreach ($combo['items'] as $ci): ?>
                                    <li class="p-combo-item-row">
                                        <img src="<?php echo BASE_URL . htmlspecialchars($ci['image'] ?? 'assets/images/placeholder.png'); ?>"
                                             alt=""
                                             class="p-combo-item-row__img"
                                             onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>assets/images/placeholder.png';">
                                        <span class="p-combo-item-row__name"><?php echo htmlspecialchars($ci['name']); ?></span>
                                        <span class="p-combo-item-row__qty">×<?php echo (int)$ci['quantity']; ?></span>
                                        <span class="p-combo-item-row__price">₹<?php echo number_format((float)($ci['sale_price'] ?: $ci['base_price']), 0); ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- ── Pricing ── -->
                        <div class="p-combo-card__pricing">
                            <div class="p-combo-card__price-block">
                                <span class="p-combo-card__final-price">₹<?php echo number_format($finalPrice, 0); ?></span>
                                <?php if ($savings > 0): ?>
                                <span class="p-combo-card__original-price">₹<?php echo number_format($origPrice, 0); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($savings > 0): ?>
                            <span class="p-combo-card__save-pill">
                                <i class="bi bi-lightning-fill"></i> Save ₹<?php echo number_format($savings, 0); ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- ── CTA Buttons ── -->
                        <div class="p-combo-card__cta">
                            <?php if ($combo['stock_status'] === 'out_of_stock'): ?>
                                <button type="button" class="p-combo-add-btn w-100 disabled" style="background:#6B7280; cursor:not-allowed;" disabled>
                                    <i class="bi bi-x-circle me-1"></i> Out of Stock
                                </button>
                            <?php else: ?>
                                <form method="POST" action="<?php echo BASE_URL; ?>shopping-cart.php" class="m-0 flex-grow-1">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="action" value="add_combo">
                                    <input type="hidden" name="combo_id" value="<?php echo (int)$combo['id']; ?>">
                                    <button type="submit" class="p-combo-add-btn w-100">
                                        <i class="bi bi-cart-plus me-1"></i> Add to Cart
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── Best Sellers Strip ── -->
<div class="mt-5 pt-4 border-top">
<?php
    $productService = new ProductService();
    $bestSellers = $productService->getFeaturedProducts();
    require_once 'sections/bestsellers.php';
?>
</div>

<?php require_once 'includes/footer.php'; ?>

<style>
/* Combo card gallery swiper */
.combo-card-swiper { width: 100%; height: 100%; }
.combo-card-swiper .swiper-slide { overflow: hidden; }
.combo-card-pagination {
    position: absolute;
    bottom: 6px;
    left: 0; right: 0;
    display: flex;
    justify-content: center;
    gap: 4px;
    z-index: 2;
    pointer-events: none;
}
.combo-card-pagination .swiper-pagination-bullet {
    width: 5px; height: 5px;
    background: rgba(255,255,255,.6);
    border-radius: 50%;
    opacity: 1;
    transition: background .2s, transform .2s;
    pointer-events: all;
    cursor: pointer;
}
.combo-card-pagination .swiper-pagination-bullet-active {
    background: #fff;
    transform: scale(1.35);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Flash toast auto-dismiss ──
    var t = document.getElementById('flashToast');
    if (t) {
        setTimeout(function() {
            t.style.transition = 'opacity 0.5s';
            t.style.opacity = '0';
            setTimeout(function() { t.remove(); }, 500);
        }, 4000);
    }

    // ── Accordion chevron rotation ──
    document.querySelectorAll('.p-combo-accordion-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var chev = this.querySelector('.p-combo-chevron');
            if (chev) {
                var expanded = this.getAttribute('aria-expanded') === 'true';
                chev.style.transform = expanded ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
    });

    // ── Init Swiper on every combo-card-swiper ──
    document.querySelectorAll('.combo-card-swiper').forEach(function(el) {
        new Swiper(el, {
            loop: true,
            autoplay: { delay: 3200, disableOnInteraction: false, pauseOnMouseEnter: true },
            speed: 600,
            pagination: {
                el: el.querySelector('.combo-card-pagination'),
                clickable: true,
            },
        });
    });
});
</script>
