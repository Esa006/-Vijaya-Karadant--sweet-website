<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/shop-range.php
 * Description: "Shop our Range" section with promotional cards
 * =============================================================
 */
?>

<section class="c-shop-range py-5">
    <div class="container">
        <div class="text-start mb-5">
            <h2 class="c-shop-range__title u-ff-heading fw-bold">Shop our Range</h2>
        </div>

        <div class="row g-4">
            <!-- Card 1: Gandhagiri Laddu -->
            <div class="col-md-4" data-aos="fade-up">
                <a href="<?php echo BASE_URL; ?>cart.php?slug=gandahagiri-laddu" class="c-range-card d-block" aria-label="Gandhagiri Laddu">
                    <img src="assets/images/cart/hope (1).png" alt="Gandhagiri Laddu" class="c-range-card__img">
                </a>
            </div>

            <!-- Card 2: Authentic Sweets -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <a href="<?php echo BASE_URL; ?>karadant.php" class="c-range-card d-block" aria-label="Authentic Sweets">
                    <img src="assets/images/cart/hope (2).png" alt="Authentic Sweets" class="c-range-card__img">
                </a>
            </div>

            <!-- Card 3: Everyday Gifting -->
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <a href="<?php echo BASE_URL; ?>gifting.php" class="c-range-card d-block" aria-label="Everyday Gifting">
                    <img src="assets/images/cart/hope (3).png" alt="Everyday Gifting" class="c-range-card__img">
                </a>
            </div>
        </div>
    </div>
</section>
