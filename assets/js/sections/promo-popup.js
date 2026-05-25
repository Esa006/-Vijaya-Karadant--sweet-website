/**
 * Sweets Website
 * =============================================================
 * File: promo-popup.js
 * Description: Logic for delayed promotional popup with persistence
 * =============================================================
 */

(function() {
    'use strict';

    const CONFIG = {
        delay: 10000, // 10 seconds
        storageKey: 'vjk_promo_shown_session',
        selectors: {
            popup: '#promoPopup',
            overlay: '#promoPopupOverlay',
            closeBtn: '#closePromoPopup',
            container: '.c-promo-popup__container'
        }
    };

    function initPromoPopup() {
        const popup = document.querySelector(CONFIG.selectors.popup);
        const overlay = document.querySelector(CONFIG.selectors.overlay);
        const closeBtn = document.querySelector(CONFIG.selectors.closeBtn);

        if (!popup) return;

        // Check if already shown in this session
        const hasBeenShown = sessionStorage.getItem(CONFIG.storageKey);

        if (hasBeenShown) {
            console.log('Promo Popup: Already shown in this session.');
            return;
        }

        // Set timeout to show popup
        console.log(`Promo Popup: Timer started. Will appear in ${CONFIG.delay / 1000} seconds.`);
        setTimeout(() => {
            showPopup(popup);
        }, CONFIG.delay);

        // Event listeners
        closeBtn.addEventListener('click', () => hidePopup(popup));
        overlay.addEventListener('click', () => hidePopup(popup));

        // Keyboard support (ESC key)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && popup.classList.contains('is-visible')) {
                hidePopup(popup);
            }
        });
    }

    function showPopup(popup) {
        popup.classList.add('is-visible');
        document.body.classList.add('promo-popup-open');
        
        // Mark as shown immediately in session
        sessionStorage.setItem(CONFIG.storageKey, 'true');
    }

    function hidePopup(popup) {
        popup.classList.remove('is-visible');
        document.body.classList.remove('promo-popup-open');
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPromoPopup);
    } else {
        initPromoPopup();
    }
})();
