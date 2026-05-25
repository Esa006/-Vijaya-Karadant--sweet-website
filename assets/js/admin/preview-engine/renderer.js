/**
 * Preview Engine - Renderer
 * Modular DOM rendering with partial updates
 */

import { utils } from './utils.js';

export class Renderer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.elements = this.cacheElements();
    }

    /**
     * Cache DOM elements for efficient access
     */
    cacheElements() {
        return {
            skeleton: document.getElementById('preview_skeleton'),
            mainContent: document.getElementById('preview_main_content'),
            errorUI: document.getElementById('preview_error_state'),
            image: document.getElementById('preview_image'),
            name: document.getElementById('preview_name'),
            badge: document.getElementById('preview_status_badge'),
            price: document.getElementById('preview_stat_price'),
            stock: document.getElementById('preview_stat_stock'),
            sold: document.getElementById('preview_stat_sold'),
            rating: document.getElementById('preview_stat_rating'),
            variantsList: document.getElementById('preview_variants_list'),
            variantSection: document.querySelector('.variant-section-custom'),
            desc: document.getElementById('preview_desc_text'),
            sku: document.getElementById('preview_sku_value'),
            category: document.getElementById('preview_category_value'),
            actionBtn: document.getElementById('preview_main_action_btn'),
            inStockRadio: document.getElementById('inStockPreview'),
            outStockRadio: document.getElementById('outStockPreview')
        };
    }

    /**
     * Main Render Cycle
     */
    render(state, prevState) {
        const { loading, error, product, selectedVariant, type } = state;

        // 1. Root Visibility
        this.toggleRootState(loading, !!error);
        if (loading || error || !product) return;

        // 2. Partial Updates (Only if values changed)
        if (product.name !== prevState.product?.name) {
            this.elements.name.textContent = product.name;
        }

        this.renderHeader(product);
        this.renderStatus(product.status);
        this.renderMetrics(selectedVariant, prevState.selectedVariant, state);
        this.renderVariants(product.variants, selectedVariant, type);
        this.renderMeta(product, type);
        this.renderActions(selectedVariant);
    }

    renderHeader(product) {
        if (this.elements.image) {
            // Priority: image (user structure) -> hero_image -> image_path -> fallback
            const path = product.image || product.hero_image || product.image_path || product.product_image || product.category_image || 'assets/images/placeholders/product-placeholder.png';
            this.elements.image.src = (window.BASE_URL || '') + path;
        }
        
        const bestSellerBadge = document.getElementById('preview_best_seller_badge');
        if (bestSellerBadge) {
            bestSellerBadge.style.display = product.is_best_seller || (product.sold > 200) ? 'block' : 'none';
        }
    }

    toggleRootState(loading, hasError) {
        this.elements.skeleton?.classList.toggle('d-none', !loading);
        this.elements.mainContent?.classList.toggle('d-none', loading || hasError);
        this.elements.errorUI?.classList.toggle('d-none', !hasError);
    }

    renderStatus(status) {
        const isActive = status === 'active' || status === 'published' || status === 1;
        this.elements.badge.textContent = isActive ? 'Active' : 'Draft';
        this.elements.badge.className = 'status-badge-custom';
        if (!isActive) {
            this.elements.badge.style.backgroundColor = '#f8f9fa';
            this.elements.badge.style.color = '#6c757d';
            this.elements.badge.style.borderColor = '#dee2e6';
        } else {
            this.elements.badge.style.backgroundColor = '';
            this.elements.badge.style.color = '';
            this.elements.badge.style.borderColor = '';
        }
    }

    renderMetrics(variant, prevVariant, state) {
        if (!variant) return;

        const newPrice = utils.formatCurrency(variant.price);
        const newStock = variant.stock;

        // Update Price with animation if changed
        if (this.elements.price.textContent !== newPrice) {
            this.elements.price.textContent = newPrice;
            if (prevVariant) this.triggerFlash(this.elements.price.parentElement);
        }

        // Update Stock with animation if changed
        if (this.elements.stock.textContent != newStock) {
            this.elements.stock.textContent = newStock;
            if (prevVariant) this.triggerFlash(this.elements.stock.parentElement);
        }

        this.elements.sold.textContent = variant.sold || 0;
        this.elements.rating.textContent = state.product?.rating ? `${state.product.rating}/5` : 'No rating';

        if (this.elements.inStockRadio && this.elements.outStockRadio) {
            this.elements.inStockRadio.checked = variant.stock > 0;
            this.elements.outStockRadio.checked = variant.stock <= 0;
        }
    }

    renderVariants(variants, selectedVariant, type) {
        if (!variants || variants.length === 0) {
            if (this.elements.variantSection) this.elements.variantSection.style.display = 'none';
            return;
        }
        
        if (this.elements.variantSection) this.elements.variantSection.style.display = '';
        
        this.elements.variantsList.innerHTML = variants.map(v => `
            <button class="btn-weight-custom ${selectedVariant?.id === v.id ? 'active' : ''}" 
                    onclick="window.PreviewEngine.selectVariant('${v.id}')"
                    aria-label="Select variant ${v.label}">
                ${v.label}
            </button>
        `).join('');
    }

    renderMeta(product, type) {
        if (this.elements.desc) {
            this.elements.desc.textContent = product.description || product.short_description || 'No description available.';
        }
        if (this.elements.sku) {
            this.elements.sku.textContent = product.sku || 'N/A';
        }
        if (this.elements.category) {
            this.elements.category.textContent = product.category_name || 'General';
        }

        // Always show all stats if data structure is provided
        const stats = document.querySelectorAll('.stat-card-custom');
        const skuRow = document.querySelector('.sku-row-custom');
        
        stats.forEach(s => s && (s.style.display = ''));
        if (skuRow) skuRow.style.display = '';
    }

    renderActions(variant) {
        if (!this.elements.actionBtn) return;

        if (variant && variant.stock === 0) {
            this.elements.actionBtn.textContent = 'Out of Stock (Fix in Edit)';
            this.elements.actionBtn.classList.replace('btn-primary', 'btn-warning');
            this.elements.actionBtn.style.backgroundColor = '#E67E22'; // Use orange for warning
            this.elements.actionBtn.disabled = false;
        } else {
            this.elements.actionBtn.textContent = 'Edit Details';
            this.elements.actionBtn.classList.remove('btn-warning');
            this.elements.actionBtn.style.backgroundColor = '#8B2E2E';
            this.elements.actionBtn.disabled = false;
        }
    }

    triggerFlash(el) {
        el.classList.remove('value-update-flash');
        void el.offsetWidth; // Trigger reflow
        el.classList.add('value-update-flash');
    }
}
