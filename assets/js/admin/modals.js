/**
 * Sweets Website - Admin
 * =============================================================
 * File: assets/js/admin/modals.js
 * Description: Centralized logic for premium admin modals
 * =============================================================
 */

(function () {
    'use strict';

    const AdminModals = {
        deleteTarget: null,
        deleteType: 'product',
        onConfirmCallback: null,
        state: {},

        init() {
            this.bindDeleteConfirmation();
            this.bindHeartToggle();
            this.bindWeightTabs();
        },

        /**
         * Generic Delete Modal Populator
         * @param {String} type - 'product' | 'category' | 'subcategory' | 'customer' | 'offer' | 'inventory'
         * @param {Function} callback - Optional callback on confirm
         */
        openDeleteModal(data, type = 'product', callback = null) {
            this.deleteTarget = data;
            this.deleteType = type;
            this.onConfirmCallback = callback;

            const titleEl = document.getElementById('delete_modal_title');
            const subtitleEl = document.getElementById('delete_modal_subtitle');
            const nameEl = document.getElementById('delete_item_name');
            const priceEl = document.getElementById('delete_item_meta_primary');
            const stockEl = document.getElementById('delete_item_meta_secondary');
            const imageEl = document.getElementById('delete_item_image');

            // Set Labels based on type
            const capitalizedType = type.charAt(0).toUpperCase() + type.slice(1);
            if (titleEl) titleEl.textContent = `Delete ${capitalizedType}?`;
            if (subtitleEl) subtitleEl.textContent = `This action cannot be undone. The ${type} will be permanently removed.`;

            // Populate Content
            if (nameEl) nameEl.textContent = data.name;
            
            if (priceEl) {
                if (type === 'product') {
                    priceEl.textContent = 'Price : ₹ ' + (data.sale_price || data.base_price || 0);
                    priceEl.style.display = '';
                } else {
                    priceEl.style.display = 'none';
                }
            }

            if (stockEl) {
                if (type === 'product') {
                    stockEl.textContent = (data.stock_quantity || 0) + ' in Stock';
                    stockEl.style.display = '';
                } else if (type === 'category' || type === 'subcategory') {
                    stockEl.textContent = (data.product_count || 0) + ' Linked Products';
                    stockEl.style.display = '';
                } else {
                    stockEl.style.display = 'none';
                }
            }

            if (imageEl) {
                const path = data.image_path || data.image || 'assets/images/placeholders/product-placeholder.png';
                imageEl.src = (window.BASE_URL || '') + path;
            }

            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        },

        bindDeleteConfirmation() {
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            if (!confirmBtn) return;

            confirmBtn.addEventListener('click', async () => {
                if (!this.deleteTarget) return;

                // Handle Custom Callbacks (for mock data or specialized pages)
                if (this.onConfirmCallback) {
                    try {
                        await this.onConfirmCallback(this.deleteTarget);
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                        if (modal) modal.hide();
                    } catch (e) {
                        alert('Operation failed: ' + e.message);
                    }
                    return;
                }

                const originalHTML = confirmBtn.innerHTML;
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

                try {
                    const formData = new FormData();
                    let apiPath = 'api/v1/products.php';

                    if (this.deleteType === 'product' || this.deleteType === 'inventory') {
                        formData.append('product_id', this.deleteTarget.id);
                        formData.append('action', 'delete');
                    } else if (this.deleteType === 'category') {
                        apiPath = 'api/v1/categories.php';
                        formData.append('id', this.deleteTarget.id);
                        formData.append('action', 'delete');
                    } else if (this.deleteType === 'subcategory') {
                        apiPath = 'api/v1/subcategories.php';
                        formData.append('id', this.deleteTarget.id);
                        formData.append('action', 'delete');
                    }

                    const baseUri = (window.BASE_URL || '/').replace(/\/$/, '');
                    const fullApiPath = baseUri + '/admin/' + apiPath;

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const response = await fetch(fullApiPath, {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const result = await response.json();
                    
                    const isSuccess = result.success === true || result.status === 'success';
                    
                    if (isSuccess) {
                        window.location.reload();
                    } else {
                        throw new Error(result.message || 'Deletion failed');
                    }
                } catch (error) {
                    console.error('[AdminError]', error);
                    alert('Error: ' + error.message);
                } finally {
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = originalHTML;
                }
            });
        },

        bindHeartToggle() {
            const previewHeartBtn = document.getElementById('preview_heart_btn');
            if (!previewHeartBtn) return;
            previewHeartBtn.addEventListener('click', function () {
                const icon = this.querySelector('i');
                if (icon.style.color === 'rgb(245, 158, 11)' || icon.style.color === '#f59e0b') {
                    icon.style.color = '#ef4444';
                } else {
                    icon.style.color = '#f59e0b';
                }
            });
        },

        bindWeightTabs() {
            document.querySelectorAll('.weight-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const group = this.closest('.weight-options') || this.parentElement;
                    group.querySelectorAll('.weight-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        },

        /**
         * Open Product Preview Modal
         * @param {Object} data - Product data {id, name, image_path, sku, stock, status}
         * @param {String} type - 'product' | 'inventory'
         */
        openPreviewMode(data, type = 'product') {
            this.state.product = data;

            const modalEl = document.getElementById('productPreviewModal');
            if (!modalEl) return;

            const skeleton = document.getElementById('preview_skeleton');
            const mainContent = document.getElementById('preview_main_content');
            const errorState = document.getElementById('preview_error_state');

            if (skeleton) skeleton.classList.remove('d-none');
            if (mainContent) mainContent.classList.add('d-none');
            if (errorState) errorState.classList.add('d-none');

            const previewModal = new bootstrap.Modal(modalEl);
            previewModal.show();

            // Populate fields after brief skeleton delay
            setTimeout(() => {
                try {
                    const baseUrl = window.BASE_URL || '';
                    const imgPath = data.image_path || data.image || 'assets/images/placeholders/product-placeholder.png';

                    const el = (id) => document.getElementById(id);

                    if (el('preview_image')) el('preview_image').src = baseUrl + imgPath;
                    if (el('preview_name')) el('preview_name').textContent = data.name || 'Product';
                    if (el('preview_sku_value')) el('preview_sku_value').textContent = data.sku || 'N/A';
                    if (el('preview_stat_stock')) el('preview_stat_stock').textContent = data.stock ?? 0;
                    if (el('preview_stat_price')) el('preview_stat_price').textContent = data.price ? '₹ ' + data.price : '—';
                    if (el('preview_stat_sold')) el('preview_stat_sold').textContent = data.sold ?? '—';
                    if (el('preview_stat_rating')) el('preview_stat_rating').textContent = data.rating ? parseFloat(data.rating).toFixed(1) : '—';
                    if (el('preview_desc_text')) el('preview_desc_text').textContent = data.description || data.short_description || 'No description available.';

                    // Status badge
                    const statusBadge = el('preview_status_badge');
                    if (statusBadge) {
                        const status = (data.status || '').toLowerCase();
                        statusBadge.textContent = status === 'published' || status === 'in stock' ? 'Active' : (data.status || 'Active');
                        statusBadge.className = 'status-badge-custom';
                    }

                    // Stock radio
                    const inStock = el('inStockPreview');
                    const outStock = el('outStockPreview');
                    if (inStock && outStock) {
                        const stockQty = parseInt(data.stock) || 0;
                        inStock.checked = stockQty > 0;
                        outStock.checked = stockQty <= 0;
                    }

                    // Edit button — link to inventory-detail
                    const editBtn = el('preview_main_action_btn');
                    if (editBtn && data.id) {
                        editBtn.onclick = () => {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                            window.location.href = 'inventory-detail.php?id=' + data.id;
                        };
                        editBtn.textContent = 'Edit Product';
                    }

                    if (skeleton) skeleton.classList.add('d-none');
                    if (mainContent) mainContent.classList.remove('d-none');
                } catch (e) {
                    console.error('[AdminModals] openPreviewMode error:', e);
                    if (skeleton) skeleton.classList.add('d-none');
                    if (errorState) errorState.classList.remove('d-none');
                }
            }, 300);
        }
    };

    // Global Exposure
    window.BASE_URL = window.BASE_URL || '/Sweets-Website/';
    window.AdminModals = AdminModals;
    window.openPreviewMode = (data, type = 'product') => AdminModals.openPreviewMode(data, type);
    window.openDeleteModal = (data, type, callback) => AdminModals.openDeleteModal(data, type, callback);
    window.editFromPreview = () => {
        const product = AdminModals.state && AdminModals.state.product;
        if (product) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('productPreviewModal'));
            if (modal) modal.hide();
            if (typeof window.openEditProduct === 'function') {
                window.openEditProduct(product);
            } else if (product.id) {
                window.location.href = 'inventory-detail.php?id=' + product.id;
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => AdminModals.init());
})();
