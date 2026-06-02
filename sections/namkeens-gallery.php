<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/namkeens-gallery.php
 * Description: High-density Namkeens section matching Best Sellers layout
 * =============================================================
 */

$sectionTitle = $namkeensSectionTitle ?? 'Crispy & Authentic Namkeens';
?>

<section class="c-bestsellers py-5">
    <div class="container">

        <!-- ── Section Header ────────────────────────── -->
        <div class="c-bestsellers__header position-relative">
            <div class="c-bestsellers__title-wrap">

                <img src="<?php echo BASE_URL; ?>assets/images/icon/Vector (2).png" alt=""
                    class="c-bestsellers__title-icon">
                <h2 class="c-bestsellers__title-main"><?php echo htmlspecialchars($sectionTitle); ?></h2>
                <img src="<?php echo BASE_URL; ?>assets/images/icon/Vector (3).png" alt=""
                    class="c-bestsellers__title-icon">

            </div>

            <div class="c-bestsellers__view-all-wrap mt-2 text-center text-lg-end">
                <a href="<?php echo BASE_URL; ?>namkeen.php" class="text-decoration-none text-muted small fw-bold">
                    View all collection
                </a>
            </div>
        </div>

        <!-- ── Product Grid (6-column layout) ────────── -->
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-2 g-md-3" id="namkeensGrid">
            <?php
            $namkeensToDisplay = $namkeens ?? [];
            if (empty($namkeensToDisplay)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No namkeens found.</p>
                </div>
            <?php endif;

            foreach ($namkeensToDisplay as $item): 
                $name = htmlspecialchars($item['name']);
                $slug = htmlspecialchars($item['slug']);
                $priceCurrent = number_format((float)($item['sale_price'] ?? $item['base_price'] ?? 0), 0);
                $oldPriceValue = (float)($item['base_price'] ?? 0);
                $priceOld = ($item['sale_price'] && $oldPriceValue > $item['sale_price']) ? number_format($oldPriceValue, 0) : null;
                $image = htmlspecialchars($item['image_path'] ?? 'assets/images/placeholders/product-placeholder.png');
                $catSlug = htmlspecialchars(strtolower((string)($item['category_slug'] ?? 'namkeen')));
            ?>
                <div class="col c-bestsellers__item"
                    data-category="<?php echo htmlspecialchars($item['category_slug'] ?? 'namkeen'); ?>">
                    <div class="c-product-card-v2 h-100 c-bestseller-card-wrap">

                        <!-- Image + Wishlist -->
                        <div class="c-product-card-v2__img-wrap position-relative overflow-hidden">
                            <button class="c-product-card-v2__heart js-wishlist-toggle" aria-label="Wishlist"
                                data-id="<?php echo md5($item['name']); ?>"
                                data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                data-price="<?php echo htmlspecialchars($item['base_price']); ?>"
                                data-image="<?php echo BASE_URL . $item['image_path']; ?>"
                                data-url="<?php echo BASE_URL; ?>product-detail.php?slug=<?php echo htmlspecialchars($item['slug']); ?>">
                                <i class="bi bi-heart-fill"></i>
                            </button>

                            <a href="<?php echo BASE_URL; ?>product-detail.php?slug=<?php echo htmlspecialchars($item['slug']); ?>"
                                class="d-block w-100 h-100">
                                <img src="<?php echo BASE_URL . $item['image_path']; ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                    class="c-product-card-v2__img c-bestseller-zoom-img img-fluid" loading="lazy">
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
                                <a
                                    href="<?php echo BASE_URL; ?>product-detail.php?slug=<?php echo htmlspecialchars($item['slug']); ?>">
                                    <?php echo htmlspecialchars($item['name']); ?>
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
                                    <span class="c-product-card-v2__price-current current">₹
                                        <?php echo number_format((float) $item['base_price'], 0); ?></span>
                                    <span class="c-product-card-v2__price-old old">₹
                                        <?php echo number_format((float) $item['base_price'] + 80, 0); ?></span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="c-actions--compact">
                                    <a href="<?php echo BASE_URL; ?>cart.php?slug=<?php echo htmlspecialchars($item['slug']); ?>"
                                        class="c-btn-cart--compact">Add to Cart</a>
                                    <a href="<?php echo BASE_URL; ?>cart.php?slug=<?php echo htmlspecialchars($item['slug']); ?>"
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
