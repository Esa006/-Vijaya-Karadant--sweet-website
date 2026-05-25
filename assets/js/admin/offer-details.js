/**
 * Sweets Website - Admin
 * =============================================================
 * File: assets/js/admin/offer-details.js
 * Description: Dynamic rendering logic for Offer Details dashboard
 * Author: Antigravity
 * =============================================================
 */

const OfferApp = {
    id: document.getElementById('offerApp')?.dataset.id,
    state: { data: null },

    async init() {
        console.log('Offer App: Initializing for ID:', this.id);
        if (!this.id) return;
        
        await this.fetchData();
        this.setupListeners();
    },

    async fetchData() {
        try {
            const response = await fetch(`api/v1/offer-details.php?id=${this.id}`);
            const result = await response.json();
            
            if (result.success) {
                this.state.data = result.data;
                this.render();
            } else {
                alert(result.error || 'Failed to load offer data');
            }
        } catch (error) {
            console.error('Offer App Fetch Error:', error);
        }
    },

    render() {
        const data = this.state.data;
        const config = data.config;
        const perf = data.performance;

        // 1. Header & Breadcrumb
        this.safeSetText('breadcrumbOffer', config.code);
        this.safeSetText('offerTitle', config.description || 'Unnamed Offer');

        // 2. Stat Row
        const discountVal = config.type === 'percentage' ? `${parseFloat(config.value)}% off` : `₹ ${parseFloat(config.value)} off`;
        this.safeSetText('statDiscount', discountVal);
        this.safeSetText('statMinOrder', `₹ ${parseFloat(config.min_cart_total).toLocaleString('en-IN')}`);
        
        const currentUsage = parseInt(perf.total_orders || 0);
        const totalLimit = parseInt(config.usage_limit || 0);
        const usagePct = totalLimit > 0 ? (currentUsage / totalLimit) * 100 : 0;
        
        this.safeSetText('statUsageCount', currentUsage);
        this.safeSetText('usageUsed', `${currentUsage} Used`);
        this.safeSetText('usageTotal', `${totalLimit} Total`);
        document.getElementById('usageBar').style.width = `${usagePct}%`;

        // Dates
        const start = dayjs(config.created_at);
        const end = config.expires_at ? dayjs(config.expires_at) : null;
        this.safeSetText('statValidityRange', end ? `${start.format('MMM D')} - ${end.format('MMM D')}` : 'Forever');
        
        if (end) {
            const daysLeft = end.diff(dayjs(), 'day');
            this.safeSetText('statDaysRemaining', daysLeft > 0 ? `${daysLeft} Days remaining` : 'Expired');
        }

        // 3. Configuration Details
        this.safeSetText('detailType', config.type.charAt(0).toUpperCase() + config.type.slice(1) + ' Discount');
        this.safeSetText('detailCode', config.code);
        this.safeSetText('detailCreator', config.creator_name || 'Admin');
        this.safeSetText('detailStart', start.format('MMM D, YYYY - h:mm A'));
        this.safeSetText('detailEnd', end ? end.format('MMM D, YYYY - h:mm A') : 'No Expiry');

        // 4. Categories
        const catBox = document.getElementById('categoryTags');
        if (catBox && config.applicable_categories) {
            catBox.innerHTML = config.applicable_categories.map(cat => `
                <div class="category-tag">
                    <i class="bi bi-tag"></i> ${cat}
                </div>
            `).join('');
        }

        // 5. Usage Limits
        this.safeSetText('trackTotal', `${totalLimit} Uses`);
        this.safeSetText('trackCurrent', currentUsage);
        this.safeSetText('trackRemaining', Math.max(0, totalLimit - currentUsage));
        this.safeSetText('trackPerUser', config.limit_per_user);
        this.safeSetText('trackPercent', `${Math.round(usagePct)}% Consumed`);
        document.getElementById('trackBar').style.width = `${usagePct}%`;

        // 6. Performance Insights
        this.safeSetText('perfOrders', currentUsage);
        this.safeSetText('perfDiscount', `₹ ${parseFloat(perf.total_discount || 0).toLocaleString('en-IN')}`);
        this.safeSetText('perfRevenue', `₹ ${parseFloat(perf.total_revenue || 0).toLocaleString('en-IN')}`);
        this.safeSetText('perfAov', `₹ ${parseFloat(perf.aov || 0).toLocaleString('en-IN')}`);
    },

    safeSetText(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    },

    setupListeners() {
        const copyIcon = document.querySelector('.bi-copy');
        if (copyIcon) {
            copyIcon.addEventListener('click', () => {
                const code = document.getElementById('detailCode').textContent;
                navigator.clipboard.writeText(code);
                alert('Coupon code copied to clipboard!');
            });
        }
    }
};

document.addEventListener('DOMContentLoaded', () => OfferApp.init());
