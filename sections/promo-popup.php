<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/promo-popup.php
 * Description: Premium promotional popup with delay and session logic
 * =============================================================
 */
?>

<div class="c-promo-popup" id="promoPopup" role="dialog" aria-modal="true" aria-labelledby="promoPopupTitle">
    <div class="c-promo-popup__overlay" id="promoPopupOverlay"></div>
    <div class="c-promo-popup__container">
        <!-- Close Button -->
        <button class="c-promo-popup__close" id="closePromoPopup" aria-label="Close promotion">
            <i class="bi bi-x"></i>
        </button>

        <!-- Main Banner Image -->
        <div class="c-promo-popup__image-wrap">
            <img src="<?php echo BASE_URL; ?>assets/images/promotions/offer-popup.png" alt="Special Offer - Flat 10% OFF" class="c-promo-popup__image">
        </div>

        <!-- Content Area -->
        <div class="c-promo-popup__content">
            <div class="c-promo-popup__actions text-center">
                <a href="karadant.php" class="btn c-promo-popup__btn">SHOP NOW</a>
            </div>
        </div>
    </div>
</div>
