<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Customer;
use App\Http\Controllers\Manager;
use App\Http\Controllers\PageController;

// =========================================================================
// Public routes
// =========================================================================

// Health check for Railway (no auth, no middleware)
Route::get('/health', fn () => response('ok', 200));

Route::redirect('/', '/account');
Route::get('/login', [CustomerLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [CustomerLoginController::class, 'login']);
Route::get('/logout', [CustomerLoginController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [CustomerLoginController::class, 'showForgotPassword']);
Route::post('/forgot-password', [CustomerLoginController::class, 'forgotPassword']);
Route::get('/reset-password/{uuid}', [CustomerLoginController::class, 'showResetPassword']);
Route::post('/reset-password/{uuid}', [CustomerLoginController::class, 'resetPassword']);
Route::post('/customers', [CustomerLoginController::class, 'register'])->name('customers.register');
Route::get('/customers/account-incomplete', [Customer\CustomerController::class, 'accountIncomplete']);
Route::get('/customers/almost-finished', [Customer\CustomerController::class, 'almostFinished']);
Route::get('/close-account/{hash}', [Customer\CustomerController::class, 'closeAccount'])->where('hash', '[a-f0-9]{40}');
Route::get('/confirm-close/{customerId}/{hash}', [Customer\CustomerController::class, 'confirmClose'])->where(['customerId' => '[0-9]+', 'hash' => '[a-f0-9]{40}']);
Route::get('/pages/{page?}', [PageController::class, 'display']);
Route::get('/tos', [PageController::class, 'tos']);
Route::get('/developers/widget', [PageController::class, 'developersWidget']);

// =========================================================================
// Admin auth (no IP restriction)
// =========================================================================

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login']);
    Route::get('/logout', [AdminLoginController::class, 'logout']);
    Route::get('/login-google', [GoogleLoginController::class, 'redirect']);
    Route::get('/login-google/callback', [GoogleLoginController::class, 'callback']);
});
Route::redirect('/admin', '/admin/login');

// =========================================================================
// Customer authenticated routes
// =========================================================================

Route::middleware(['auth:customer'])->group(function () {
    Route::get('/account', [Customer\CustomerController::class, 'account'])->name('customer.account');
    Route::get('/customers/edit/{partial}', [Customer\CustomerController::class, 'editPartial'])->where('partial', '[A-Za-z_]+');
    Route::post('/customers/edit/{partial}', [Customer\CustomerController::class, 'update']);
    Route::get('/customers/change-password', [Customer\CustomerController::class, 'changePassword']);
    Route::post('/customers/change-password', [Customer\CustomerController::class, 'updatePassword']);

    // Orders
    Route::get('/orders', [Customer\OrderController::class, 'index']);
    Route::get('/orders/{id}', [Customer\OrderController::class, 'show'])->where('id', '[0-9]+');
    Route::get('/orders/{id}/pay', [Customer\OrderController::class, 'payManually']);
    Route::post('/orders/{id}/pay', [Customer\OrderController::class, 'processPayment']);

    // Addresses
    Route::get('/address/add', [Customer\AddressController::class, 'create']);
    Route::post('/addresses', [Customer\AddressController::class, 'store']);
    Route::get('/address/{id}/edit', [Customer\AddressController::class, 'edit']);
    Route::post('/address/{id}/edit', [Customer\AddressController::class, 'update']);
    Route::get('/address/{id}/delete', [Customer\AddressController::class, 'destroy']);

    // Authorized Names
    Route::post('/authorized_names/add', [Customer\AuthorizedNameController::class, 'store']);
    Route::get('/authorized_names/{id}/edit', [Customer\AuthorizedNameController::class, 'edit']);
    Route::post('/authorized_names/{id}/edit', [Customer\AuthorizedNameController::class, 'update']);
    Route::get('/authorized_names/{id}/delete', [Customer\AuthorizedNameController::class, 'destroy']);

    // Custom Requests
    Route::get('/requests', [Customer\CustomPackageRequestController::class, 'index']);
    Route::get('/requests/add', [Customer\CustomPackageRequestController::class, 'create']);
    Route::post('/requests/add', [Customer\CustomPackageRequestController::class, 'store']);
    Route::get('/requests/edit/{id}', [Customer\CustomPackageRequestController::class, 'edit']);
    Route::post('/requests/edit/{id}', [Customer\CustomPackageRequestController::class, 'update']);
    Route::get('/requests/delete/{id}', [Customer\CustomPackageRequestController::class, 'destroy']);
});

// =========================================================================
// Admin routes (shared between manager and employee prefixes)
// =========================================================================

