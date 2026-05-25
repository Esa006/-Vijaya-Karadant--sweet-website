<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/bestsellers.php
 * Description: Best Sellers section with category filtering & grid
 * =============================================================
 */
?>
<section class="c-bestsellers" id="bestsellers">
    <div class="container">

        <!-- ── Section Header ────────────────────────── -->
        <div class="c-bestsellers__header">
            <div class="c-bestsellers__title-wrap">
                <div class="c-bestsellers__title-line"></div>
                <img src="<?php echo BASE_URL; ?>assets/images/icon/Vector (2).png" alt="Icon"
                    class="c-bestsellers__title-icon">
                <h2 class="c-bestsellers__title-main">Our Best Sellers</h2>
                <img src="<?php echo BASE_URL; ?>assets/images/icon/Vector (3).png" alt="Icon"
                    class="c-bestsellers__title-icon">
                <div class="c-bestsellers__title-line"></div>

            </div>

            <div class="c-bestsellers__subtitle">
                <i class="bi bi-heart-fill c-bestsellers__subtitle-icon"></i>
                Loved by Happy Families
                <i class="bi bi-heart-fill c-bestsellers__subtitle-icon"></i>
            </div>

            <!-- Category Filter Tabs -->
            <div class="c-bestsellers__tabs">
                <button class="c-bestsellers__tab js-bestseller-tab" data-filter="all">ALL COLLECTIONS</button>
                <button class="c-bestsellers__tab js-bestseller-tab active" data-filter="<?php echo strtolower('karadant'); ?>">KARADANT</button>
                <button class="c-bestsellers__tab js-bestseller-tab" data-filter="<?php echo strtolower('laddu'); ?>">LADDU</button>
                <button class="c-bestsellers__tab js-bestseller-tab" data-filter="<?php echo strtolower('namkeen'); ?>">NAMKEEN</button>
                <button class="c-bestsellers__tab js-bestseller-tab" data-filter="<?php echo strtolower('combo'); ?>">Combo</button>
            </div>
        </div>

        <!-- ── Product Grid ──────────────────────────── -->
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-2 g-md-3" id="bsProductGrid">
            <?php
            // Merge: put category-specific arrays FIRST so their effective_category_slug
            // takes precedence in deduplication; bestSellers comes last as a fallback.
            $karadants = $karadants ?? [];
            $laddus    = $laddus    ?? [];
            $namkeens  = $namkeens  ?? [];
            $combos    = $combos    ?? [];
            $bestSellers = $bestSellers ?? [];

            // Normalize combos to match product structure for the grid
            $normalizedCombos = array_map(function($c) {
                return [
                    'id' => 'combo_' . $c['id'],
                    'name' => $c['name'],
                    'slug' => $c['slug'],
                    'sale_price' => $c['final_price'] ?? $c['price'] ?? 0,
                    'base_price' => $c['original_price'] ?? $c['price'] ?? 0,
                    'image_path' => $c['image'] ?? 'assets/images/placeholders/product-placeholder.png',
                    'short_description' => $c['description'] ?? '',
                    'effective_category_slug' => 'combo',
                    'stock_quantity' => (isset($c['stock_status']) && $c['stock_status'] === 'out_of_stock' ? 0 : 10),
                    'is_combo' => true
                ];
            }, $combos);

            $allProductsRaw = array_merge($karadants, $laddus, $namkeens, $normalizedCombos, $bestSellers);

            // Deduplicate by ID – keep the first occurrence (category version wins over generic bestSellers)
            $bestsellersToDisplay = [];
            foreach ($allProductsRaw as $p) {
                if (!isset($bestsellersToDisplay[$p['id']])) {
                    $bestsellersToDisplay[$p['id']] = $p;
                }
            }
            $bestsellersToDisplay = array_values($bestsellersToDisplay);

            if (empty($bestsellersToDisplay)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No products found in Bestsellers.</p>
                </div>
            <?php endif;

            foreach ($bestsellersToDisplay as $product): 
                // Map DB fields to template variables (handling fallbacks)
                $name = htmlspecialchars($product['name']);
                $slug = htmlspecialchars($product['slug']);
                $price = number_format((float)($product['sale_price'] ?? $product['base_price'] ?? 0), 2);
                $oldPriceValue = (float)($product['base_price'] ?? 0);
                $oldPrice = ($product['sale_price'] && $oldPriceValue > $product['sale_price']) ? number_format($oldPriceValue, 2) : null;
                
                $image = htmlspecialchars($product['image_path'] ?? 'assets/images/placeholders/product-placeholder.png');
                // Use effective_category_slug (parent slug for sub-category products).
                // Falls back to category_slug (direct) when already at root level.
                $catSlug = htmlspecialchars(strtolower((string)(
                    $product['effective_category_slug']
                    ?? $product['parent_category_slug']
                    ?? $product['category_slug']
                    ?? ''
                )));
                
                // Badge logic (optional, can be expanded)
                $badgeText = '';
                $badgeClass = '';
                
                // Stock Check
                $stockQty = (int)($product['stock_quantity'] ?? 0);
                $isOutOfStock = ($stockQty <= 0);
                $cardClass = $isOutOfStock ? 'c-product-card-v2--out-of-stock' : '';

                if ($isOutOfStock) {
                    $badgeText = 'Out of Stock';
                    $badgeClass = 'c-product-card-v2__badge--out-of-stock';
                } elseif ($product['sale_price'] && $oldPriceValue > $product['sale_price']) {
                    $discount = round((($oldPriceValue - $product['sale_price']) / $oldPriceValue) * 100);
                    $badgeText = $discount . '% Off';
                    $badgeClass = 'c-product-card-v2__badge--offer';
                }
            ?>
                <div class="col c-bestsellers__item js-bestseller-item"
                    data-category="<?php echo $catSlug; ?>">
                    <div class="c-product-card-v2 h-100 c-bestseller-card-wrap <?php echo $cardClass; ?>">

                        <!-- Image + Heart -->
                        <div class="c-product-card-v2__img-wrap position-relative overflow-hidden">
                            <?php if (!empty($badgeText)): ?>
                                <span class="c-product-card-v2__badge <?php echo $badgeClass; ?>">
                                    <?php echo $badgeText; ?>
                                </span>
                            <?php endif; ?>

                            <button class="c-product-card-v2__heart js-wishlist-toggle" aria-label="Wishlist"
                                data-id="<?php echo $slug; ?>"
                                data-name="<?php echo $name; ?>"
                                data-price="<?php echo $price; ?>"
                                data-image="<?php echo $image; ?>"
                                data-url="<?php echo BASE_URL; ?>product-detail.php?slug=<?php echo $slug; ?>">
                                <i class="bi bi-heart"></i>
                            </button>

                            <a href="<?php echo BASE_URL; ?>product-detail.php?slug=<?php echo $slug; ?>"
                                class="d-block w-100 h-100" style="position: relative; z-index: 5;">
                                <img src="<?php echo BASE_URL . $image; ?>"
                                    alt="<?php echo $name; ?>"
                                    class="c-product-card-v2__img c-bestseller-zoom-img img-fluid" loading="lazy">
                            </a>
                        </div>

                        <!-- Card Body (External CSS) -->
                        <div class="c-product-card-v2__body--compact">

                            <!-- Rating -->
                            <div class="c-product-card-v2__rating--compact">
                                <span class="score">4.0 ★</span>
                                <span class="label">Very Good</span>
                                <span class="count">(160)</span>
                            </div>

                            <!-- Title -->
                            <h3 class="c-product-card-v2__title--compact p-2">
                                <a href="<?php echo BASE_URL; ?>product-detail.php?slug=<?php echo $slug; ?>">
                                    <?php echo $name; ?>
                                </a>
                            </h3>

                            <?php if (!empty($product['short_description'])): ?>
                                <p class="c-product-card-v2__desc--compact text-muted px-2"
                                    style="font-size: 0.75rem; margin-top: -5px; line-height: 1.4;">
                                    <?php echo htmlspecialchars($product['short_description']); ?>
                                </p>
                            <?php endif; ?>

                            <!-- Weight Content Wrapper (35px margin-top in CSS) -->
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
                                    <span class="c-product-card-v2__price-current current">₹<?php echo $price; ?></span>
                                    <?php if ($oldPrice): ?>
                                        <span class="c-product-card-v2__price-old old">₹<?php echo $oldPrice; ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Action Buttons -->
                                <div class="c-actions--compact">
                                    <?php if ($isOutOfStock): ?>
                                        <button type="button" class="btn w-100" style="font-size: 0.75rem; padding: 10px; background-color: #6c757d; color: white; border: none; border-radius: 5px; font-weight: bold;" onclick="openNotifyModal('<?php echo htmlspecialchars((string)($product['id'] ?? '')); ?>', '<?php echo !empty($product['is_combo']) ? 'combo' : 'product'; ?>', '<?php echo htmlspecialchars($name, ENT_QUOTES); ?>')">
                                            <i class="bi bi-bell-fill"></i> Notify Me
                                        </button>
                                    <?php else: ?>
                                        <a href="<?php echo BASE_URL; ?>cart.php?slug=<?php echo $slug; ?>"
                                            class="c-btn-cart--compact">
                                            Add to Cart
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>cart.php?slug=<?php echo $slug; ?>"
                                            class="c-btn-book--compact">
                                            Buy Now
                                        </a>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div><!-- /body -->
                    </div><!-- /card -->
                </div><!-- /item -->
                <?php
            endforeach; ?>
        </div><!-- /row -->

    </div><!-- /container -->
</section>

<script>
function openNotifyModal(productId, productType, productName) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Notify Me',
            html: '<p style="font-size:14px; margin-bottom: 15px;">Get alerted when <b>' + productName + '</b> is back in stock.</p>' +
                  '<input id="notify-email" type="email" class="swal2-input" placeholder="Enter your email address" style="max-width: 100%;">',
            confirmButtonText: 'Submit',
            confirmButtonColor: '#7b1d1d',
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const email = document.getElementById('notify-email').value;
                if (!email || !email.includes('@')) {
                    Swal.showValidationMessage('Please enter a valid email address');
                    return false;
                }
                return email;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const email = result.value;
                
                // Make API request
                const formData = new FormData();
                formData.append('email', email);
                formData.append('product_id', productId);
                formData.append('product_type', productType);
                
                fetch('<?php echo BASE_URL; ?>api/v1/notify_stock.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            confirmButtonColor: '#7b1d1d'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message || 'Something went wrong.',
                            confirmButtonColor: '#7b1d1d'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to connect to the server.',
                        confirmButtonColor: '#7b1d1d'
                    });
                });
            }
        });
    } else {
        alert('SweetAlert is required for this popup.');
    }
}
</script>
