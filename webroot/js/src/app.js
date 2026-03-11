// Main entry point — imports all modules.
// Each module self-activates based on DOM element presence.

// Global
import './main.js';

// Utilities (exported for use by other modules)
import './ajaxify.js';

// Page-specific modules
import './addresses/manager_add.js';
import './customers/account.js';
import './customers/almost_finished.js';
import './customers/manager_view.js';
import './elements/custom_package_requests/form.js';
import './elements/forms/inputs/customer_payment_info.js';
import './layouts/admin/navbar_items.js';
import './orders/admin_view_base.js';
import './orders/manager_add.js';
import './orders/manager_charge.js';
import './orders/pay_manually.js';
import './reports/base.js';
import './trackings/manager_add.js';
