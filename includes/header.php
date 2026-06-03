<?php
/**
 * Sweets Website
 * =============================================================
 * File: header.php
 * Description: Global header with Bootstrap and Meta tags
 * Author: Sweets Website Team
 * Version: 1.0.0
 * =============================================================
 */

require_once ROOT_PATH . '/config/config.php';
$currentPage = basename($_SERVER['PHP_SELF']);
$isCheckoutPage = in_array($currentPage, ['checkout.php', 'checkout-v2.php'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <meta name="google-site-verification" content="sMO9uC3Oa8MTFWRYWEdbIGrOPbOD3J8Pk9ZlZtO5npg" />
   
    
    <?php
    // Dynamic SEO Framework
    $seoTitle = $seoContext['title'] ?? $pageTitle ?? '';
    if (empty($seoTitle) || $seoTitle === SITE_NAME) {
        $filename = basename($_SERVER['PHP_SELF'], '.php');
        if ($filename === 'index' || $filename === 'index_legacy') {
            $seoTitle = SITE_NAME;
        } else {
            $prettyName = ucwords(str_replace(['-', '_'], ' ', $filename));
            $seoTitle = $prettyName . ' | ' . SITE_NAME;
        }
    }
    $seoDesc  = $seoContext['description'] ?? $metaDesc ?? SITE_TAGLINE;
    if (empty(trim($seoDesc))) {
        $seoDesc = 'Order authentic Gokak Karadant, traditional sweets, and premium namkeens online. Handcrafted with organic jaggery and pure cow ghee since 1952.';
    }
    $seoUrl   = $seoContext['canonical'] ?? (BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/'));
    $seoImage = $seoContext['og_image'] ?? (BASE_URL . SITE_LOGO);
    ?>
    <title><?php echo htmlspecialchars($seoTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seoDesc); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($seoUrl); ?>">
    
    <!-- Open Graph Metadata -->
    <meta property="og:title" content="<?php echo htmlspecialchars($seoTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($seoDesc); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($seoImage); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($seoUrl); ?>">
    <meta property="og:type" content="<?php echo $seoContext['type'] ?? 'website'; ?>">
    <meta name="twitter:card" content="summary_large_image">
    
    <?php if (!empty($seoContext['schema'])): ?>
    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    <?php echo json_encode($seoContext['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
    </script>
    <?php endif; ?>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL . SITE_FAVICON; ?>">
    
    <!-- Bootstrap 5.3.0 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- Swiper 11 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Nunito:wght@400;600;700&family=Poppins:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- Critical Path CSS (Parallel Loading) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/base/variables.css?v=<?php echo SITE_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/base/reset.css?v=<?php echo SITE_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/base/typography.css?v=<?php echo SITE_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/layout/grid.css?v=<?php echo SITE_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/layout/header.css?v=<?php echo SITE_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/layout/footer.css?v=<?php echo SITE_VERSION; ?>">
    
    <!-- Component Styles -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/buttons.css?v=<?php echo SITE_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/product-card.css?v=<?php echo SITE_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/text-animations.css?v=<?php echo SITE_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/c-stock-badge.css?v=<?php echo SITE_VERSION; ?>">

    
    <!-- Section Specific Styles (Previously via @import) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/promo-strip.css?v=<?php echo SITE_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/top-bar.css?v=<?php echo SITE_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/bestsellers.css?v=<?php echo SITE_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/product-catalog.css?v=<?php echo SITE_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/availability-banner.css?v=<?php echo SITE_VERSION; ?>">

    <!-- Main Stylesheet (Now Handles fewer shared sections) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/main.css?v=<?php echo SITE_VERSION; ?>">
    
    <script>
        window.BASE_URL = "<?php echo BASE_URL; ?>";
    </script>
    <script src="<?php echo BASE_URL; ?>assets/js/components/text-animations.js?v=<?php echo SITE_VERSION; ?>" defer></script>
</head>
<body class="u-bg-warm">

<?php

// These sections are included here to prevent Flash of Unstyled Content (FOUC)
// and ensure they appear on every page if needed.
require_once ROOT_PATH . '/sections/promo-strip.php';
require_once ROOT_PATH . '/sections/top-bar.php';
?>

<!-- Main Navigation -->
<header class="c-site-header sticky-top bg-white">
    <div class="container">
        <div class="c-header-wrapper">
            <div class="c-logo">
                <a href="<?php echo BASE_URL; ?>">
                    <img src="<?php echo BASE_URL . SITE_LOGO; ?>" alt="logo" class="c-logo__img">
                </a>
            </div>

            <!-- Static Nav Links -->
            <nav class="c-main-nav">
                <ul class="c-main-nav__list">
                    <li class="c-main-nav__item"><a class="c-main-nav__link <?php echo is_active('index.php'); ?>" href="index.php">Home</a></li>
                    <li class="c-main-nav__item"><a class="c-main-nav__link <?php echo is_active('about.php'); ?>" href="about.php">About Us</a></li>
                    <li class="c-main-nav__item"><a class="c-main-nav__link <?php echo is_active('karadant.php'); ?>" href="karadant.php">Karadant</a></li>
                    <li class="c-main-nav__item"><a class="c-main-nav__link <?php echo is_active('namkeen.php'); ?>" href="namkeen.php">Namkeen</a></li>
                    <li class="c-main-nav__item"><a class="c-main-nav__link <?php echo is_active('combos.php'); ?>" href="combos.php">Combos</a></li>
                    <li class="c-main-nav__item"><a class="c-main-nav__link <?php echo is_active('gifting.php'); ?>" href="gifting.php">Gifting</a></li>
                    <li class="c-main-nav__item"><a class="c-main-nav__link <?php echo is_active('global-shipping.php'); ?>" href="global-shipping.php">Global Shipping</a></li>
                    <li class="c-main-nav__item"><a class="c-main-nav__link <?php echo is_active('branches.php'); ?>" href="branches.php">Branches</a></li>
                    <li class="c-main-nav__item"><a class="c-main-nav__link <?php echo is_active('contact.php'); ?>" href="contact.php">Contact Us</a></li>
                </ul>
            </nav>

            <div class="c-header-actions">
                <?php if (!$isCheckoutPage): ?>
                    <div class="c-header-actions__item position-relative">
                        <a href="<?php echo BASE_URL; ?>shopping-cart.php" class="text-dark">
                            <i class="bi bi-cart fs-5"></i>
                            <?php
    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0):
        $cartItemCount = 0;
        foreach ($_SESSION['cart'] as $item)
            $cartItemCount += $item['quantity'];
    ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger js-cart-count" style="font-size: 10px;">
                                    <?php echo $cartItemCount; ?>
                                </span>
                            <?php
    endif; ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="c-header-actions__item d-none d-md-flex">
                    <a href="<?php echo BASE_URL; ?>wishlist.php" class="text-dark">
                        <i class="bi bi-heart fs-5"></i>
                    </a>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="c-header-actions__item c-profile-action">
                        <div class="user-profile-trigger">
                            <img src="<?php echo BASE_URL; ?>assets/images/icon/Group (1).png" alt="Profile" class="c-profile-icon" loading="lazy">
                            <span class="fw-bold d-none d-lg-inline" style="font-size: 0.95rem;"><?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'User')[0]); ?></span>
                            <i class="bi bi-chevron-down d-none d-lg-inline" style="font-size: 0.75rem; color: #888;"></i>
                            <div class="user-dropdown-menu">
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <a href="<?php echo BASE_URL; ?>admin/index.php"><i class="bi bi-speedometer2"></i> Admin Panel</a>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>profile.php"><i class="bi bi-person"></i> My Profile</a>
                                <hr class="divider">
                                <a href="<?php echo BASE_URL; ?>api/v1/logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="c-header-actions__item c-profile-action">
                        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-dark btn-sm rounded-pill px-4 py-2 fw-bold" style="font-size:0.85rem; background: #6B1515; border:none;">
                            <span class="d-none d-sm-inline">Login / Sign up</span>
                            <span class="d-inline d-sm-none">Login</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="c-nav-toggle d-lg-none ms-2" id="mobileMenuToggle" aria-label="Toggle Menu">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Navigation Drawer (Offcanvas style) -->
    <div class="c-mobile-nav" id="mobileNavDrawer">
        <div class="c-mobile-nav__header">
            <div class="c-logo">
                <img src="<?php echo BASE_URL . SITE_LOGO; ?>" alt="logo" class="c-logo__img" style="height: 60px;">
            </div>
            <button class="c-nav-close" id="mobileMenuClose">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="c-mobile-nav__body">
            <ul class="c-mobile-nav__list">
                <li class="c-mobile-nav__item"><a class="c-mobile-nav__link <?php echo is_active('index.php'); ?>" href="index.php">Home</a></li>
                <li class="c-mobile-nav__item"><a class="c-mobile-nav__link <?php echo is_active('about.php'); ?>" href="about.php">About Us</a></li>
                <li class="c-mobile-nav__item"><a class="c-mobile-nav__link <?php echo is_active('karadant.php'); ?>" href="karadant.php">Karadant</a></li>
                <li class="c-mobile-nav__item"><a class="c-mobile-nav__link <?php echo is_active('namkeen.php'); ?>" href="namkeen.php">Namkeen</a></li>
                <li class="c-mobile-nav__item"><a class="c-mobile-nav__link <?php echo is_active('gifting.php'); ?>" href="gifting.php">Gifting</a></li>
                <li class="c-mobile-nav__item"><a class="c-mobile-nav__link <?php echo is_active('global-shipping.php'); ?>" href="global-shipping.php">Global Shipping</a></li>
                <li class="c-mobile-nav__item"><a class="c-mobile-nav__link <?php echo is_active('branches.php'); ?>" href="branches.php">Branches</a></li>
                <li class="c-mobile-nav__item"><a class="c-mobile-nav__link <?php echo is_active('contact.php'); ?>" href="contact.php">Contact Us</a></li>
            </ul>
        </div>
        <div class="c-mobile-nav__footer">
            <div class="c-header-actions justify-content-center">
                <div class="c-header-actions__item">
                    <a href="shopping-cart.php" class="text-dark"><i class="bi bi-cart"></i></a>
                </div>
                <div class="c-header-actions__item">
                    <a href="wishlist.php" class="text-dark"><i class="bi bi-heart"></i></a>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="c-header-actions__item c-profile-action">
                        <div class="user-profile-trigger">
                            <img src="<?php echo BASE_URL; ?>assets/images/icon/Group (1).png" alt="Profile" class="c-profile-icon" loading="lazy">
                            <span class="fw-bold" style="font-size: 0.95rem;"><?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'User')[0]); ?></span>
                            <div class="user-dropdown-menu" style="top: auto; bottom: calc(100% + 8px); right: 0;">
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <a href="<?php echo BASE_URL; ?>admin/index.php"><i class="bi bi-speedometer2"></i> Admin Panel</a>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>profile.php"><i class="bi bi-person"></i> My Profile</a>
                                <hr class="divider">
                                <a href="<?php echo BASE_URL; ?>api/v1/logout.php" class="logout-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="c-header-actions__item c-profile-action">
                        <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-dark btn-sm rounded-pill px-4 py-2 fw-bold" style="font-size:0.85rem; background: #6B1515; border:none;">
                            Login / Sign up
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="c-mobile-nav-overlay" id="mobileNavOverlay"></div>
</header>

