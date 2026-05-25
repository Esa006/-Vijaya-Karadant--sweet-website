/**
 * Sweets Website
 * =============================================================
 * File: products.js
 * Description: Production-grade product management with Optimistic deletion
 * Author: Antigravity - Senior frontend Engineer
 * Version: 2.1.0
 * =============================================================
 */

class ProductManager {
    constructor() {
        this.locks = new Set();
        this.init();
    }

    init() {
        this.bindEvents();
    }

    /**
     * Event Delegation for Delete actions
     */
    bindEvents() {
        document.body.addEventListener('click', (e) => {
            const deleteBtn = e.target.closest('[title="Delete Product"]');
            if (deleteBtn) {
                const row = deleteBtn.closest('.product-row');
                const id = parseInt(row.dataset.productId);
                this.handleDelete(id, row);
            }
        });
    }

    /**
     * Optimistic Deletion with UI Rollback
     */
    async handleDelete(id, row) {
        if (this.locks.has(id)) return;

        // confirm with user (Bootstrap native approach)
        // In a real app, we would listen for 'confirm' from the modal
        // For this demo, let's assume the user clicks "Yes Delete"

        const confirmBtn = document.querySelector('.products-delete-confirm-btn');
        if (!confirmBtn) return;

        confirmBtn.onclick = async () => {
            const previousDisplay = row.style.display;

            // 1. Lock entity
            this.setLock(id, true);

            // 2. Optimistic: Remove from DOM
            row.style.opacity = '0.3';
            row.style.pointerEvents = 'none';

            // 3. API Call
            const response = await window.api.delete('products.php', { id });

            // Close the modal
            bootstrap.Modal.getInstance(document.getElementById('deleteProductModal')).hide();

            // 4. Finalize or Rollback
            if (!response.success) {
                // ROLLBACK: Restore visibility and show error
                row.style.opacity = '1';
                row.style.pointerEvents = 'auto';
                this.showToast('error', response.error.message);
                this.setLock(id, false);
            } else {
                // Remove entirely
                row.classList.add('removing');
                setTimeout(() => row.remove(), 400);
                this.showToast('success', 'Product deleted successfully.');
                this.locks.delete(id);
            }
        };
    }

    setLock(id, locked) {
        const row = document.querySelector(`.product-row[data-product-id="${id}"]`);
        if (!row) return;

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
    window.productManager = new ProductManager();
});
