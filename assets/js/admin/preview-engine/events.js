/**
 * Preview Engine - Event Handlers
 * Bridges user interactions to the Engine
 */

export function setupEvents(engine) {
    /**
     * Variant Selection
     */
    window.selectVariant = (id) => engine.selectVariant(id);

    /**
     * Edit Action from Preview
     */
    window.editFromPreview = () => {
        const state = engine.stateManager.getState();
        if (state.product && typeof window.openEditProduct === 'function') {
            // Close preview modal first
            const modalEl = document.getElementById(engine.config.containerId);
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            
            // Open edit offcanvas/modal
            window.openEditProduct(state.product);
        }
    };

    /**
     * Keyboard Navigation Support
     */
    document.addEventListener('keydown', (e) => {
        const state = engine.stateManager.getState();
        if (!state.product || state.loading) return;

        // Example: ESC to close is handled by Bootstrap, 
        // but we could add custom shortcuts here.
    });
}
