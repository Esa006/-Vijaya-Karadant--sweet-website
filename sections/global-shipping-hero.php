<?php
/**
 * Sweets Website
 * =============================================================
 * File: global-shipping-hero.php
 * Description: Hero section for the Global Shipping page
 * =============================================================
 */
?>

<section class="c-global-shipping-hero py-5 u-bg-warm">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <!-- Accurate Title -->
                <h1 class="c-hero-shipping__title mb-2">
                    Now Shipping <br>
                    <span class="text-highlight">Worldwide!</span>
                </h1>
                
                <!-- Delivery Subtitle -->
                <p class="c-hero-shipping__delivery mb-4">Delivery within 7 Days</p>
                
                <!-- Action Button -->
                <div class="mb-5">
                    <a href="#international-form" class="btn c-hero-shipping__btn shadow">Buy Now</a>
                </div>
                
                <!-- Promotional Bar -->
                <div class="c-hero-promo">
                    <p class="c-hero-promo__label mb-2">Shop Over 10000 Rupees</p>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="c-hero-promo__pill">
                            Get FLAT 10% OFF on your Order
                        </div>
                        <div class="c-hero-promo__divider d-none d-md-block"></div>
                        <div class="c-hero-promo__code">
                            Use Code <span class="fw-bold">Flat 10</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

 <style>
.c-global-shipping-hero {
    position: relative;
    overflow: hidden;
    background-image: url('assets/images/banners/Component 294 (3).png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 520px;
    padding: 80px 0 !important;
    display: flex;
    align-items: center;
}
.c-global-shipping-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}
.c-global-shipping-hero .container {
    position: relative;
    z-index: 10;
}


/* Accurate Typography */
.c-hero-shipping__title {
    font-family: var(--ff-heading, serif);
    font-size: clamp(1rem, 4vw, 4.2rem) !important;
    font-weight: 800;
    color: #4A2A19; /* Deep Brownish Maroon */
    line-height: 1.1;
    margin-bottom: 0.5rem;
}
.c-hero-shipping__title .text-highlight {
    color: #d67a18 !important; /* Rich Orange Gold */
}

.c-hero-shipping__delivery {
    color: #4A2A19 !important;
    font-weight: 700;
    font-size: 1.5rem;
    font-family: var(--ff-body, sans-serif);
}

/* Button Gradient */
.c-hero-shipping__btn {
    background: linear-gradient(90deg, #7b1d1d 0%, #d67a18 100%);
    color: #fff !important;
    font-weight: 700;
    padding: 14px 70px;
    border-radius: 8px;
    font-size: 1.15rem;
    border: none;
    transition: all 0.3s ease;
    display: inline-block;
    text-decoration: none;
}
.c-hero-shipping__btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(123, 29, 29, 0.4);
    color: #fff !important;
}

/* Promotional Bar */
.c-hero-promo__label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #333;
    opacity: 0.85;
}
.c-hero-promo__pill {
    background: #fff;
    border: 1.5px solid #d67a18;
    color: #4A2A19;
    padding: 10px 28px;
    border-radius: 40px;
    font-weight: 800;
    font-size: 1.05rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.06);
}
.c-hero-promo__divider {
    width: 1.5px;
    height: 45px;
    background: #4A2A19;
    opacity: 0.4;
}
.c-hero-promo__code {
    font-size: 1rem;
    color: #333;
    font-weight: 500;
}
.c-hero-promo__code span {
    color: #7b1d1d;
    font-weight: 800;
}

@media (max-width: 1024px) {
    .c-global-shipping-hero {
        min-height: 420px;
        padding: 60px 0;
                height: 243px;
    }
    .c-hero-shipping__title {
        font-size: clamp(2rem, 4vw, 3rem);
    }
    .c-hero-shipping__delivery {
        font-size: 1.25rem;
    }
    .c-hero-shipping__btn {
        padding: 12px 50px;
        font-size: 1rem;
    }
    .c-hero-promo__pill {
        padding: 8px 20px;
        font-size: 0.95rem;
    }
    .d-flex.align-items-center.gap-3.flex-wrap{
        gap: 5px !important;
    }
    .col-lg-6{
        padding: 0 !important;
        position: relative;
        left: 57px;
    }
        .c-hero-promo{
            position: relative;
            margin-top: -20px;
        }   
}

@media (max-width: 769px) {
      .col-lg-6 {
        padding: 0 !important;
        position: relative;
        left: 40px;
      }
      .c-hero-promo{
        position: relative;
        margin-top: 20px;
      }
      .c-global-shipping-hero{
        min-height: 250px;
        height: 303px;
      }
      .c-hero-shipping__title {
position: relative;
top: 30px;
}
.c-hero-shipping__delivery {
    position: relative;
    top: 30px;
}
.c-hero-shipping__btn {
    position: relative;
    top: 20px;
}
.c-hero-promo__label {
    position: relative;
    top: -20px;
}
.d-flex.align-items-center.gap-3.flex-wrap {
    position: relative;
    top: -20px;
    left: -20px;
}   
.c-hero-promo__pill {
    font-size: 0.60rem;
}
.c-hero-promo__divider {
    height: 20px;
}
}}
@media (max-width: 576px) {
    .c-global-shipping-hero {
        background-image: url('assets/images/banners/phone-screen-banner/global Shipping (2).png') !important;
        background-size: cover !important;
        background-position: center !important;
        min-height: 172px !important;
        padding: 0 !important;
        display: flex;
        align-items: center;
        background-color: transparent !important;
    }
    .c-global-shipping-hero .container {
        display: none !important;
    }
}

@media (max-width: 425px) {
    .c-global-shipping-hero {
        background-image: url('assets/images/banners/phone-screen-banner/Component 294 (3).png') !important;
        background-size: cover !important;
        background-position: center !important;
        min-height: 172px !important;
        padding: 0 !important;
        display: flex;
        align-items: center;
        background-color: transparent !important;
        height: 165px;
    }
    .c-global-shipping-hero .container {
        display: none !important;
    }
}

@media (max-width: 375px) {
    .c-global-shipping-hero {
        background-image: url('assets/images/banners/phone-screen-banner/Component 294 (3).png') !important;
        min-height: 152px !important;
        height: 112px;
    }
}

@media (max-width: 320px) {
    .c-global-shipping-hero {
        min-height: 102px !important;
    }
}
</style>