<?php if (!$isCheckoutPage): ?>
<div class="c-mobile-commerce-bar d-lg-none">
    <form class="c-mobile-search js-site-search" action="<?php echo BASE_URL; ?>category-products.php" method="get" role="search" data-search-api="<?php echo BASE_URL; ?>api/v1/product-search.php" autocomplete="off">
        <div class="c-mobile-search__field">
            <input type="search" name="search" class="c-mobile-search__input js-site-search-input" placeholder="Search sweets and gifts" aria-label="Search sweets and gifts" aria-expanded="false" aria-controls="siteSearchSuggestions">
            <div class="c-mobile-search__suggestions js-site-search-suggestions" id="siteSearchSuggestions" role="listbox" aria-label="Product suggestions"></div>
        </div>
        <button type="submit" class="c-mobile-search__button" aria-label="Search">
            <i class="bi bi-search"></i>
        </button>
    </form>

    <nav class="c-mobile-quick-nav" aria-label="Quick shop links">
        <a href="<?php echo BASE_URL; ?>category-products.php">Shop By<br>Category</a>
        <a href="<?php echo BASE_URL; ?>combos.php">Deals</a>
        <a href="<?php echo BASE_URL; ?>gifting.php">Gifting</a>
        <a href="<?php echo BASE_URL; ?>namkeen.php">Namkeen</a>
    </nav>

    <div class="c-mobile-location">
        <i class="bi bi-geo-alt"></i>
        <span>Delivering across India - Update location</span>
        <i class="bi bi-chevron-down"></i>
    </div>
