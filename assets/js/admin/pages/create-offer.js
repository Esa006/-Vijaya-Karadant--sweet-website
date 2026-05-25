/**
 * Sweets Website
 * =============================================================
 * File: create-offer.js
 * Description: Client-side logic for real-time offer preview
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', function () {
    // FORM INPUTS
    const offerNameInput = document.getElementById('offerName');
    const couponCodeInput = document.getElementById('couponCode');
    const discountValueInput = document.getElementById('discountValue');
    const discountTypeInput = document.getElementById('discountType');
    const minOrderInput = document.getElementById('minOrderValue');
    const maxDiscountInput = document.getElementById('maxDiscount');
    const startDateInput = document.getElementById('startDate');
    const expiryDateInput = document.getElementById('expiryDate');
    const usageLimitInput = document.getElementById('totalUsageLimit');
    const perUserLimitInput = document.getElementById('perUserLimit');

    // PREVIEW ELEMENTS
    const previewBannerTitle = document.getElementById('previewBannerTitle');
    const previewBannerDiscount = document.getElementById('previewBannerDiscount');
    const previewCouponCode = document.getElementById('previewCouponCode');
    const previewSummaryTitle = document.getElementById('previewSummaryTitle');
    const previewSummaryDesc = document.getElementById('previewSummaryDesc');
    const previewStatUsage = document.getElementById('previewStatUsage');
    const previewStatPerUser = document.getElementById('previewStatPerUser');
    const previewStatStart = document.getElementById('previewStatStart');
    const previewStatEnd = document.getElementById('previewStatEnd');

    // SYNC FUNCTIONS
    const syncText = (input, element, fallback) => {
        if (!input || !element) return;
        input.addEventListener('input', () => {
            element.textContent = input.value || fallback;
            updateChecklist();
        });
    };

    syncText(offerNameInput, previewBannerTitle, 'Diwali Dhamaka Sale');
    syncText(couponCodeInput, previewCouponCode, 'DIWALI20');
    syncText(usageLimitInput, previewStatUsage, '500 total uses');
    syncText(perUserLimitInput, previewStatPerUser, '2 redemptions');
    
    // DISCOUNT SYNC
    if (discountValueInput) {
        discountValueInput.addEventListener('input', updateDiscountPreview);
    }
    if (discountTypeInput) {
        discountTypeInput.addEventListener('change', updateDiscountPreview);
    }

    function updateDiscountPreview() {
        const val = discountValueInput.value || '20%';
        const type = discountTypeInput.value;
        const max = maxDiscountInput.value ? ` up to ₹${maxDiscountInput.value}` : '';
        
        previewBannerDiscount.textContent = `${val}${type === 'Percentage' ? ' OFF' : ''}`;
        previewSummaryTitle.textContent = `Save ${val}${max} on festive categories`;
        updateChecklist();
    }

    // DATE SYNC
    if (startDateInput) {
        startDateInput.addEventListener('change', () => {
            previewStatStart.textContent = formatDate(startDateInput.value) || '20 Oct 2023';
            updateChecklist();
        });
    }
    if (expiryDateInput) {
        expiryDateInput.addEventListener('change', () => {
            previewStatEnd.textContent = formatDate(expiryDateInput.value) || '15 Nov 2023';
            updateChecklist();
        });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const options = { day: '2-digit', month: 'short', year: 'numeric' };
        return new Date(dateStr).toLocaleDateString('en-GB', options);
    }

    // CHECKLIST LOGIC
    function updateChecklist() {
        const dotExpiry = document.getElementById('dotExpiry');
        const dotScoped = document.getElementById('dotScoped');
        
        // Final Expiry Date Check
        if (expiryDateInput.value) {
            dotExpiry.classList.remove('dot-pending');
            dotExpiry.classList.add('dot-done');
        } else {
            dotExpiry.classList.remove('dot-done');
            dotExpiry.classList.add('dot-pending');
        }
    }
});
