<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/box-of-joy.php
 * Description: Premium "Create Your Own Box of Joy" section
 * =============================================================
 */
?>

<section class="c-box-of-joy py-5">
    <!-- Subtle Side Backgrounds -->
    <img src="assets/images/homepage/expore-bg (1).png" alt="" class="c-box-of-joy__bg-side c-box-of-joy__bg-side--left">
    <img src="assets/images/homepage/expore-bg (2).png" alt="" class="c-box-of-joy__bg-side c-box-of-joy__bg-side--right">
    
    <div class="container">
        
        <!-- Section Header -->
        <header class="mb-5 js-reveal text-center">
            <div class="c-box-of-joy__header-inner">
                <img src="assets/images/icon/explorelogo.png" alt="" class="c-header-icon"/>
                <h2 class="c-box-of-joy__title fw-bold">
                    <span class="c-header-text">Create Your Own Box of Joy</span>
                </h2>
                <img src="assets/images/icon/explorelogo.png" alt="" class="c-header-icon"/>
            </div>
        </header>

        <div class="row g-5 align-items-center">
            
            <!-- Left Column: Visual Showcase -->
            <div class="col-lg-6 js-reveal ">
                <div class="c-box-of-joy__image-container">
                    <!-- Image Wrapper with subtle inner shadow/border -->
                    <div class="c-box-of-joy__img-frame shadow-sm">
                        <img src="assets/images/homepage/gift-box.png" 
                             alt="Premium Gift Box" 
                             class="img-fluid c-box-of-joy__main-img w-100">
                    </div>

                    <!-- Overlay Label -->
                    <div class="c-box-of-joy__overlay shadow">
                        <span class="c-box-of-joy__overlay-label">Artisanal Craftsmanship</span>
                        <h3 class="c-box-of-joy__overlay-title">Hand-Packed with Love</h3>
                    </div>
                </div>
            </div>

            <!-- Right Column: Step-by-Step Guide -->
            <div class="col-lg-6">
                <!-- Converted to Flex/Grid for mobile uniformity -->
                <div class="c-box-of-joy__steps-grid row g-3 g-md-4">
                    
                    <!-- Step 1 -->
                    <div class="col-6 col-lg-12 js-step">
                        <div class="c-box-step d-flex flex-column flex-lg-row align-items-center align-items-lg-start gap-3 h-100">
                            <div class="c-box-step__icon-wrap">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div class="c-box-step__content text-center text-lg-start">
                                <h4 class="c-box-step__title">Choose your Box</h4>
                                <p class="c-box-step__text">
                                    Select from our range of handcrafted premium boxes.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="col-6 col-lg-12 js-step">
                        <div class="c-box-step d-flex flex-column flex-lg-row align-items-center align-items-lg-start gap-3 h-100">
                            <div class="c-box-step__icon-wrap">
                                <i class="bi bi-circle-square"></i>
                            </div>
                            <div class="c-box-step__content text-center text-lg-start">
                                <h4 class="c-box-step__title">Add favorite Sweets</h4>
                                <p class="c-box-step__text">
                                    Pick from our selection of artisanal mithai.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="col-6 col-lg-12 js-step">
                        <div class="c-box-step d-flex flex-column flex-lg-row align-items-center align-items-lg-start gap-3 h-100">
                            <div class="c-box-step__icon-wrap">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div class="c-box-step__content text-center text-lg-start">
                                <h4 class="c-box-step__title">Personal message</h4>
                                <p class="c-box-step__text">
                                    Include a handwritten note for your loved ones.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="col-6 col-lg-12 js-step">
                        <div class="c-box-step d-flex flex-column flex-lg-row align-items-center align-items-lg-start gap-3 h-100">
                            <div class="c-box-step__icon-wrap">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="c-box-step__content text-center text-lg-start">
                                <h4 class="c-box-step__title">Schedule Delivery</h4>
                                <p class="c-box-step__text">
                                    Choose a date for the perfect surprise.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- CTA Button -->
                <div class="mt-4 pt-2">
                    <a href="cart.php" class="btn c-box-of-joy__cta">
                        Customized Gift Box
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>