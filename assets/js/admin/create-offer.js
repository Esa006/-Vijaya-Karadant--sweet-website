/**
 * Sweets Website
 * =============================================================
 * File: create-offer.js
 * Description: Live preview engine and UI logic for the Create Offer page.
 * Includes category tagging and publishing checklist management.
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', function() {
    // --- Form Field References ---
    const fields = {
        offerName: document.getElementById('offerName'),
        couponCode: document.getElementById('couponCode'),
        offerDescription: document.getElementById('offerDescription'),
        discountType: document.getElementById('discountType'),
        discountValue: document.getElementById('discountValue'),
        maxDiscount: document.getElementById('maxDiscount'),
        minOrderValue: document.getElementById('minOrderValue'),
        perUserLimit: document.getElementById('perUserLimit'),
        totalUsageLimit: document.getElementById('totalUsageLimit'),
        startDate: document.getElementById('startDate'),
        expiryDate: document.getElementById('expiryDate')
    };

    // --- Initial Execution ---
    setupEventListeners();
    updateLivePreview();

    function setupEventListeners() {
        // Form field changes
        Object.values(fields).forEach(f => {
            if (f) {
                f.addEventListener('input', updateLivePreview);
                f.addEventListener('change', updateLivePreview);
            }
        });

        // Radio Pill Selection
        document.querySelectorAll('.offer-radio-pill').forEach(pill => {
            pill.addEventListener('click', function() {
                const groupName = this.getAttribute('data-group');
                document.querySelectorAll(`.offer-radio-pill[data-group="${groupName}"]`).forEach(p => {
                    p.classList.remove('active');
                    const radio = p.querySelector('input[type="radio"]');
                    if (radio) radio.checked = false;
                });
                
                this.classList.add('active');
                const selectedRadio = this.querySelector('input[type="radio"]');
                if (selectedRadio) {
                    selectedRadio.checked = true;
                    // Trigger custom events if needed
                }
                updateLivePreview();
            });
        });
    }

    // --- Live Preview Core ---
    function updateLivePreview() {
        if (!fields.offerName) return;

        // 1. Promo Card Updates
        document.getElementById('previewOfferName').textContent = fields.offerName.value || 'Diwali Dhamaka Sale';
        
        const desc = fields.offerDescription.value;
        const truncatedDesc = desc ? (desc.length > 80 ? desc.substring(0, 80) + '...' : desc) : 'Celebrate with premium mithai and gift boxes.';
        document.getElementById('previewDescription').textContent = truncatedDesc;
        
        const dv = fields.discountValue.value || '20%';
        document.getElementById('previewDiscountBadge').textContent = dv.toUpperCase();

        // 2. Coupon Badge
        document.getElementById('previewCouponBadge').textContent = (fields.couponCode.value || 'DIWALI20').toUpperCase();

        // 3. Info Panel
        const maxD = fields.maxDiscount.value || '₹ 500';
        const minOrder = fields.minOrderValue.value || '₹ 1,000';
        const categories = getSelectedCategoryNames();
        const catText = categories.length > 0 ? categories.join(', ') : 'eligible products';
        
        document.getElementById('previewTitleInfo').textContent = `Save up to ${maxD.replace('₹ ', '₹')} on festive categories`;
        document.getElementById('previewDescInfo').textContent = `Applicable on ${catText} for orders above ${minOrder}.`;

        // 4. Stats Grid
        document.getElementById('previewStatUsage').textContent = fields.totalUsageLimit.value || '500 redemptions';
        document.getElementById('previewStatPerUser').textContent = fields.perUserLimit.value || '2 uses';
        
        if (fields.startDate.value) {
            document.getElementById('previewStatStart').textContent = formatPrettyDate(fields.startDate.value);
        }
        if (fields.expiryDate.value) {
            document.getElementById('previewStatExpiry').textContent = formatPrettyDate(fields.expiryDate.value);
        }

        // 5. Checklist Logic
        updatePublishingChecklist();
    }

    // --- Checklist Management ---
    function updatePublishingChecklist() {
        let itemsDone = 0;
        const dotExpiry = document.getElementById('dotExpiry');
        const dotDiscount = document.getElementById('dotDiscount');
        const dotScope = document.getElementById('dotScope');

        // Expiry
        if (fields.expiryDate.value) {
            dotExpiry.className = 'checklist-status-dot dot-ready';
            itemsDone++;
        } else {
            dotExpiry.className = 'checklist-status-dot dot-wait';
        }

        // Discount
        if (fields.discountValue.value && fields.maxDiscount.value) {
            dotDiscount.className = 'checklist-status-dot dot-ready';
            itemsDone++;
        } else {
            dotDiscount.className = 'checklist-status-dot dot-wait';
        }

        // Scope
        if (getSelectedCategoryNames().length > 0) {
            dotScope.className = 'checklist-status-dot dot-ready';
            itemsDone++;
        } else {
            dotScope.className = 'checklist-status-dot dot-warn';
        }

        const badge = document.getElementById('checklistBadge');
        if (badge) {
            const itemsLeft = 3 - itemsDone;
            badge.textContent = itemsLeft > 0 ? `${itemsLeft} items left` : 'All ready!';
            badge.style.color = itemsLeft === 0 ? '#2E9E5A' : '#7B2D12';
            badge.style.borderColor = itemsLeft === 0 ? '#2E9E5A' : '#7B2D12';
            badge.style.background = itemsLeft === 0 ? '#F0FAF0' : 'transparent';
        }
    }

    // --- Helper Functions ---
    function getSelectedCategoryNames() {
        const tags = document.querySelectorAll('.offer-category-tag');
        return Array.from(tags).map(t => t.getAttribute('data-category'));
    }

    function formatPrettyDate(dateStr) {
        const d = new Date(dateStr);
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    }

    // --- Exported Interface for Native HTML Calls ---
    window.removeOfferCategory = function(el) {
        el.closest('.offer-category-tag').remove();
        updateLivePreview();
        showCreateOfferToast('Category removed from scope');
    };

    window.addOfferCategory = function() {
        const modalEl = document.getElementById('addCategoryOfferModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    };

    window.confirmAddOfferCategory = function() {
        const input = document.getElementById('newCategoryInputOffer');
        const name = input ? input.value.trim() : '';
        
        if (!name) return;
        
        const existing = getSelectedCategoryNames();
        if (existing.includes(name)) {
            showCreateOfferToast('Category already in list', true);
            return;
        }

        const wrap = document.getElementById('offerCategoryTagsWrap');
        const addBtn = wrap.querySelector('.add-category-trigger');
        
        const tag = document.createElement('span');
        tag.className = 'offer-category-tag';
        tag.setAttribute('data-category', name);
        tag.innerHTML = `${name} <span class="remove-offer-tag bi bi-x" onclick="removeOfferCategory(this)"></span>`;
        
        wrap.insertBefore(tag, addBtn);
        
        bootstrap.Modal.getInstance(document.getElementById('addCategoryOfferModal')).hide();
        if (input) input.value = '';
        
        updateLivePreview();
        showCreateOfferToast(`"${name}" added to promotion`);
    };

    window.saveNewOffer = async function() {
        const name = fields.offerName.value.trim();
        const code = fields.couponCode.value.trim();
        const type = fields.discountType.value;
        const val  = fields.discountValue.value.replace(/[^\d.]/g, '');

        if (!name || !code || !val) {
            showCreateOfferToast('Please fill all required fields (Name, Code, Value)!', true);
            return;
        }

        const btn = document.querySelector('.btn-save-offer-publish');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Publishing...';
        btn.disabled = true;

        try {
            const payload = {
                code: code,
                description: fields.offerDescription.value,
                type: type,
                value: val,
                min_cart_total: fields.minOrderValue.value.replace(/[^\d.]/g, '') || 0,
                usage_limit: fields.totalUsageLimit.value.replace(/[^\d.]/g, '') || 1,
                limit_per_user: fields.perUserLimit.value.replace(/[^\d.]/g, '') || 1,
                expires_at: fields.expiryDate.value || null,
                applicable_categories: JSON.stringify(getSelectedCategoryNames())
            };

            const res = await fetch('api/v1/coupons.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (result.success) {
                showCreateOfferToast('Promotion published successfully!');
                setTimeout(() => {
                    window.location.href = 'offers.php';
                }, 1000);
            } else {
                showCreateOfferToast(result.error || 'Failed to publish offer', true);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (err) {
            console.error('Save error:', err);
            showCreateOfferToast('System error while publishing', true);
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    };

    window.cancelCreateOffer = function() {
        if (confirm('Discard changes and return to offers?')) {
            window.location.href = 'offers.php';
        }
    };

    function showCreateOfferToast(message, isError = false) {
        const toast = document.createElement('div');
        toast.style.cssText = `position: fixed; bottom: 30px; right: 30px; background: ${isError ? '#D32F2F' : '#2D2D2D'}; 
                               color: white; padding: 12px 24px; border-radius: 10px; z-index: 1100; box-shadow: 0 4px 12px rgba(0,0,0,0.2); 
                               animation: slideInUp 0.4s ease;`;
        toast.innerHTML = `<i class="bi bi-${isError ? 'exclamation-circle' : 'check-circle'} me-2"></i> ${message}`;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.4s ease';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    }
});
