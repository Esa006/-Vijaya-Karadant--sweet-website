<?php
/**
 * Sweets Website
 * =============================================================
 * File: global-shipping.php
 * Description: Global Shipping landing page
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

// Initialize Service
$productService = new ProductService();
$featuredProducts = $productService->getFeaturedProducts(8);

// Header Meta overrides
$seoContext = [
    'title' => 'Global Shipping - Worldwide Delivery | ' . SITE_NAME,
    'description' => 'We ship our authentic Gokak Karadant and traditional sweets worldwide. Fast, secure, and fresh international delivery directly from India.',
    'canonical' => BASE_URL . 'global-shipping.php',
    'type' => 'website'
];

require_once 'includes/header.php';
?>

<!-- Custom Page Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/karadant-page.css?v=<?php echo SITE_VERSION; ?>">

<main class="page-global-shipping">
    
    <!-- Global Shipping Hero -->
    <?php require_once 'sections/global-shipping-hero.php'; ?>

    <!-- How It Works (Animated Timeline) -->
    <?php require_once 'sections/global-how-it-works.php'; ?>

   
    <!-- Our Karadant Collection -->
    <section class="py-5">
        <div class="container">
            <div class="mb-4">
                <h2 class="fw-bold m-0" style="color: #6C2C23; font-family: 'Quando', serif; font-size: clamp(2rem, 3.5vw, 2.8rem);">Our Karadant Collection</h2>
            </div>

            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-2 g-md-3">
                <?php
                $targetSlugs = [
                    'premium-vijaya-karadant',
                    'tilkut-vijaya-karadant',
                    'supreme-vijaya-karadant',
                    'raga-anjeer-karadant',
                    'premium-dink-laddu',
                    'mawa-vijaya-karadant',
                    'classic-vijaya-karadant',
                    'regal-anjeer-karadant'
                ];

                $karadantCollection = [];
                foreach ($targetSlugs as $slug) {
                    $prod = $productService->getProductBySlug($slug);
                    if ($prod) {
                        $price = !empty($prod['sale_price']) ? floatval($prod['sale_price']) : floatval($prod['base_price']);
                        $oldPrice = !empty($prod['sale_price']) ? floatval($prod['base_price']) : 0;
                        
                        $karadantCollection[] = [
                            'name' => $prod['name'],
                            'image' => $prod['image_path'] ?? '',
                            'price' => $price,
                            'old_price' => $oldPrice,
                            'slug' => $prod['slug']
                        ];
                    }
                }

                foreach ($karadantCollection as $product): ?>
                <div class="col">
                    <div class="c-product-card-v2 h-100 c-bestseller-card-wrap">

                        <!-- Image + Wishlist -->
                        <div class="c-product-card-v2__img-wrap position-relative overflow-hidden">
                            <button class="c-product-card-v2__heart js-wishlist-toggle"
                                    data-id="<?php echo md5($product['slug']); ?>"
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-price="<?php echo $product['price']; ?>"
                                    data-image="<?php echo htmlspecialchars($product['image']); ?>"
                                    data-url="<?php echo BASE_URL; ?>cart.php?slug=<?php echo htmlspecialchars($product['slug']); ?>"
                                    aria-label="Wishlist">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                            <a href="<?php echo BASE_URL; ?>cart.php?slug=<?php echo htmlspecialchars($product['slug']); ?>" class="d-block w-100 h-100">
                                <img src="<?php echo BASE_URL . htmlspecialchars($product['image']); ?>"
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="c-product-card-v2__img c-bestseller-zoom-img img-fluid"
                                     loading="lazy">
                            </a>
                        </div>

                        <!-- Card Body (Compact-mode) -->
                        <div class="c-product-card-v2__body--compact">

                            <!-- Rating -->
                            <div class="c-product-card-v2__rating--compact">
                                <span class="score">4.0 ★</span>
                                <span class="label">Very Good</span>
                                <span class="count">(160)</span>
                            </div>

                            <!-- Title -->
                            <h3 class="c-product-card-v2__title--compact px-1">
                                <a href="<?php echo BASE_URL; ?>cart.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>

                            <!-- Weight Content Wrapper -->
                            <div class="weight-content">
                                <!-- Weight Selector -->
                                <div class="c-product-card-v2__weight">
                                    <div class="c-weight-selector--compact">
                                        <button class="c-weight-btn--compact active" data-weight="250g">250g</button>
                                        <button class="c-weight-btn--compact" data-weight="500g">500g</button>
                                        <button class="c-weight-btn--compact" data-weight="1kg">1kg</button>
                                    </div>
                                </div>

                                <!-- Price -->
                                <div class="c-price--compact">
                                    <span class="c-product-card-v2__price-current current">₹ <?php echo number_format($product['price']); ?></span>
                                    <span class="c-product-card-v2__price-old old">₹ <?php echo number_format($product['old_price']); ?></span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="c-actions--compact">
                                    <a href="<?php echo BASE_URL; ?>cart.php?slug=<?php echo htmlspecialchars($product['slug']); ?>" 
                                       class="c-btn-cart--compact">Add to Cart</a>
                                    <a href="<?php echo BASE_URL; ?>cart.php?slug=<?php echo htmlspecialchars($product['slug']); ?>" 
                                       class="c-btn-book--compact">Buy Now</a>
                                </div>

                            </div>
                        </div><!-- /body -->
                    </div><!-- /card -->
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <!-- Global Customer Stories Section -->
    <?php require_once 'sections/global-customer-stories.php'; ?>

    <!-- International Customization Form -->
    <?php require_once 'sections/international-form.php'; ?>

    <!-- Perfect Karadant Gift Packs -->
    <?php require_once 'sections/gift-boxes.php'; ?>
  <!-- Heritage Story Section -->
    <?php require_once 'sections/heritage-story.php'; ?>
     <!-- Important Points -->
    <?php require_once 'sections/global-important-points.php'; ?>

</main>
  

<?php require_once 'includes/footer.php'; ?>
