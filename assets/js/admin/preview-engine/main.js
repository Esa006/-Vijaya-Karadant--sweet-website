/**
 * Preview Engine - Entry Point (main.js)
 * High-fidelity, production-grade modular system
 */

import { PreviewEngine } from './engine.js';
import { setupEvents } from './events.js';

// Configuration
const config = {
    baseUrl: window.BASE_URL || '/',
    containerId: 'productPreviewModal'
};

// Initialize Engine
const engine = new PreviewEngine(config);

// Setup Event Bridge
setupEvents(engine);

// Expose to window for legacy onclick attributes in PHP
window.PreviewEngine = engine;

// Compatibility wrapper for existing code
window.openPreviewMode = (data, type = 'product') => {
    engine.open(data, type);
};

console.log('🚀 Product Preview Engine Initialized');
