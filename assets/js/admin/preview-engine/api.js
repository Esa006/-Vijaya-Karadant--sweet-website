/**
 * Preview Engine - API Layer
 * Handles resilient network requests with retry and timeout
 */

export class ApiService {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
        this.lastRequestId = null;
    }

    /**
     * Fetch product with automatic retry and timeout
     */
    async fetchProduct(id, options = {}) {
        const requestId = Math.random().toString(36).substring(7);
        this.lastRequestId = requestId;

        const url = `${this.baseUrl}admin/api/v1/products.php?id=${id}`;
        
        const result = await this.fetchWithRetry(url, {
            ...options,
            requestId
        });

        // Ignore if a newer request has been started
        if (this.lastRequestId !== requestId) {
            return { status: 'stale' };
        }

        return result;
    }

    /**
     * Resilient fetch with exponential backoff
     */
    async fetchWithRetry(url, options = {}, retries = 3, backoff = 1000) {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10s timeout

            const response = await fetch(url, {
                ...options,
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            
            const data = await response.json();
            return { status: 'success', data: data.data };

        } catch (error) {
            if (retries > 0 && error.name !== 'AbortError') {
                await new Promise(resolve => setTimeout(resolve, backoff));
                return this.fetchWithRetry(url, options, retries - 1, backoff * 2);
            }
            return { status: 'error', message: error.message };
        }
    }
}
