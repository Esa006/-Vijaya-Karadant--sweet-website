/**
 * Sweets Website
 * =============================================================
 * File: offers.js
 * Description: State-driven coupon and promotion management
 * Author: Antigravity - Senior frontend Engineer
 * Version: 2.1.0
 * =============================================================
 */

class OffersManager {
    constructor() {
        this.locks = new Set();
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // 1. Search & Filter
        const searchInput = document.getElementById('offerSearch');
        const statusFilter = document.getElementById('offerStatusFilter');

        if (searchInput) searchInput.addEventListener('input', () => this.filter());
        if (statusFilter) statusFilter.addEventListener('change', () => this.filter());

        // 2. Action Buttons (Event Delegation)
        document.body.addEventListener('click', (e) => {
            const deleteBtn = e.target.closest('[title="Delete"]');
            if (deleteBtn) {
                const row = deleteBtn.closest('.offer-row');
                const id = parseInt(row.dataset.couponId);
                this.handleDelete(id, row);
            }
        });
    }

    /**
     * Optimized Filtering
     */
    filter() {
        const searchTerm = document.getElementById('offerSearch').value.toLowerCase();
        const selStatus = document.getElementById('offerStatusFilter').value;
        const rows = document.querySelectorAll('.offer-row');

        rows.forEach(row => {
            const name   = row.getAttribute('data-name');
            const code   = row.getAttribute('data-code');
            const status = row.getAttribute('data-status');

            const matchesSearch = name.includes(searchTerm) || code.includes(searchTerm);
            const matchesStatus = (selStatus === 'all' || status === selStatus);

            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    /**
     * Optimistic Deletion
     */
    async handleDelete(id, row) {
        if (this.locks.has(id)) return;
        
        if (!confirm('Are you sure you want to delete this offer?')) return;

        // 1. Lock entity
        this.setLock(id, true, row);

        // 2. Optimistic UI
        row.style.opacity = '0.3';
        row.style.transition = 'opacity 0.2s ease';

        // 3. API Call
        const response = await window.api.delete('coupons.php', { id });

        // 4. Finalize or Rollback
        if (!response.success) {
            row.style.opacity = '1';
            this.showToast('error', response.error.message);
            this.setLock(id, false, row);
        } else {
            row.classList.add('removing');
            setTimeout(() => row.remove(), 300);
            this.showToast('success', 'Coupon deleted successfully.');
        }
    }

    setLock(id, locked, row) {
        if (locked) {
            this.locks.add(id);
            row.querySelectorAll('button, a').forEach(el => el.style.pointerEvents = 'none');
        } else {
            this.locks.delete(id);
            row.querySelectorAll('button, a').forEach(el => el.style.pointerEvents = 'auto');
        }
    }

    showToast(type, message) {
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
}

// Global initialization
document.addEventListener('DOMContentLoaded', () => {
    window.offersManager = new OffersManager();
});
