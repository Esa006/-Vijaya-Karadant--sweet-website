<?php
/**
 * Sweets Website
 * =============================================================
 * File: product-card.php  (PHP Component)
 * Description: Reusable product card component for best sellers
 * Usage: include ROOT_PATH . '/components/product-card.php';
 * Variables required: $product (array with keys: name, image_path, short_description, category_slug)
 * Author: Sweets Website Team
 * Version: 1.0.0
 * =============================================================
 */

// Safety check — must be called with a valid $product array
if (empty($product)) return;
?>
<div class="c-product-card-v2" data-category="<?php echo htmlspecialchars($product['category_slug'] ?? ''); ?>">
    <div class="c-product-card-v2__img-wrap">
        <span class="c-product-card-v2__badge">Best Seller</span>
        <button class="c-product-card-v2__heart" aria-label="Add to Wishlist" data-id="<?php echo (int)($product['id'] ?? 0); ?>">
            <i class="bi bi-heart-fill"></i>
        </button>
        <img
            src="<?php echo htmlspecialchars($product['image_path'] ?? ''); ?>"
            alt="<?php echo htmlspecialchars($product['name'] ?? ''); ?>"
            class="c-product-card-v2__img"
            loading="lazy">
    </div>
    <div class="c-product-card-v2__body">
        <div class="c-product-card-v2__rating">
            <span class="c-product-card-v2__score">4.0</span>
            <span class="c-product-card-v2__label">Very Good</span>
            <span class="c-product-card-v2__label">160 reviews</span>
        </div>
        <div class="c-product-card-v2__stars">★★★★★</div>
        <h3 class="c-product-card-v2__title"><?php echo htmlspecialchars($product['name'] ?? ''); ?></h3>
        <p class="c-product-card-v2__desc"><?php echo htmlspecialchars($product['short_description'] ?? ''); ?></p>
        <div class="c-product-card-v2__weights">
            <button class="c-weight-btn active">250g</button>
            <button class="c-weight-btn">500g</button>
            <button class="c-weight-btn">1kg</button>
        </div>
        <div class="c-product-card-v2__actions">
            <button class="c-btn-cart">Add to Cart</button>
            <button class="c-btn-book">Book Now</button>
        </div>
    </div>
</div>
