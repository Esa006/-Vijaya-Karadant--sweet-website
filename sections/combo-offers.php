<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/combo-offers.php
 * Description: Home-page Combo Offers section — live data, high-conversion cards
 * =============================================================
 */

if (!class_exists('ComboService')) {
    require_once SERVICES_PATH . '/ComboService.php';
}

$_homeCombos = (new ComboService())->getAllCombos();

// Filter out combos that are out of stock
$_homeCombos = array_filter($_homeCombos, function($combo) {
    return ($combo['stock_status'] ?? 'in_stock') === 'in_stock';
});

// Limit to 8 for the home-page carousel
$_homeCombos = array_slice($_homeCombos, 0, 8);
?>

<?php if (!empty($_homeCombos)): ?>
<section class="c-combo-offers-section py-5 js-reveal" id="combo-offers">
    <div class="container">

        <!-- ── Section Header ── -->
        <div class="row align-items-end mb-4">
            <div class="col">
                <span class="c-section-eyebrow">Bundle & Save</span>
                <h2 class="c-section-title c-header-text mb-0">Special Combo Offers</h2>
                <p class="text-muted mt-1 mb-0">Curated combos at unbeatable prices — save more on every order.</p>
            </div>
            <div class="col-auto">
                <a href="<?php echo BASE_URL; ?>combos.php" class="c-combo-see-all-btn">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- ── Combo Cards Swiper ── -->
        <div class="swiper c-combo-swiper">
            <div class="swiper-wrapper">
                <?php foreach ($_homeCombos as $hc):
                    $hcImg      = htmlspecialchars($hc['image'] ?? 'assets/images/placeholder.png');
                    $hcSavings  = (float)$hc['savings_amount'];
                    $hcFinal    = (float)$hc['final_price'];
                    $hcOriginal = (float)$hc['original_price'];
                    $hcSavingsPct = ($hcOriginal > 0) ? round(($hcSavings / $hcOriginal) * 100) : 0;
                ?>
                <div class="swiper-slide c-combo-slide">
                    <div class="c-combo-card">

                        <!-- Image + Badge -->
                        <div class="c-combo-card__img-wrap">
                            <img src="<?php echo BASE_URL . $hcImg; ?>"
                                 alt="<?php echo htmlspecialchars($hc['name']); ?>"
                                 class="c-combo-card__img"
                                 onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>assets/images/placeholder.png';">
                            <?php if ($hcSavings > 0): ?>
                            <span class="c-combo-card__badge">
                                <?php echo $hcSavingsPct; ?>% OFF
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Body -->
                        <div class="c-combo-card__body">
                            <h3 class="c-combo-card__name"><?php echo htmlspecialchars($hc['name']); ?></h3>

                            <!-- Mini product strip -->
                            <?php if (!empty($hc['items'])): ?>
                            <div class="c-combo-card__items-strip">
                                <?php foreach (array_slice($hc['items'], 0, 4) as $ci): ?>
                                <div class="c-combo-item-pill" title="<?php echo htmlspecialchars($ci['name']); ?> ×<?php echo $ci['quantity']; ?>">
                                    <img src="<?php echo BASE_URL . htmlspecialchars($ci['image'] ?? 'assets/images/placeholder.png'); ?>"
                                         alt="<?php echo htmlspecialchars($ci['name']); ?>"
                                         onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>assets/images/placeholder.png';">
                                    <span class="c-combo-item-pill__qty">×<?php echo (int)$ci['quantity']; ?></span>
                                </div>
                                <?php endforeach; ?>
                                <?php if (count($hc['items']) > 4): ?>
                                <span class="c-combo-item-more">+<?php echo count($hc['items']) - 4; ?> more</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Pricing Row -->
                            <div class="c-combo-card__pricing">
                                <span class="c-combo-card__price">₹<?php echo number_format($hcFinal, 0); ?></span>
                                <?php if ($hcSavings > 0): ?>
                                <span class="c-combo-card__original">₹<?php echo number_format($hcOriginal, 0); ?></span>
                                <span class="c-combo-card__save">Save ₹<?php echo number_format($hcSavings, 0); ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- CTA -->
                            <div class="c-combo-card__cta">
                                <form method="POST" action="<?php echo BASE_URL; ?>shopping-cart.php" class="m-0 flex-grow-1">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="action" value="add_combo">
                                    <input type="hidden" name="combo_id" value="<?php echo (int)$hc['id']; ?>">
                                    <button type="submit" class="c-combo-card__add-btn">
                                        <i class="bi bi-cart-plus me-1"></i> Add to Cart
                                    </button>
                                </form>
                                <a href="<?php echo BASE_URL; ?>combos.php" class="c-combo-card__detail-btn" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Swiper Navigation -->
            <div class="swiper-button-prev c-combo-prev"></div>
            <div class="swiper-button-next c-combo-next"></div>
            <div class="swiper-pagination c-combo-pagination"></div>
        </div>

    </div>
