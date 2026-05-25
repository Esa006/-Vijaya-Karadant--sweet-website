<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/home-offers-slider.php
 * Description: Promotional slider for the Home Page
 * =============================================================
 */
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/countdown-timer.css?v=<?php echo SITE_VERSION; ?>">

<section class="c-home-offers-slider py-5">
    <div class="container">
        
        <!-- Festival Countdown Timer -->
        <div class="c-countdown text-center">
            <h2 class="c-countdown__title">
                <div class="c-header-flourish-wrap d-none d-md-flex">
                    <img src="assets/images/icon/Frame 2147228516.png" alt="" class="c-header-flourish-img"/>
                    <img src="assets/images/icon/offer (1).png" alt="" class="c-header-icon"/>
                </div>
                <span class="c-header-text">Festival's Offers</span>
                <div class="c-header-flourish-wrap d-none d-md-flex">
                    <img src="assets/images/icon/offer (1).png" alt="" class="c-header-icon"/>
                    <img src="assets/images/icon/Frame 2147228517.png" alt="" class="c-header-flourish-img"/>
                </div>
            </h2>
            <p class="c-countdown__subtitle">
                <i class="bi bi-heart-fill text-danger mx-1"></i> Crafted for Your Festive Moments <i class="bi bi-heart-fill text-danger mx-2"></i>
            </p>
            <div class="c-countdown__timer row g-3 justify-content-center" role="timer" aria-live="polite">
                <div class="col-6 col-md-3">
                    <div class="c-timer-block">
                        <span class="c-timer-block__icon" aria-hidden="true"><i class="bi bi-calendar2-week-fill"></i></span>
                        <div class="c-timer-block__sep"></div>
                        <div class="c-timer-block__meta">
                            <span class="c-timer-block__value" id="timer-days">2</span>
                            <span class="c-timer-block__label">DAYS</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="c-timer-block">
                        <span class="c-timer-block__icon" aria-hidden="true"><i class="bi bi-clock-fill"></i></span>
                        <div class="c-timer-block__sep"></div>
                        <div class="c-timer-block__meta">
                            <span class="c-timer-block__value" id="timer-hours">15</span>
                            <span class="c-timer-block__label">HRS</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="c-timer-block">
                        <span class="c-timer-block__icon" aria-hidden="true"><i class="bi bi-hourglass-split"></i></span>
                        <div class="c-timer-block__sep"></div>
                        <div class="c-timer-block__meta">
                            <span class="c-timer-block__value" id="timer-mins">40</span>
                            <span class="c-timer-block__label">MINS</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="c-timer-block">
                        <span class="c-timer-block__icon" aria-hidden="true"><i class="bi bi-stopwatch-fill"></i></span>
                        <div class="c-timer-block__sep"></div>
                        <div class="c-timer-block__meta">
                            <span class="c-timer-block__value" id="timer-secs">12</span>
                            <span class="c-timer-block__label">SEC</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Swiper -->
        <div class="swiper homeOffersSwiper">
            <div class="swiper-wrapper">
                
                <!-- Slide 1 -->
                <div class="swiper-slide">
                    <div class="c-home-offers__banner">
                        <img src="assets/images/homepage/home-offer (1).png" alt="Special Offer Corporate Orders" class="img-fluid w-100 rounded-4 shadow-lg">
                        <div class="c-home-offers__content">
                            <!-- Floating interactive area if needed, but image already has text -->
                            <a href="contact.php" class="c-home-offers__overlay-link"></a>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="swiper-slide">
                    <div class="c-home-offers__banner">
                        <img src="assets/images/homepage/home-offer.png" alt="Share Sweet Moments" class="img-fluid w-100 rounded-4 shadow-lg">
                        <div class="c-home-offers__content">
                            <a href="catalog.php" class="c-home-offers__overlay-link"></a>
                        </div>
                    </div>
                </div>

            </div>

            
            <!-- Optional Navigation -->
            <div class="swiper-button-next swiper-nav-custom"></div>
            <div class="swiper-button-prev swiper-nav-custom"></div>
            
            <!-- Pagination -->
            <div class="swiper-pagination offers-pagination"></div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/home-offers-slider.css?v=<?php echo SITE_VERSION; ?>">

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper !== 'undefined') {
        new Swiper('.homeOffersSwiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.offers-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
        });
    }
});
</script>
<script src="<?php echo BASE_URL; ?>assets/js/sections/countdown-timer.js?v=<?php echo SITE_VERSION; ?>"></script>
