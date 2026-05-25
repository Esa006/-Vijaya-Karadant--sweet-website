<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/home-hero.php
 * Description: Premium Home Hero section — dynamic slides from DB
 * =============================================================
 */

// Load service if not already loaded
if (!class_exists('HeroSlideService')) {
    require_once SERVICES_PATH . '/HeroSlideService.php';
}
if (!isset($heroSlideService)) {
    $heroSlideService = new HeroSlideService();
}
$heroSlides = $heroSlideService->getActiveSlides();

// Static badge / proof data (shared across all slides)
$heroIcons = [
    ['icon' => 'bi bi-award', 'img' => null,  'alt' => 'Since 1907', 'label' => 'Since 1907'],
    ['icon' => null, 'img' => 'assets/images/banners/hero-icon (1).png', 'alt' => 'Make in India', 'label' => 'Make in India'],
    ['icon' => null, 'img' => 'assets/images/banners/hero-icon (2).png', 'alt' => 'ISO Certified', 'label' => 'ISO Certified'],
    ['icon' => null, 'img' => 'assets/images/banners/hero-icon (3).png', 'alt' => 'Pure and Natural', 'label' => 'Pure and Natural'],
];
?>
<!-- Premium Home Hero Section — Dynamic -->
<section class="c-home-hero swiper" id="homeHeroSwiper">
    <div class="swiper-wrapper">

        <?php foreach ($heroSlides as $index => $slide): ?>
        <?php
            $desktopBg   = htmlspecialchars($slide['desktop_image'] ?? '', ENT_QUOTES);
            $mobileBg    = htmlspecialchars($slide['mobile_image']  ?? '', ENT_QUOTES);
            $titleLine1  = htmlspecialchars($slide['title_line1']   ?? '', ENT_QUOTES);
            $titleAccent = htmlspecialchars($slide['title_accent']  ?? '', ENT_QUOTES);
            $tagline     = htmlspecialchars($slide['tagline']       ?? '', ENT_QUOTES);
            $btnText     = htmlspecialchars($slide['button_text']   ?? 'Shop Now', ENT_QUOTES);
            $btnUrl      = htmlspecialchars($slide['button_url']    ?? '#bestsellers', ENT_QUOTES);
            $slideNum    = $index + 1;
        ?>
        <div class="swiper-slide c-home-hero__slide c-home-hero__slide--dynamic"
             data-desktop-bg="<?php echo BASE_URL . $desktopBg; ?>"
             data-mobile-bg="<?php echo BASE_URL . $mobileBg; ?>"
             style="background-image: url('<?php echo BASE_URL . $desktopBg; ?>');">
            <div class="container c-home-hero__container">
                <div class="c-home-hero__wrapper">
                    <p class="c-home-hero__tagline"><?php echo htmlspecialchars($tagline); ?></p>
                    <h1 class="c-home-hero__title">
                        <?php echo htmlspecialchars($titleLine1); ?> <br>
                        <span class="c-home-hero__title-accent"><?php echo htmlspecialchars($titleAccent); ?></span>
                    </h1>
                    <div class="c-home-hero__actions">
                        <a href="<?php echo $btnUrl; ?>" class="c-cta-button"><?php echo htmlspecialchars($btnText); ?></a>
                    </div>

                    <!-- Proof Section -->
                    <div class="c-home-hero__proof-section<?php echo $slideNum === 3 || $slideNum === 4 ? ' c-home-hero__proof-section--enhanced' : ''; ?>">
                        <div class="c-home-hero__social">
                            <div class="c-avatar-group">
                                <div class="c-avatar c-avatar--1"></div>
                                <div class="c-avatar c-avatar--2"></div>
                                <div class="c-avatar c-avatar--3"></div>
                            </div>
                            <p class="c-home-hero__social-text">
                                <span class="c-home-hero__social-rating">4.9/5</span> from 2,000+ happy customers
                            </p>
                        </div>
                        <div class="c-home-hero__badges<?php echo $slideNum === 1 ? ' c-home-hero__badges--banner-1' : ''; ?>">
                            <?php foreach ($heroIcons as $badge): ?>
                            <div class="c-badge-item">
                                <?php if ($badge['icon']): ?>
                                    <i class="<?php echo $badge['icon']; ?> c-badge-icon"></i>
                                <?php else: ?>
                                    <img src="<?php echo BASE_URL . $badge['img']; ?>" class="c-badge-icon" alt="<?php echo htmlspecialchars($badge['alt']); ?>">
                                <?php endif; ?>
                                <?php echo htmlspecialchars($badge['label']); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div>

    <!-- Navigation / Pagination -->
    <div class="swiper-pagination hero-pagination"></div>
</section>

<script>
// Responsive hero background swapping
(function () {
    function applyHeroBg() {
        var isMobile = window.innerWidth <= 575;
        document.querySelectorAll('.c-home-hero__slide--dynamic').forEach(function (slide) {
            var bg = isMobile
                ? (slide.getAttribute('data-mobile-bg') || slide.getAttribute('data-desktop-bg'))
                : slide.getAttribute('data-desktop-bg');
            if (bg) slide.style.backgroundImage = "url('" + bg + "')";
        });
    }
    applyHeroBg();
    window.addEventListener('resize', applyHeroBg);
})();
</script>
