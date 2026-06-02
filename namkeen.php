<?php
/**
 * Sweets Website
 * =============================================================
 * File: namkeen.php
 * Description: Namkeen Collection landing page
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

// Initialize Service
$productService = new ProductService();
$namkeens = $productService->getProductsByCategory('namkeen');

// Header Meta overrides (optional)
$seoContext = [
    'title' => 'Crispy & Authentic Namkeens | ' . SITE_NAME,
    'description' => 'Savor our premium traditional namkeens. Crispy, spicy, and perfectly crafted using heritage recipes for the ultimate crunch.',
    'canonical' => BASE_URL . 'namkeen.php',
    'type' => 'website'
];

require_once 'includes/header.php';
?>

<!-- Custom Page Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/namkeen-hero.css?v=<?php echo SITE_VERSION; ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/namkeen-page.css?v=<?php echo SITE_VERSION; ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/special-collections.css?v=<?php echo SITE_VERSION; ?>">

<main class="page-karadant page-namkeen">

    <!-- Hero Section -->
    <?php require_once 'sections/namkeen-hero.php'; ?>

    <!-- How It Works (Animated Timeline) -->
    <?php require_once 'sections/global-how-it-works.php'; ?>

    <!-- Namkeen Gallery Section -->
    <?php $namkeensSectionTitle = 'Our Signature Collection'; ?>
    <?php require_once 'sections/namkeens-gallery.php'; ?>

    <!-- Art of Crafting Perfect Crunch -->
    <?php require_once 'sections/namkeen-art.php'; ?>

    <!-- Why Choose Our Namkeen Section -->
    <?php require_once 'sections/namkeen-why.php'; ?>
    <!-- Discover Our Special Collections Section -->
    <!-- Gift Boxes Section -->
    <?php require_once 'sections/gift-boxes.php'; ?>

    <!-- Share the Joy of Crunch CTA Section -->
    <?php require_once 'sections/namkeen-cta.php'; ?>


</main>

<?php require_once 'includes/footer.php'; ?>
