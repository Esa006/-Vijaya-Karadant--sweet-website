<?php
/**
 * Sweets Website - Product Card Component
 * =============================================================
 */

function renderProductCard($product)
{
    $id = (int)($product['id'] ?? 0);
    $name = $product['name'] ?? 'Product';
    $slug = $product['slug'] ?? 'premium-vijaya-karadant';
    $image = $product['image_path'] ?? 'assets/images/placeholder.png';
    $image = trim((string) $image);

    // Fallback for missing/broken images — normalize to forward slashes for is_file()
    $absoluteImagePath = ROOT_PATH . '/' . ltrim(str_replace('\\', '/', $image), '/');
    if ($image === '' || !is_file($absoluteImagePath)) {
        $image = 'assets/images/placeholder.png';
    }
    // Build browser-accessible URL
    $imageUrl = (defined('BASE_URL') ? BASE_URL : '') . ltrim(str_replace('\\', '/', $image), '/');

    $basePrice = $product['base_price'] ?? 0;
    $salePrice = $product['sale_price'] ?? null;
    $normalizedBasePrice = (float)$basePrice;
    $hasSalePrice = $salePrice !== null && $salePrice !== '';
    $normalizedSalePrice = $hasSalePrice ? (float)$salePrice : null;
    $displayPrice = $normalizedSalePrice !== null ? $normalizedSalePrice : $normalizedBasePrice;
    $showOldPrice = $normalizedSalePrice !== null && $normalizedSalePrice > 0 && $normalizedBasePrice > $normalizedSalePrice;
    $rating = $product['rating'] ?? 4.0;
    $reviews = $product['reviews_count'] ?? 160;
    $isBestseller = $product['is_bestseller'] ?? false;
    // Build a smart, category-aware fallback if no description is set
    $rawDesc = trim((string)($product['short_description'] ?? $product['description'] ?? ''));
    if ($rawDesc === '') {
        $catSlug = strtolower((string)($product['category_slug'] ?? $product['category_name'] ?? ''));
        if (strpos($catSlug, 'karadant') !== false) {
            $rawDesc = 'Traditional Karadant made with organic jaggery, premium nuts & pure edible gum.';
        } elseif (strpos($catSlug, 'laddu') !== false) {
            $rawDesc = 'Handcrafted laddu made with pure ghee and wholesome traditional ingredients.';
        } elseif (strpos($catSlug, 'namkeen') !== false) {
            $rawDesc = 'Crispy, flavourful namkeen crafted with signature house spices and fresh ingredients.';
        } elseif (strpos($catSlug, 'combo') !== false) {
            $rawDesc = 'A curated combo of our finest sweets and snacks, perfect for gifting.';
        } elseif (strpos($catSlug, 'gift') !== false) {
            $rawDesc = 'A premium gift box filled with handcrafted traditional sweets and snacks.';
        } else {
            $rawDesc = 'Handcrafted with love using traditional recipes and the finest ingredients.';
        }
    }
    // Truncate to 80 chars to keep card heights consistent across the grid
    $shortDesc = mb_strlen($rawDesc) > 80 ? mb_substr($rawDesc, 0, 77) . '…' : $rawDesc;
    $detailUrl = 'product-detail.php?slug=' . urlencode((string)$slug);
    
    // Stock Check
    $stockQty = (int)($product['stock_quantity'] ?? 0);
    $isOutOfStock = ($stockQty <= 0);
    $cardClass = $isOutOfStock ? 'c-product-card-premium--out-of-stock' : '';
    ?>
    <div class="c-product-card-premium d-flex flex-column h-100 js-product-card <?php echo $cardClass; ?>" data-product-url="<?php echo htmlspecialchars($detailUrl); ?>" data-slug="<?php echo htmlspecialchars($slug); ?>">
        <!-- Badge & Wishlist -->
        <div class="c-product-card-premium__top-actions">
            <?php if ($isOutOfStock): ?>
                <span class="c-badge c-badge--out-of-stock">Out of Stock</span>
            <?php elseif ($isBestseller): ?>
                <span class="c-badge c-badge--bestseller">Best Seller</span>
            <?php endif; ?>
            <button class="c-wishlist-btn js-wishlist-toggle" data-id="<?php echo md5($name); ?>"
                data-name="<?php echo htmlspecialchars($name); ?>" data-slug="<?php echo htmlspecialchars($slug); ?>"
                data-price="<?php echo $salePrice ?? $basePrice; ?>" data-image="<?php echo htmlspecialchars($image); ?>"
                data-url="<?php echo htmlspecialchars($detailUrl); ?>" aria-label="Add to wishlist">
                <i class="bi bi-heart"></i>
            </button>
        </div>

        <!-- Product Image -->
        <div class="c-product-card-premium__image-wrap">
            <a href="<?php echo htmlspecialchars($detailUrl); ?>" class="d-block h-100 w-100 js-card-nav-link" style="position: relative; z-index: 1;">
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($name); ?>" class="c-product-card-premium__image"
                    loading="lazy">
            </a>
        </div>

        <!-- Product Info -->
        <div class="c-product-card-premium__content d-flex flex-column flex-grow-1">
            <!-- Rating -->
            <div class="c-product-card-premium__rating-row">
                <span class="c-product-card-premium__rating-score"><?php echo number_format($rating, 1); ?></span>
                <span class="c-product-card-premium__rating-label">Very Good</span>
                <span class="c-product-card-premium__rating-count">(<?php echo $reviews; ?>)</span>
            </div>

            <div class="c-product-card-premium__stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi bi-star-fill <?php echo ($i <= $rating) ? 'active' : ''; ?>"></i>
                <?php endfor; ?>
            </div>

            <!-- Title -->
            <h3 class="c-product-card-premium__title">
                <a href="<?php echo htmlspecialchars($detailUrl); ?>" class="text-decoration-none text-dark js-card-nav-link">
                    <?php echo htmlspecialchars($name); ?>
                </a>
            </h3>
            <p class="c-product-card-premium__desc">
                <?php echo htmlspecialchars($shortDesc); ?>
            </p>

            <!-- Weight Selection -->
            <div class="c-product-card-premium__weight mt-auto">
                <div class="c-product-card-premium__weight-selector d-none d-sm-flex">
                    <button class="weight-btn active" data-weight="250g">250g</button>
                    <button class="weight-btn" data-weight="500g">500g</button>
                    <button class="weight-btn" data-weight="1kg">1kg</button>
                </div>
                <div class="c-product-card-premium__weight-dropdown d-block d-sm-none">
                    <select class="form-select form-select-sm weight-select">
                        <option value="250g" selected>250g</option>
                        <option value="500g">500g</option>
                        <option value="1kg">1kg</option>
                    </select>
                </div>
            </div>

            <!-- Price -->
            <div class="c-product-card-premium__price-row">
                <span class="c-product-card-premium__price-current">₹<?php echo number_format($displayPrice, 2); ?></span>
                <?php if ($showOldPrice): ?>
                    <span class="c-product-card-premium__price-old">₹<?php echo number_format($normalizedBasePrice, 2); ?></span>
                <?php endif; ?>
            </div>

            <!-- CTA Buttons -->
            <div class="c-product-card-premium__actions">
                <?php if ($isOutOfStock): ?>
                    <button class="btn btn-disabled w-100" disabled>Currently Unavailable</button>
                <?php else: ?>
                    <a href="cart.php?slug=<?php echo htmlspecialchars($slug); ?>"
                        class="btn btn-cart js-card-cta">Add to Cart</a>
                    <a href="cart.php?slug=<?php echo htmlspecialchars($slug); ?>&action=buy_now"
                        class="btn btn-book js-card-cta">Buy Now</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
?>
