/**
 * Sweets Website
 * =============================================================
 * File: inventory.js
 * Description: State-driven inventory management with Optimistic UI
 * Author: Antigravity - Senior frontend Engineer
 * Version: 2.1.0
 * =============================================================
 */

class InventoryManager {
    constructor() {
        this.state = new Map();
        this.locks = new Set();
        this.init();
    }

    /**
     * Bootstraps the module from the injected initial state
     */
    init() {
        try {
            const raw = document.getElementById('inventory-state').textContent;
            const items = JSON.parse(raw);
            items.forEach(item => this.state.set(parseInt(item.id), item));
        } catch (e) {
            console.error('[InventoryManager] Failed to load state:', e);
        }

        this.bindEvents();
    }

    /**
     * Event Delegation for +/- buttons and inputs
     */
    bindEvents() {
        document.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const container = e.target.closest('.inventory-qty-control');
                const id = parseInt(container.dataset.id);
                const action = e.target.dataset.action;
                this.updateStock(id, action);
            });
        });
    }

    /**
     * Core Optimistic Update Logic
     */
    async updateStock(id, action) {
        if (this.locks.has(id)) return; // Prevent double-clicks for this entity

        const item = this.state.get(id);
        if (!item) return;

        const previousStock = item.stock;
        const change = (action === 'add') ? 1 : -1;
        const newStock = previousStock + change;

        // Frontend Guard: No negative stock
        if (newStock < 0) {
            this.showToast('error', 'Stock cannot be negative.');
            return;
        }

        // 1. Lock entity
        this.setLock(id, true);

        // 2. Update local state
        item.stock = newStock;
        item.status = this.calculateStatus(newStock);

        // 3. Re-render UI (Optimistic)
        this.renderRow(id);

        // 4. API Call
        const response = await window.api.post('inventory.php', {
            product_id: id,
            quantity: 1,
            action: action
        });

        // 5. Finalize or Rollback
        if (!response.success) {
            // ROLLBACK
            item.stock = previousStock;
            item.status = this.calculateStatus(previousStock);
            this.renderRow(id);
            this.showToast('error', response.error.message);
        } else {
            this.showToast('success', `Stock for ${item.name} updated.`);
        }

        this.setLock(id, false);
    }

    /**
     * Re-calculate stock status label based on quantity
     */
    calculateStatus(qty) {
        if (qty === 0) return 'Out of Stock';
        if (qty <= 10) return 'Low Stock';
        return 'In Stock';
    }

    /**
     * Selective Row Re-rendering
     */
    renderRow(id) {
        const row = document.querySelector(`.product-row[data-product-id="${id}"]`);
        if (!row) return;

        const item = this.state.get(id);
        const input = row.querySelector('.qty-input');
        const statusPill = row.querySelector('.products-status-pill');

        // Update Input
        if (input) input.value = item.stock;

        // Update Status Pill
        if (statusPill) {
            statusPill.textContent = item.status;
            statusPill.className = 'd-inline-block fw-bold text-center products-status-pill ' + this.getStatusClass(item.status);
        }

        // Pulse effect for feedback
        row.classList.add('row-updating');
        setTimeout(() => row.classList.remove('row-updating'), 500);
    }

    getStatusClass(status) {
        if (status === 'In Stock' || status === 'Active') return 'products-status-in';
        if (status === 'Low Stock') return 'products-status-low';
        return 'products-status-out';
    }

    setLock(id, locked) {
        const row = document.querySelector(`.product-row[data-product-id="${id}"]`);
        if (!row) return;

        if (locked) {
            this.locks.add(id);
            row.classList.add('u-locked');
            row.querySelectorAll('button').forEach(b => b.disabled = true);
        } else {
            this.locks.delete(id);
            row.classList.remove('u-locked');
            row.querySelectorAll('button').forEach(b => b.disabled = false);
        }
    }

    showToast(type, message) {
        // Simple implementation of a non-blocking notification
        console.log(`[${type.toUpperCase()}] ${message}`);
        // In a real production app, we would mount a Toast component here
    }
}

// Initialize on Load
document.addEventListener('DOMContentLoaded', () => {
    window.inventoryManager = new InventoryManager();
});
