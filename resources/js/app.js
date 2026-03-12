/**
 * APO Box Account - Main JS Entry Point
 */

// Import Bootstrap JS
import 'bootstrap';

// Import Lucide Icons
import { createIcons, icons } from 'lucide';

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
