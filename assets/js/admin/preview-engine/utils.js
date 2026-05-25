/**
 * Preview Engine - Utilities
 * Industrial-grade helper functions
 */

export const utils = {
    /**
     * Format currency to Indian Rupee (INR)
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(amount);
    },

    /**
     * Debounce function to prevent rapid-fire execution
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Generate a unique request ID to prevent race conditions
     */
    generateRequestId() {
        return Math.random().toString(36).substring(2, 15);
    },

    /**
     * Deep comparison of two objects (simplified for state)
     */
    isEqual(obj1, obj2) {
        return JSON.stringify(obj1) === JSON.stringify(obj2);
    }
};
