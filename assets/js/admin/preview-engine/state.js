/**
 * Preview Engine - State Manager
 * Central source of truth with reactive updates
 */

export class StateManager {
    constructor(initialState = {}, onStateChange) {
        this.state = {
            product: null,
            selectedVariant: null,
            loading: false,
            error: null,
            lastUpdated: null,
            ...initialState
        };
        this.prevState = { ...this.state };
        this.onStateChange = onStateChange;
    }

    /**
     * Update state and trigger re-render
     */
    setState(partialState) {
        this.prevState = { ...this.state };
        this.state = { ...this.state, ...partialState };
        
        // Auto-select variant if product changed and no variant selected
        if (this.state.product && !this.state.selectedVariant && this.state.product.variants) {
            this.state.selectedVariant = this.state.product.variants.find(v => v.stock > 0) || this.state.product.variants[0];
        }

        if (this.onStateChange) {
            this.onStateChange(this.state, this.prevState);
        }
    }

    getState() {
        return this.state;
    }
}
