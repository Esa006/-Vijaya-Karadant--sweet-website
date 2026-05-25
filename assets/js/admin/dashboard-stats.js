/**
 * Sweets Website - Dashboard Stats Manager
 * =============================================================
 * Responsibilities: 
 * - Fetch stats from /admin/api/v1/reports
 * - Update dashboard cards with formatting (₹, L, etc.)
 * - Handle loading states
 * =============================================================
 */

document.addEventListener('DOMContentLoaded', async () => {
    const statsManager = {
        elements: {
            revenue: document.getElementById('stat-revenue'),
            orders: document.getElementById('stat-orders'),
            customers: document.getElementById('stat-customers'),
            pending: document.getElementById('stat-pending')
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                maximumFractionDigits: 0
            }).format(value);
        },

        formatCompact(value) {
            return new Intl.NumberFormat('en-IN', {
                notation: 'compact',
                maximumFractionDigits: 1
            }).format(value);
        },

        async init() {
            console.log('[Dashboard] Initializing Statistics...');
            
            // 1. Fetch from Hardened API
            const response = await window.adminAPI.get('/reports.php');

            if (response.success) {
                this.updateUI(response.data);
            } else {
                console.error('[Dashboard] Failed to fetch stats:', response.error.message);
                this.setFallbackUI();
            }
        },

        updateUI(data) {
            if (this.elements.revenue) {
                this.elements.revenue.textContent = this.formatCurrency(data.total_revenue);
            }
            if (this.elements.orders) {
                this.elements.orders.textContent = data.total_orders.toLocaleString();
            }
            if (this.elements.customers) {
                this.elements.customers.textContent = data.total_customers.toLocaleString();
            }
            if (this.elements.pending) {
                this.elements.pending.textContent = this.formatCurrency(480000); // Placeholder for detailed pending logic
            }
        },

        setFallbackUI() {
            // Set some default/mock data if API fails
            Object.values(this.elements).forEach(el => {
                if (el) el.textContent = 'Error';
            });
        }
    };

    // Auto-run
    await statsManager.init();
});