$adminRoutes = function () {
    Route::get('/', [Manager\DashboardController::class, 'index'])->name('dashboard');

    // Pages
    Route::get('/pages/{page?}', [PageController::class, 'display']);

    // ---- Customers ----
    Route::get('/customers', [Manager\CustomerController::class, 'search'])->name('customers.search');
    Route::get('/customers/{billingId}/view', [Manager\CustomerController::class, 'viewByBillingId'])->where('billingId', '[A-Za-z]{2}\d{4}')->name('customers.view-billing');
    Route::get('/customers/view/{id}', [Manager\CustomerController::class, 'view'])->where('id', '[0-9]+')->name('customers.view');
    Route::get('/customers/{id}/recent-orders', [Manager\CustomerController::class, 'recentOrders'])->name('customers.recent-orders');
    Route::get('/customers/{id}/edit/payment-info', [Manager\CustomerController::class, 'editPaymentInfo'])->name('customers.edit-payment');
    Route::post('/customers/{id}/edit/payment-info', [Manager\CustomerController::class, 'updatePaymentInfo'])->name('customers.update-payment');
    Route::get('/customers/{id}/edit/contact-info', [Manager\CustomerController::class, 'editContactInfo'])->name('customers.edit-contact');
    Route::post('/customers/{id}/edit/contact-info', [Manager\CustomerController::class, 'updateContactInfo'])->name('customers.update-contact');
    Route::get('/customers/{id}/addresses', [Manager\CustomerController::class, 'addresses']);
    Route::get('/customers/{id}/shippingAddresses', [Manager\CustomerController::class, 'shippingAddresses']);
    Route::get('/customers/{id}/edit/default-addresses', [Manager\CustomerController::class, 'editDefaultAddresses'])->name('customers.edit-addresses');
    Route::post('/customers/{id}/edit/default-addresses', [Manager\CustomerController::class, 'updateDefaultAddresses'])->name('customers.update-addresses');
    Route::get('/customers/{customerId}/close-account', [Manager\CustomerController::class, 'closeAccount'])->name('customers.close');
    Route::post('/customers/{customerId}/zendesk-ticket', [Manager\CustomerController::class, 'createZendeskTicket'])->name('customers.zendesk-ticket');
    Route::get('/customers/quick-order', [Manager\CustomerController::class, 'quickOrder'])->name('customers.quick-order');
    Route::post('/customers/quick-order', [Manager\CustomerController::class, 'processQuickOrder'])->name('customers.quick-order.process');
    Route::get('/customers/report', [Manager\CustomerInfoController::class, 'report'])->name('customers.report');
    Route::get('/customers/demographics', [Manager\CustomerController::class, 'demographicsReport'])->name('customers.demographics');

    // ---- Customer's authorized names & addresses (admin context) ----
    Route::post('/customers/{customerId}/authorized_names/add', [Manager\AuthorizedNameController::class, 'store'])->name('customers.authorized-names.store');
    Route::get('/authorized_names/{id}/edit', [Manager\AuthorizedNameController::class, 'edit'])->name('authorized-names.edit');
    Route::post('/authorized_names/{id}/edit', [Manager\AuthorizedNameController::class, 'update'])->name('authorized-names.update');
    Route::get('/authorized_names/{id}/delete', [Manager\AuthorizedNameController::class, 'destroy'])->name('authorized-names.delete');
    Route::post('/customers/{customerId}/address/add', [Manager\AddressController::class, 'store'])->name('customers.address.store');

    // ---- Orders ----
    Route::get('/orders', [Manager\OrderController::class, 'search'])->name('orders.search');
    Route::get('/orders/statustotals', [Manager\OrderController::class, 'statusTotals'])->name('orders.status-totals');
    Route::get('/orders/report', [Manager\OrderController::class, 'report'])->name('orders.report');
    Route::get('/orders/add/{customerId}', [Manager\OrderController::class, 'add'])->where('customerId', '[0-9]+')->name('orders.add');
    Route::post('/orders/add/{customerId}', [Manager\OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{id}', [Manager\OrderController::class, 'view'])->where('id', '[0-9]+')->name('orders.view');
    Route::get('/orders/{id}/mark-shipped', [Manager\OrderController::class, 'markAsShipped'])->name('orders.mark-shipped');
    Route::post('/orders/{id}/update-status', [Manager\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::match(['get', 'post'], '/orders/{id}/charge', [Manager\OrderController::class, 'charge'])->name('orders.charge');
    Route::get('/orders/{id}/print_label', [Manager\OrderController::class, 'printLabel'])->name('orders.print-label');
    Route::get('/orders/{id}/print_fedex', [Manager\OrderController::class, 'printFedex'])->name('orders.print-fedex');
    Route::get('/orders/{id}/print_fedex/{reprint}', [Manager\OrderController::class, 'printFedex'])->name('orders.print-fedex-reprint');
    Route::get('/orders/{id}/print_ups', [Manager\OrderController::class, 'printUps'])->name('orders.print-ups');
    Route::get('/orders/{id}/print_ups/{reprint}', [Manager\OrderController::class, 'printUps'])->name('orders.print-ups-reprint');
    Route::get('/orders/{id}/delete_label', [Manager\OrderController::class, 'deleteLabel'])->name('orders.delete-label');
    Route::get('/orders/delete/{id}', [Manager\OrderController::class, 'deleteOrder'])->name('orders.delete');
    Route::post('/orders/{id}/zendesk-ticket', [Manager\OrderController::class, 'createZendeskTicket'])->name('orders.zendesk-ticket');
    Route::get('/orders/recent', [Manager\CustomerController::class, 'recentOrders'])->name('orders.recent');

    // ---- Custom requests (admin) ----
    Route::get('/customer/{customerId}/request/add', [Manager\CustomPackageRequestController::class, 'create'])->name('requests.create');
    Route::post('/customer/{customerId}/request/add', [Manager\CustomPackageRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests', [Manager\CustomPackageRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/edit/{id}', [Manager\CustomPackageRequestController::class, 'edit'])->name('requests.edit');
    Route::post('/requests/edit/{id}', [Manager\CustomPackageRequestController::class, 'update'])->name('requests.update');
    Route::get('/requests/delete/{id}', [Manager\CustomPackageRequestController::class, 'destroy'])->name('requests.delete');

    // ---- Tracking / Scans ----
    Route::get('/scan', [Manager\TrackingController::class, 'add'])->name('tracking.add');
    Route::post('/scan', [Manager\TrackingController::class, 'store'])->name('tracking.store');
    Route::get('/scan/edit/{id}', [Manager\TrackingController::class, 'edit'])->name('tracking.edit');
    Route::post('/scan/edit/{id}', [Manager\TrackingController::class, 'update'])->name('tracking.update');
    Route::get('/scan/delete/{id}', [Manager\TrackingController::class, 'destroy'])->name('tracking.delete');
    Route::get('/scans', [Manager\TrackingController::class, 'search'])->name('tracking.search');

    // ---- Reports ----
    Route::get('/reports/index', [Manager\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/api/summary', [Manager\ReportApiController::class, 'summary'])->name('reports.api.summary');
    Route::get('/reports/api/trends', [Manager\ReportApiController::class, 'trends'])->name('reports.api.trends');
    Route::get('/reports/api/customers', [Manager\ReportApiController::class, 'customers'])->name('reports.api.customers');
    Route::get('/reports/api/orders', [Manager\ReportApiController::class, 'orders'])->name('reports.api.orders');
    Route::get('/reports/api/export', [Manager\ReportApiController::class, 'export'])->name('reports.api.export');

    // ---- Admins CRUD ----
    Route::get('/admins/index', [Manager\AdminController::class, 'index'])->name('admins.index');
    Route::get('/admins/add', [Manager\AdminController::class, 'create'])->name('admins.create');
    Route::post('/admins/add', [Manager\AdminController::class, 'store'])->name('admins.store');
    Route::get('/admins/edit/{id}', [Manager\AdminController::class, 'edit'])->name('admins.edit');
    Route::post('/admins/edit/{id}', [Manager\AdminController::class, 'update'])->name('admins.update');
    Route::get('/admins/delete/{id}', [Manager\AdminController::class, 'destroy'])->name('admins.delete');

    // ---- Tools (run artisan commands from web) ----
    Route::get('/tools', [Manager\ToolController::class, 'index'])->name('tools.index');
    Route::post('/tools/run/{command}', [Manager\ToolController::class, 'run'])->name('tools.run');

    // ---- Logs ----
    Route::get('/logs/view/{file?}', [Manager\LogController::class, 'view'])->name('logs.view');

    // ---- Affiliate Links ----
    Route::get('/affiliate-links', [Manager\AffiliateLinkController::class, 'index'])->name('affiliate-links.index');
    Route::get('/affiliate-links/add', [Manager\AffiliateLinkController::class, 'create'])->name('affiliate-links.create');
    Route::post('/affiliate-links/add', [Manager\AffiliateLinkController::class, 'store'])->name('affiliate-links.store');
    Route::get('/affiliate-links/edit/{id}', [Manager\AffiliateLinkController::class, 'edit'])->name('affiliate-links.edit');
    Route::post('/affiliate-links/edit/{id}', [Manager\AffiliateLinkController::class, 'update'])->name('affiliate-links.update');
    Route::get('/affiliate-links/delete/{id}', [Manager\AffiliateLinkController::class, 'destroy'])->name('affiliate-links.delete');
};

// Manager routes
Route::prefix('manager')
    ->name('manager.')
    ->middleware(['auth:admin', 'warehouse.ip', 'role:manager', 'admin.layout'])
    ->group($adminRoutes);

// Employee routes (same controllers, different role)
Route::prefix('employee')
    ->name('employee.')
    ->middleware(['auth:admin', 'warehouse.ip', 'role:employee', 'admin.layout'])
    ->group($adminRoutes);
