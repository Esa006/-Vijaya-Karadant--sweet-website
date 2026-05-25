<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/responsive-hero.php
 * Description: Specialized Responsive Hero Unit
 * =============================================================
 */
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/responsive-hero.css">

<section class="c-res-hero">
    <div class="c-res-hero__container">
        
        <!-- Hero Content Area -->
        <article class="c-res-hero__content">
            <small class="c-res-hero__tag">100% Natural & Pure</small>
            <h1 class="c-res-hero__title">Made with Pure Ghee</h1>
            <p class="c-res-hero__subtitle">Handcrafted with Love</p>
            
            <div class="c-res-hero__actions">
                <a href="shop.php" class="c-res-hero__btn">Browse Shop</a>
            </div>
        </article>

        <!-- Product Image Area -->
        <div class="c-res-hero__image-wrap">
            <img 
                src="<?php echo BASE_URL; ?>assets/images/homepage/Explore (1).png" 
                alt="Premium Namkeen Bowl" 
                class="c-res-hero__img" 
                loading="eager"
            >
        </div>

    </div>
</section>
