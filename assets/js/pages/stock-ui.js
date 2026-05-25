/**
 * Sweets Website
 * =============================================================
 * File: assets/js/pages/stock-ui.js
 * Description: Frontend stock management system
 *   - Renders stock badges on product cards (Amazon/Flipkart style)
 *   - Guards Add-to-Cart with real-time backend revalidation
 *   - Handles "Notify Me" for out-of-stock products
 *   - Bulk-checks all visible product IDs on page load
 * =============================================================
 */

const StockUI = (() => {
    'use strict';

    const API_BASE = 'api/v1/stock.php';
    const CART_API = 'api/v1/cart-add.php';

    // Low-stock threshold must match PHP constant (5)
    const LOW_STOCK_THRESHOLD = 5;

    // ── Utility ───────────────────────────────────────────────

    /**
     * Derive stock_status from quantity (mirrors PHP StockRepository::computeStatus)
     */
    function computeStatus(qty) {
        if (qty <= 0)                    return 'out_of_stock';
        if (qty <= LOW_STOCK_THRESHOLD)  return 'low_stock';
        return 'in_stock';
    }

    /**
     * Build badge HTML for a given stock_status + optional quantity.
     */
    function buildBadgeHTML(status, qty = 0) {
        if (status === 'in_stock') {
            return `<span class="c-stock-badge c-stock-badge--in" aria-label="In Stock">In Stock</span>`;
        }
        if (status === 'low_stock') {
            const fill = Math.round((qty / LOW_STOCK_THRESHOLD) * 100);
            return `
                <span class="c-stock-badge c-stock-badge--low" aria-label="Low stock">Only ${qty} left</span>
                <div class="c-stock-urgency" aria-hidden="true">
                    <div class="c-stock-urgency__bar-track">
                        <div class="c-stock-urgency__bar-fill" style="width:${fill}%"></div>
                    </div>
                </div>`;
        }
        // out_of_stock
        return `<span class="c-stock-badge c-stock-badge--out" aria-label="Out of Stock">Out of Stock</span>`;
    }

    /**
     * Apply stock state to a product card element.
     * Expects card to have:
     *   [data-product-id]
     *   .c-product-card__stock-wrap  — badge injection point
     *   .btn-add-to-cart             — the cart button
     *   .c-notify-wrap               — notify-me section (optional)
     */
    function applyStockState(card, payload) {
        const { stock_status: status, stock_quantity: qty } = payload;

        // ── 1. Badge ─────────────────────────────────────────
        const badgeWrap = card.querySelector('.c-product-card__stock-wrap');
        if (badgeWrap) {
            badgeWrap.innerHTML = buildBadgeHTML(status, qty);
        }

        // ── 2. Cart button ───────────────────────────────────
        const addBtn = card.querySelector('.btn-add-to-cart');
        if (addBtn) {
            if (status === 'out_of_stock') {
                addBtn.disabled = true;
                addBtn.classList.add('is-disabled');
                addBtn.setAttribute('aria-disabled', 'true');
                addBtn.textContent = 'Out of Stock';
            } else {
                addBtn.disabled = false;
                addBtn.classList.remove('is-disabled');
                addBtn.setAttribute('aria-disabled', 'false');
                if (!addBtn.dataset.originalText) {
                    addBtn.dataset.originalText = addBtn.textContent;
                }
                addBtn.textContent = addBtn.dataset.originalText || 'Add to Cart';
            }
        }

        // ── 3. Card OOS overlay ──────────────────────────────
        if (status === 'out_of_stock') {
            card.classList.add('c-product-card--oos');
        } else {
            card.classList.remove('c-product-card--oos');
        }

        // ── 4. Notify Me section ─────────────────────────────
        const notifyWrap = card.querySelector('.c-notify-wrap');
        if (notifyWrap) {
            notifyWrap.style.display = status === 'out_of_stock' ? 'block' : 'none';
        }

        // Store on element for cart handler
        card.dataset.stockStatus = status;
        card.dataset.stockQty    = qty;
    }

    // ── Bulk stock fetch ──────────────────────────────────────

    async function fetchBulkStock(ids) {
        if (!ids.length) return [];
        try {
            const res  = await fetch(`${API_BASE}?action=bulk&ids=${ids.join(',')}`);
            if (!res.ok) return [];
            return await res.json();
        } catch (e) {
            console.warn('[StockUI] Bulk fetch failed:', e);
            return [];
        }
    }

    /**
     * Scan the page for product cards and refresh their stock state.
     */
    async function refreshPageStock() {
        const cards = document.querySelectorAll('[data-product-id]');
        if (!cards.length) return;

        const ids = [...new Set([...cards].map(c => c.dataset.productId).filter(Boolean))];
        const payloads = await fetchBulkStock(ids);

        const map = {};
        payloads.forEach(p => { map[p.product_id] = p; });

        cards.forEach(card => {
            const pid = card.dataset.productId;
            if (map[pid]) {
                applyStockState(card, map[pid]);
            }
        });
    }

    // ── Add to Cart guard ─────────────────────────────────────

    /**
     * Replace all static add-to-cart form submits with AJAX
     * so we can revalidate stock from the backend first.
     */
    function initAddToCartGuard() {
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-add-to-cart');
            if (!btn || btn.disabled || btn.classList.contains('is-disabled')) return;

            const card      = btn.closest('[data-product-id]');
            const productId = card?.dataset.productId ?? btn.dataset.productId;
            const quantity  = card?.dataset.quantity  ?? btn.dataset.quantity  ?? 1;
            const weight    = card?.dataset.weight    ?? btn.dataset.weight    ?? '500g';
            const variantId = card?.dataset.variantId ?? btn.dataset.variantId ?? 0;

            if (!productId) return;
            e.preventDefault();

            // Optimistic UI
            const orig = btn.textContent;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Adding…';

            try {
                const fd = new FormData();
                fd.append('product_id', productId);
                fd.append('quantity',   quantity);
                fd.append('weight',     weight);
                fd.append('variant_id', variantId);

                const res  = await fetch(CART_API, { method: 'POST', body: fd });
                const data = await res.json();

                if (data.success) {
                    btn.innerHTML = '✓ Added!';
                    btn.classList.add('is-success');

                    // Update cart count badge in header
                    const cartCount = document.querySelector('#cart-count, .js-cart-count');
                    if (cartCount && data.cart_count !== undefined) {
                        cartCount.textContent = data.cart_count;
                    }

                    setTimeout(() => {
                        btn.disabled = false;
                        btn.classList.remove('is-success');
                        btn.textContent = orig;
                    }, 1800);
                } else {
                    // Re-apply stock state if now OOS
                    if (card && data.stock_status) {
                        applyStockState(card, {
                            stock_status:   data.stock_status,
                            stock_quantity: data.stock_quantity ?? 0,
                        });
                    }
                    showToast(data.message || 'Could not add to cart', 'error');
                    btn.disabled = false;
                    btn.textContent = orig;
                }
            } catch (err) {
                console.error('[StockUI] add-to-cart error:', err);
                showToast('Network error. Please try again.', 'error');
                btn.disabled = false;
                btn.textContent = orig;
            }
        });
    }

    // ── Notify Me ─────────────────────────────────────────────

    function initNotifyMe() {
        document.addEventListener('click', (e) => {
            // Toggle notify form
            const triggerBtn = e.target.closest('.btn-notify-me');
            if (triggerBtn) {
                const card  = triggerBtn.closest('[data-product-id]');
                const form  = card?.querySelector('.c-notify-form');
                if (form) {
                    form.classList.toggle('is-open');
                    const input = form.querySelector('.c-notify-form__input');
                    if (form.classList.contains('is-open') && input) input.focus();
                }
            }
        });

        // Submit notify form
        document.addEventListener('submit', async (e) => {
            const form = e.target.closest('.c-notify-form');
            if (!form) return;
            e.preventDefault();

            const card      = form.closest('[data-product-id]');
            const productId = card?.dataset.productId;
            const email     = form.querySelector('.c-notify-form__input')?.value.trim();
            const submitBtn = form.querySelector('.c-notify-form__submit');

            if (!productId || !email) {
                showToast('Please enter a valid email.', 'error');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending…';

            const fd = new FormData();
            fd.append('action',     'notify');
            fd.append('product_id', productId);
            fd.append('email',      email);

            try {
                const res  = await fetch(API_BASE, { method: 'POST', body: fd });
                const data = await res.json();
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    form.classList.remove('is-open');
                    form.reset();
                }
            } catch (err) {
                showToast('Network error. Please try again.', 'error');
            } finally {
                submitBtn.disabled  = false;
                submitBtn.textContent = 'Notify Me';
            }
        });
    }

    // ── Toast helper ──────────────────────────────────────────

    function showToast(message, type = 'info') {
        let container = document.querySelector('.js-stock-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'js-stock-toast-container';
            container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
            document.body.appendChild(container);
        }

        const colors = {
            success : { bg: '#ecfdf5', border: '#10b981', text: '#065f46' },
            error   : { bg: '#fef2f2', border: '#ef4444', text: '#991b1b' },
            info    : { bg: '#eff6ff', border: '#3b82f6', text: '#1e40af' },
        };
        const c = colors[type] || colors.info;

        const toast = document.createElement('div');
        toast.style.cssText = `
            background:${c.bg};border-left:4px solid ${c.border};color:${c.text};
            padding:12px 18px;border-radius:8px;font-size:0.85rem;font-weight:600;
            min-width:240px;box-shadow:0 4px 16px rgba(0,0,0,0.1);
            opacity:0;transform:translateX(30px);transition:all 0.3s ease;
        `;
        toast.textContent = message;
        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.style.opacity   = '1';
            toast.style.transform = 'translateX(0)';
        });

        setTimeout(() => {
            toast.style.opacity   = '0';
            toast.style.transform = 'translateX(30px)';
            setTimeout(() => toast.remove(), 350);
        }, 3500);
    }

    // ── Public init ───────────────────────────────────────────

    function init() {
        initAddToCartGuard();
        initNotifyMe();
        // Refresh stock state for all visible product cards
        refreshPageStock();
    }

    return { init, refreshPageStock, applyStockState, buildBadgeHTML, showToast };
})();

document.addEventListener('DOMContentLoaded', StockUI.init);
