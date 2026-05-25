<?php
/**
 * Sweets Website
 * =============================================================
 * File: karadant.php
 * Description: Heritage Collection / Karadant landing page
 * Cleaned, refactored, and modularized.
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

// Initialize Service
$productService = new ProductService();

// Header Meta overrides (optional)
$pageTitle = "Heritage Collection - " . SITE_NAME;

require_once 'includes/header.php';
?>

<!-- Custom Page Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/karadant-page.css?v=<?php echo SITE_VERSION; ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/karadant-art.css?v=<?php echo SITE_VERSION; ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/karadant-why.css?v=<?php echo SITE_VERSION; ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/gift-boxes.css?v=<?php echo SITE_VERSION; ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/special-collections.css?v=<?php echo SITE_VERSION; ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/box-of-joy.css?v=<?php echo SITE_VERSION; ?>">

<main class="page-karadant">

    <!-- Hero Section -->
    <?php require_once 'sections/karadant-hero.php'; ?>
    <!-- Product Catalog Section -->
    <?php require_once 'sections/product-catalog.php'; ?>


    <!-- Art of Crafting Section -->
    <?php require_once 'sections/karadant-art.php'; ?>


    <!-- Why Choose Section -->
    <?php require_once 'sections/karadant-why.php'; ?>
    <!-- Testimonials Section -->
    <?php require_once 'sections/testimonials.php'; ?>


    <!-- Gift Boxes Section -->
    <?php require_once 'sections/gift-boxes.php'; ?>


    <!-- Heritage Story Section -->
    <?php require_once 'sections/heritage-story.php'; ?>




</main>

<!-- Karadant Collection Tab Guide Fix (jQuery Manual Handler) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // 1. Category Pill Filtering (Client-side)
    $(document).on('click', '.js-category-pill', function(e) {
        // If it's a category link in the catalog, we bypass the page reload
        var href = $(this).attr('href');
        if (href && href.indexOf('?category=') !== -1) {
            e.preventDefault();
            
            var urlParams = new URLSearchParams(href.split('?')[1]);
            var category = urlParams.get('category') || 'all';
            
            console.log('[Karadant Guide Fix] Filtering Catalog to:', category);
            
            // UI Update: Active class
            $('.js-category-pill').removeClass('active');
            $(this).addClass('active');
            
            // Item Filtering
            var visibleCount = 0;
            $('.js-catalog-item').each(function() {
                var itemCat = $(this).data('category') || $(this).attr('data-category') || '';
                if (category === 'all' || itemCat === category) {
                    $(this).show().css('opacity', '1');
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });
            
            // Update UI Count
            $('#catalogProductCount').text(visibleCount);
            
            // Trigger scroll reveal
            window.dispatchEvent(new Event('scroll'));
        }
    });

    // 2. Dropdown Toggle (Direct Manual)
    $(document).on('click', '.js-dropdown-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $menu = $(this).next('.js-dropdown-menu');
        if ($menu.length) {
            $('.js-dropdown-menu.show').not($menu).removeClass('show');
            $menu.toggleClass('show');
        }
    });

    // Close menus on outside click
    $(document).on('click', function() {
        $('.js-dropdown-menu.show').removeClass('show');
    });

    console.log('[Karadant Guide Fix] Manual jQuery Handler Initialized.');
});
</script>

<?php require_once 'includes/footer.php'; ?>