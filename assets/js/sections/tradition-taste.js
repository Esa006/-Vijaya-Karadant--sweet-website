/**
 * Sweets Website
 * =============================================================
 * File: tradition-taste.js
 * Description: SweetAlert2 popup logic for brand features
 * =============================================================
 */

(function () {
    'use strict';

    function initTraditionPopups() {
        /**
         * Opens a SweetAlert2 popup with template content
         * @param {string} targetId - The id suffix for the template
         */
        function openPopup(targetId) {
            const template = document.getElementById(`template-${targetId}`);
            if (!template) {
                console.warn(`Tradition Popups: Template "template-${targetId}" not found.`);
                return;
            }

            // Get title from template mapping or data attribute
            const titles = {
                'tradition': 'Tradition You Can Taste',
                'recipes': 'Authentic Recipes',
                'ingredients': 'Premium Ingredients',
                'gifting': 'Elegant Gifting'
            };

            const title = titles[targetId] || 'Traditional Excellence';
            
            // Create a temporary container to get the HTML content
            const tempDiv = document.createElement('div');
            tempDiv.appendChild(template.content.cloneNode(true));
            const htmlContent = tempDiv.innerHTML;

            Swal.fire({
                title: title,
                html: htmlContent,
                showCloseButton: true,
                showConfirmButton: false,
                width: 'min(95%, 700px)',
                color: '#4a3728',
                background: '#fdf8f2',
                customClass: {
                    container: 'c-swal-container',
                    popup: 'c-swal-popup--premium',
                    title: 'c-swal-title--heading',
                    closeButton: 'c-swal-close-btn'
                },
                showClass: {
                    popup: 'animate__animated animate__fadeInDown animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp animate__faster'
                }
            });
        }

        // --- Event Delegation for Buttons ---
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.c-tradition-btn, .c-feature-item');
            if (btn) {
                const target = btn.getAttribute('data-modal-target');
                if (target) openPopup(target);
            }
        });

        document.addEventListener('keydown', (e) => {
            const card = e.target.closest('.c-feature-item');
            if (!card) {
                return;
            }

            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const target = card.getAttribute('data-modal-target');
                if (target) {
                    openPopup(target);
                }
            }
        });
    }

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTraditionPopups);
    } else {
        initTraditionPopups();
    }
})();
