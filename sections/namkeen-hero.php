<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/namkeen-hero.php
 * Description: Namkeen Collection hero section
 * =============================================================
 */
?>

<section class="nh-hero position-relative" aria-labelledby="hero-title">
  <div class="container">
    <div class="row align-items-center nh-hero__row">
      <!-- Content Column -->
      <div class="col-12 col-lg-6 position-relative" style="z-index: 1;">
        <div class="nh-hero__content pe-lg-4">
          <h1 class="nh-hero__title mb-3 ani-mask-slant" id="hero-title">
            Our Signature Namkeen Collection
          </h1>

          <p class="nh-hero__desc mb-4">
            Enjoy the crispy, crunchy taste of traditional Indian snacks.
            Handcrafted using age-old recipes and the finest ingredients for
            that perfect afternoon tea companion.
          </p>

          <div class="nh-hero__actions d-flex flex-wrap gap-2 mb-4">
            <a href="#namkeens" class="nh-btn nh-btn--primary">Buy Now</a>
            <a href="#namkeens" class="nh-btn nh-btn--secondary">Explore Collection</a>
          </div>

          <div class="nh-proof d-flex align-items-center gap-3 flex-wrap" aria-label="Customer rating">
            <div class="nh-avatars d-flex align-items-center ps-1" aria-hidden="true">
              <div class="c-avatar-group">
                <div class="c-avatar c-avatar--1"></div>
                <div class="c-avatar c-avatar--2"></div>
                <div class="c-avatar c-avatar--3"></div>
              </div>
            </div>
            <p class="nh-proof__text m-0 d-flex align-items-center gap-2 flex-wrap fw-semibold"
              style="font-size: .92rem; color: #7a5c4b; font-family: Inter, system-ui, sans-serif;">
              <span class="nh-proof__rating text-primary fw-bolder" style="color: var(--brand) !important;">4.9/5</span>
              <span>from 2,000+ happy customers</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Empty right column to keep spacing consistent over the background image -->
      <div class="col-12 col-lg-6 nh-hero__art" aria-hidden="true"></div>
    </div>
  </div>
</section>