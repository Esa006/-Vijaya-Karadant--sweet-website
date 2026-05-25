/**
 * Sweets Website - Hardened Admin API Client
 * =============================================================
 * Responsibilities: 
 * - Base URL management
 * - CSRF injection
 * - Timeout handling (8s)
 * - Request deduplication
 * - Error normalization
 * =============================================================
 */

class AdminAPI {
    constructor(options = {}) {
        // Automatically detect base path for admin API
        const pathSegments = window.location.pathname.split('/');
        const adminIndex = pathSegments.indexOf('admin');
        const basePath = adminIndex !== -1 ? pathSegments.slice(0, adminIndex).join('/') : '';
        
        this.baseURL = options.baseURL || `${basePath}/admin/api/v1`;
        this.timeout = options.timeout || 8000;
        this.inFlight = new Set();
    }

    /**
     * Centralized Request Handler
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const requestId = `${options.method || 'GET'}:${url}`;

        // 1. Deduplication (Ignore duplicate in-flight requests)
        if (this.inFlight.has(requestId)) {
            console.warn(`[AdminAPI] Deduplicated request: ${requestId}`);
            return { success: false, handled: true, message: 'Request already in progress' };
        }

        this.inFlight.add(requestId);

        // 2. Add Headers (CSRF + Auth)
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken,
            ...options.headers
        };

        // 3. Timeout Controller
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.timeout);

        try {
            const response = await fetch(url, {
                ...options,
                headers,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                return this.handleError(response.status, errorData.message);
            }

            const data = await response.json();
            return { success: true, data: data.data };

        } catch (error) {
            if (error.name === 'AbortError') {
                return { success: false, error: { type: 'timeout', message: 'Request timed out' } };
            }
            return { success: false, error: { type: 'system', message: 'Network or System Error' } };
        } finally {
            this.inFlight.delete(requestId);
        }
    }

    handleError(status, message) {
        let type = 'system';
        if (status === 401) type = 'auth';
        if (status === 403) type = 'forbidden';
        if (status === 422) type = 'validation';

        return {
            success: false,
            error: { type, message: message || 'An unexpected error occurred' }
        };
    }

    // Convenience Methods
    get(endpoint) { return this.request(endpoint, { method: 'GET' }); }
    post(endpoint, data) { return this.request(endpoint, { method: 'POST', body: JSON.stringify(data) }); }
    put(endpoint, data) { return this.request(endpoint, { method: 'PUT', body: JSON.stringify(data) }); }
    patch(endpoint, data) { return this.request(endpoint, { method: 'PATCH', body: JSON.stringify(data) }); }
    delete(endpoint) { return this.request(endpoint, { method: 'DELETE' }); }
}

// Global Export
window.adminAPI = new AdminAPI();
