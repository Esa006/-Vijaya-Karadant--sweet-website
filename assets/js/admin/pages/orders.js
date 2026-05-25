/**
 * Sweets Website
 * =============================================================
 * File: orders.js
 * Description: Order management with strict state machine transitions
 * Author: Antigravity - Senior frontend Engineer
 * Version: 2.1.0
 * =============================================================
 */

class OrderManager {
    constructor() {
        this.locks = new Set();
        // Match the backend service map
        this.statusMap = {
            'pending':   ['paid', 'cancelled'],
            'paid':      ['shipped', 'cancelled'],
            'shipped':   ['delivered'],
            'delivered': [],
            'cancelled': []
        };
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        document.querySelectorAll('.status-selector').forEach(select => {
            select.addEventListener('change', (e) => {
                const id = parseInt(e.target.dataset.id);
                const nextStatus = e.target.value;
                const row = e.target.closest('.shipment-row');
                const currentStatus = row.dataset.status.toLowerCase();

                this.handleTransition(id, currentStatus, nextStatus, row, e.target);
            });
        });
    }

    /**
     * Handle state machine transition
     */
    async handleTransition(id, current, next, row, select) {
        if (this.locks.has(id)) return;

        // 1. Client-side transition check
        const allowed = this.statusMap[current] || [];
        if (!allowed.includes(next)) {
            this.showToast('error', `Cannot move from ${current} to ${next}.`);
            select.value = current; // Reset
            return;
        }

        // 2. Lock & Loading
        this.setLock(id, true, row);

        // 3. API Call
        const response = await window.api.patch('orders.php', {
            order_id: id,
            status: next
        });

        // 4. Finalize or Rollback
        if (!response.success) {
            this.showToast('error', response.error.message);
            select.value = current; // Rollback select
        } else {
            this.showToast('success', `Order #${id} is now ${next}.`);
            row.dataset.status = next; // Update DOM state
            // Update row appearance if needed (e.g. colors)
            this.updateRowApperance(row, next);
        }

        this.setLock(id, false, row);
    }

    updateRowApperance(row, status) {
        // Here we could update status pill classes if they were still present
        // But since we use a select, we just update the data-attribute
    }

    setLock(id, locked, row) {
        if (locked) {
            this.locks.add(id);
            row.classList.add('u-locked');
            row.querySelectorAll('select, button').forEach(el => el.disabled = true);
        } else {
            this.locks.delete(id);
            row.classList.remove('u-locked');
            row.querySelectorAll('select, button').forEach(el => el.disabled = false);
        }
    }

    showToast(type, message) {
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    window.orderManager = new OrderManager();
});