</div>
<?php endif; ?>

<style>
/* User Dropdown — pure CSS+JS, no Bootstrap JS dependency */
.user-dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 200px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.14);
    z-index: 9999;
    overflow: hidden;
    animation: dropFadeIn 0.2s ease;
}
.user-dropdown-menu.is-open { display: block; }
@keyframes dropFadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
.user-dropdown-menu a {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 18px; font-size: 0.9rem; color: #333;
    text-decoration: none; transition: background 0.2s;
}
.user-dropdown-menu a:hover { background: #fff5f0; color: #6B1515; }
.user-dropdown-menu .divider { border: none; border-top: 1px solid #f0e6e6; margin: 4px 0; }
.user-dropdown-menu a.logout-link { color: #C0392B; }
.user-dropdown-menu a.logout-link:hover { background: #fff0f0; }
.user-profile-trigger { position: relative; cursor: pointer; display: flex; align-items: center; gap: 8px; }
</style>

<script>
(function() {
    document.addEventListener('DOMContentLoaded', function () {
        var triggers = document.querySelectorAll('.user-profile-trigger');

        triggers.forEach(function(trigger) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var menu = trigger.querySelector('.user-dropdown-menu');
                if (!menu) return;
                var isOpen = menu.classList.contains('is-open');
                document.querySelectorAll('.user-dropdown-menu.is-open').forEach(function(m) { m.classList.remove('is-open'); });
                if (!isOpen) menu.classList.add('is-open');
            });

            var menu = trigger.querySelector('.user-dropdown-menu');
            if (menu) {
                menu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-profile-trigger')) {
                document.querySelectorAll('.user-dropdown-menu.is-open').forEach(function(m) { m.classList.remove('is-open'); });
            }
        });
    });
})();
</script>
