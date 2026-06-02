<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Sweets Website
 * =============================================================
 * File: index.php
 * Description: Main landing page for the Sweets Website
 * Author: Sweets Website Team
 * Version: 1.0.1
 * =============================================================
 */
require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';
require_once SERVICES_PATH . '/PromotionService.php';

require_once SERVICES_PATH . '/ComboService.php';

// ── Data Layer ──────────────────────────────────────────────────
$productService = new ProductService();
$promotionService = new PromotionService();
$comboService = new ComboService();

$bestSellers = $productService->getFeaturedProducts();
$collectionItems = $productService->getCollectionProducts(3);
$boxOfJoyImages = $productService->getSliderImages('box-of-joy');
$curatedCombos = $promotionService->getPromotion('curated-combos');
$namkeens = $productService->getProductsByCategory('namkeen');
$karadants = $productService->getProductsByCategory('karadant');
$laddus = $productService->getProductsByCategory('laddu');
$combos = $comboService->getAllCombos();

// Build dynamic SEO context for Home page
$seoContext = [
    'title' => 'Authentic Karnataka Sweets & Namkeens | ' . SITE_NAME,
    'description' => 'Order authentic Gokak Karadant, traditional sweets, and premium namkeens online. Handcrafted with organic jaggery and pure cow ghee since 1952.',
    'canonical' => BASE_URL,
    'type' => 'website',
    'schema' => [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => SITE_NAME,
        'url' => BASE_URL,
        'logo' => BASE_URL . SITE_LOGO
    ]
];

require_once 'includes/header.php';

// Category Navigation Strip (Amazon/Flipkart style)
require_once 'sections/category-strip.php';

?>


<!-- Premium Home Hero Section -->
<?php require_once 'sections/home-hero.php'; ?>

<!-- Feature Highlights Strip -->
<?php require_once 'sections/feature-strip.php'; ?>

<!-- Trust Signals Strip -->
<?php require_once 'sections/trust-signals.php'; ?>



<!-- Joy Banners Section -->
<?php require_once 'sections/joy-banners.php'; ?>

<!-- Festival Offers Section (New) -->
<?php require_once 'sections/festival-offers.php'; ?>

<!-- Signature Collections Section -->
<?php require_once 'sections/collections.php'; ?>


<!-- From Ingredients To Celebration Section -->
<?php require_once 'sections/ingredients-process.php'; ?>

<!-- Best Sellers Section -->
<?php require_once 'sections/bestsellers.php'; ?>

<!-- Discover Our Special Collections Section -->
<?php require_once 'sections/special-collections.php'; ?>

<!-- Crispy & Authentic Namkeens Section -->
<?php require_once 'sections/namkeens-gallery.php'; ?>

<!-- Box of Joy Section -->
<?php require_once 'sections/box-of-joy.php'; ?>

<!-- Combo Offers Section -->
<?php require_once 'sections/combo-offers.php'; ?>




<!-- Multi-Banner Promotional Slider Section -->
<?php require_once 'sections/home-offers-slider.php'; ?>



<!-- ═══════════════════════════════════════════════════ -->

<!-- Testimonials Section -->
<?php require_once 'sections/testimonials.php'; ?>
<!-- Empowered by Women Section -->

<?php require_once 'sections/empower-women.php'; ?>
<!-- Latest News Updates Section -->
<?php require_once 'sections/latest-news.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/latest-news.css?v=<?php echo SITE_VERSION; ?>">


<!-- Franchise Banner Section -->
<?php require_once 'sections/franchise-banner.php'; ?>

<!-- Amingad Heritage Section -->
<?php require_once 'sections/amingad-heritage.php'; ?>

<script>
    (function () {
        'use strict';

        document.addEventListener('DOMContentLoaded', () => {

            // ── Intersection Observer for Reveal Animations ──────────
            const revealEls = document.querySelectorAll('.js-reveal');
            const stepEls = document.querySelectorAll('.js-step');

            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15 });

            const stepObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                        stepObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.2 });

            revealEls.forEach(el => revealObserver.observe(el));
            stepEls.forEach(el => stepObserver.observe(el));

            revealEls.forEach(el => revealObserver.observe(el));
            stepEls.forEach(el => stepObserver.observe(el));

            // ── Swiper initializations are now centralized in main.js ──────────────────
            // This prevents double initializations and conflicting settings.


        });
    })();
</script>



<!-- Promotional Popup (2 min delay) -->
<?php require_once 'sections/promo-popup.php'; ?>

<!-- Styles for specific sections -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/promo-popup.css?v=<?php echo SITE_VERSION; ?>">

<!-- Scripts for specific sections -->
<script src="<?php echo BASE_URL; ?>assets/js/sections/promo-popup.js?v=<?php echo SITE_VERSION; ?>"></script>
<!-- Interaction Logic for Tradition/Features -->
<script src="<?php echo BASE_URL; ?>assets/js/sections/tradition-taste.js"></script>

<!-- Availability Banner -->
<?php require_once 'sections/availability-banner.php'; ?>

<?php require_once 'includes/footer.php'; ?>