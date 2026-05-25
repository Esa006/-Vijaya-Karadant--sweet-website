/**
 * Preview Engine - Main Orchestrator
 * Production-grade class to manage lifecycle and coordination
 */

import { ApiService } from './api.js';
import { StateManager } from './state.js';
import { Renderer } from './renderer.js';

export class PreviewEngine {
    constructor(config) {
        this.api = new ApiService(config.baseUrl);
        this.renderer = new Renderer(config.containerId);
        this.stateManager = new StateManager({ type: 'product' }, (state, prevState) => {
            this.renderer.render(state, prevState);
        });

        this.pollingInterval = null;
        this.config = config;
    }

    /**
     * Entry point: Open modal and start engine
     */
    async open(initialData, type = 'product') {
        this.stateManager.setState({ 
            type, 
            product: initialData, 
            loading: true, 
            error: null, 
            selectedVariant: null 
        });

        // Explicitly show modal
        const modalEl = document.getElementById(this.config.containerId);
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            
            // Bind cleanup to modal close
            modalEl.addEventListener('hidden.bs.modal', () => this.destroy(), { once: true });
        }

        // Start Fetch & Polling (only for products)
        if (type === 'product') {
            await this.syncData(initialData.id);
            this.startPolling(initialData.id);
        } else {
            // Subcategories render immediately from initialData
            this.stateManager.setState({ loading: false });
        }
    }

    /**
     * Synchronize data from API
     */
    async syncData(id, isSilent = false) {
        if (!id || this.stateManager.getState().type !== 'product') {
            this.stateManager.setState({ loading: false });
            return;
        }

        const result = await this.api.fetchProduct(id);

        if (result.status === 'success') {
            const normalizedData = this.normalizeProductData(result.data);
            this.stateManager.setState({ 
                product: normalizedData, 
                loading: false, 
                lastUpdated: Date.now() 
            });
        } else if (result.status === 'error' && !isSilent) {
            this.stateManager.setState({ 
                error: result.message, 
                loading: false 
            });
        }
    }

    /**
     * Injects demo/fallback variants if missing from DB
     */
    normalizeProductData(data) {
        if (!data.variants || data.variants.length === 0) {
            const basePrice = parseFloat(data.sale_price || data.base_price || 0);
            data.variants = [
                { id: 'v1', label: '250g', price: basePrice, stock: data.stock_quantity, sold: 120 },
                { id: 'v2', label: '500g', price: basePrice * 1.8, stock: Math.floor(data.stock_quantity / 2), sold: 85 },
                { id: 'v3', label: '1kg', price: basePrice * 3.2, stock: 0, sold: 45 }
            ];
        }
        return data;
    }

    startPolling(id) {
        this.stopPolling();
        this.pollingInterval = setInterval(() => this.syncData(id, true), 10000);
    }

    stopPolling() {
        if (this.pollingInterval) clearInterval(this.pollingInterval);
    }

    selectVariant(variantId) {
        const state = this.stateManager.getState();
        const variant = state.product?.variants?.find(v => v.id === variantId);
        if (variant) {
            this.stateManager.setState({ selectedVariant: variant });
        }
    }

    destroy() {
        this.stopPolling();
    }
}
