/**
 * APO Box Account - Main JS Entry Point
 */

// Import Bootstrap JS (expose globally for inline scripts)
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Import Lucide Icons (expose globally for inline scripts)
import { createIcons, icons } from 'lucide';
window.lucide = { createIcons, icons };

// Initialize everything on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide icons
    createIcons({ icons });

    // Auto-initialize page-specific modules based on data attributes
    const pageModule = document.body.dataset.module;
    if (pageModule) {
        import(`./modules/${pageModule}.js`).catch(() => {
            // Module not found — no page-specific JS needed
        });
    }
});