</section>

<style>
/* ─── Combo Offers Section ──────────────────────────────────────────── */
.c-combo-offers-section {
    background: var(--color-bg-light);
    overflow: hidden;
}
.c-section-eyebrow {
    display: inline-block;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--color-primary);
    margin-bottom: 6px;
}
.c-combo-see-all-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    border: 2px solid var(--color-primary);
    color: var(--color-primary);
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
}
.c-combo-see-all-btn:hover {
    background: var(--color-primary);
    color: #fff;
}

/* ─── Swiper Layout ─────────────────────────────────────────────────── */
.c-combo-swiper {
    padding-bottom: 44px !important;
}
.c-combo-slide {
    width: 300px;
}

/* ─── Card ──────────────────────────────────────────────────────────── */
.c-combo-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: transform 0.28s ease, box-shadow 0.28s ease;
}
.c-combo-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 36px rgba(0,0,0,0.13);
}
.c-combo-card__img-wrap {
    position: relative;
    overflow: hidden;
}
.c-combo-card__img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
    transition: transform 0.35s ease;
}
.c-combo-card:hover .c-combo-card__img {
    transform: scale(1.05);
}
.c-combo-card__badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #e11d48;
    color: #fff;
    font-size: 0.78rem;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 50px;
    letter-spacing: 0.03em;
    z-index: 2;
}
.c-combo-card__body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex: 1;
}
.c-combo-card__name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--color-primary, #7b2d2d);
    margin: 0;
    line-height: 1.3;
}

/* ─── Mini Items Strip ──────────────────────────────────────────────── */
.c-combo-card__items-strip {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.c-combo-item-pill {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.c-combo-item-pill img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.c-combo-item-pill__qty {
    position: absolute;
    bottom: -4px;
    right: -4px;
    background: var(--color-primary, #7b2d2d);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    border-radius: 50px;
    padding: 1px 4px;
    line-height: 1.3;
}
.c-combo-item-more {
    font-size: 0.75rem;
    color: var(--color-text-muted, #888);
    font-weight: 600;
}

/* ─── Pricing ───────────────────────────────────────────────────────── */
.c-combo-card__pricing {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    border-top: 1px solid #f0f0f0;
    padding-top: 10px;
}
.c-combo-card__price {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--color-primary, #7b2d2d);
}
.c-combo-card__original {
    font-size: 0.9rem;
    color: #999;
    text-decoration: line-through;
}
.c-combo-card__save {
    font-size: 0.78rem;
    font-weight: 700;
    color: #16a34a;
    background: #dcfce7;
    padding: 2px 8px;
    border-radius: 50px;
}

/* ─── CTA Buttons ───────────────────────────────────────────────────── */
.c-combo-card__cta {
    display: flex;
    gap: 8px;
    align-items: stretch;
    margin-top: auto;
}
.c-combo-card__add-btn {
    flex: 1;
    padding: 10px 14px;
    background: var(--color-primary, #7b2d2d);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s;
    text-align: center;
}
.c-combo-card__add-btn:hover {
    opacity: 0.88;
    transform: scale(1.02);
}
.c-combo-card__detail-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    border: 2px solid var(--color-primary, #7b2d2d);
    border-radius: 10px;
    color: var(--color-primary, #7b2d2d);
    font-size: 1rem;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
    flex-shrink: 0;
}
.c-combo-card__detail-btn:hover {
    background: var(--color-primary, #7b2d2d);
    color: #fff;
}

/* ─── Swiper Overrides ──────────────────────────────────────────────── */
.c-combo-swiper .swiper-button-prev,
.c-combo-swiper .swiper-button-next {
    width: 40px;
    height: 40px;
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12);
    color: var(--color-primary, #7b2d2d);
    top: 38%;
}
.c-combo-swiper .swiper-button-prev::after,
.c-combo-swiper .swiper-button-next::after {
    font-size: 0.9rem;
    font-weight: 900;
}
.c-combo-swiper .swiper-pagination-bullet-active {
    background: var(--color-primary, #7b2d2d);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper !== 'undefined') {
        new Swiper('.c-combo-swiper', {
            slidesPerView: 1.2,
            spaceBetween: 16,
            grabCursor: true,
            loop: false,
            navigation: {
                nextEl: '.c-combo-next',
                prevEl: '.c-combo-prev',
            },
            pagination: {
                el: '.c-combo-pagination',
                clickable: true,
            },
            breakpoints: {
                576: { slidesPerView: 1.8, spaceBetween: 16 },
                768: { slidesPerView: 2.5, spaceBetween: 20 },
                1024: { slidesPerView: 3.2, spaceBetween: 20 },
                1280: { slidesPerView: 4,   spaceBetween: 24 },
            }
        });
    }
});
</script>
<?php endif; ?>
