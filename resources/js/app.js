/**
 * APO Box Account - Main JS Entry Point
 *
 * This is the Vite entry point for the application JavaScript.
 * Individual page modules are loaded on-demand via dynamic imports.
 */

// Import Bootstrap JS
import 'bootstrap';

// Auto-initialize page-specific modules based on data attributes
document.addEventListener('DOMContentLoaded', () => {
    const pageModule = document.body.dataset.module;
    if (pageModule) {
        import(`./modules/${pageModule}.js`).catch(() => {
            // Module not found — no page-specific JS needed
        });
    }
});
