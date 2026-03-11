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

Route::get('/health', \App\Http\Controllers\HealthCheckController::class);

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
    Route::get('/', [Manager\DashboardController::class, 'index']);

    // Pages
    Route::get('/pages/{page?}', [PageController::class, 'display']);

    // ---- Customers ----
    Route::get('/customers', [Manager\CustomerController::class, 'search']);
    Route::get('/customers/{billingId}/view', [Manager\CustomerController::class, 'viewByBillingId'])->where('billingId', '[A-Z]{2}\d{4}');
    Route::get('/customers/view/{id}', [Manager\CustomerController::class, 'view'])->where('id', '[0-9]+');
    Route::get('/customers/{id}/recent-orders', [Manager\CustomerController::class, 'recentOrders']);
    Route::get('/customers/{id}/edit/payment-info', [Manager\CustomerController::class, 'editPaymentInfo']);
    Route::post('/customers/{id}/edit/payment-info', [Manager\CustomerController::class, 'updatePaymentInfo']);
    Route::get('/customers/{id}/edit/contact-info', [Manager\CustomerController::class, 'editContactInfo']);
    Route::post('/customers/{id}/edit/contact-info', [Manager\CustomerController::class, 'updateContactInfo']);
    Route::get('/customers/{id}/addresses', [Manager\CustomerController::class, 'addresses']);
    Route::get('/customers/{id}/shippingAddresses', [Manager\CustomerController::class, 'shippingAddresses']);
    Route::get('/customers/{id}/edit/default-addresses', [Manager\CustomerController::class, 'editDefaultAddresses']);
    Route::post('/customers/{id}/edit/default-addresses', [Manager\CustomerController::class, 'updateDefaultAddresses']);
    Route::get('/customers/{customerId}/close-account', [Manager\CustomerController::class, 'closeAccount']);
    Route::get('/customers/quick-order', [Manager\CustomerController::class, 'quickOrder']);
    Route::post('/customers/quick-order', [Manager\CustomerController::class, 'processQuickOrder']);
    Route::get('/customers/report', [Manager\CustomerInfoController::class, 'report']);
    Route::get('/customers/demographics', [Manager\CustomerController::class, 'demographicsReport']);

    // ---- Customer's authorized names & addresses (admin context) ----
    Route::post('/customers/{customerId}/authorized_names/add', [Manager\AuthorizedNameController::class, 'store']);
    Route::get('/authorized_names/{id}/edit', [Manager\AuthorizedNameController::class, 'edit']);
    Route::post('/authorized_names/{id}/edit', [Manager\AuthorizedNameController::class, 'update']);
    Route::get('/authorized_names/{id}/delete', [Manager\AuthorizedNameController::class, 'destroy']);
    Route::post('/customers/{customerId}/address/add', [Manager\AddressController::class, 'store']);

    // ---- Orders ----
    Route::get('/orders', [Manager\OrderController::class, 'search']);
    Route::get('/orders/statustotals', [Manager\OrderController::class, 'statusTotals']);
    Route::get('/orders/report', [Manager\OrderController::class, 'report']);
    Route::get('/orders/add/{customerId}', [Manager\OrderController::class, 'add'])->where('customerId', '[0-9]+');
    Route::post('/orders/add/{customerId}', [Manager\OrderController::class, 'store']);
    Route::get('/orders/{id}', [Manager\OrderController::class, 'view'])->where('id', '[0-9]+');
    Route::get('/orders/{id}/mark-shipped', [Manager\OrderController::class, 'markAsShipped']);
    Route::post('/orders/{id}/update-status', [Manager\OrderController::class, 'updateStatus']);
    Route::get('/orders/{id}/charge', [Manager\OrderController::class, 'charge']);
    Route::get('/orders/{id}/print_label', [Manager\OrderController::class, 'printLabel']);
    Route::get('/orders/{id}/print_fedex', [Manager\OrderController::class, 'printFedex']);
    Route::get('/orders/{id}/print_fedex/{reprint}', [Manager\OrderController::class, 'printFedex']);
    Route::get('/orders/{id}/delete_label', [Manager\OrderController::class, 'deleteLabel']);
    Route::get('/orders/delete/{id}', [Manager\OrderController::class, 'deleteOrder']);
    Route::get('/orders/recent', [Manager\CustomerController::class, 'recentOrders']);

    // ---- Custom requests (admin) ----
    Route::get('/customer/{customerId}/request/add', [Manager\CustomPackageRequestController::class, 'create']);
    Route::post('/customer/{customerId}/request/add', [Manager\CustomPackageRequestController::class, 'store']);
    Route::get('/requests', [Manager\CustomPackageRequestController::class, 'index']);
    Route::get('/requests/edit/{id}', [Manager\CustomPackageRequestController::class, 'edit']);
    Route::post('/requests/edit/{id}', [Manager\CustomPackageRequestController::class, 'update']);
    Route::get('/requests/delete/{id}', [Manager\CustomPackageRequestController::class, 'destroy']);

    // ---- Tracking / Scans ----
    Route::get('/scan', [Manager\TrackingController::class, 'add']);
    Route::post('/scan', [Manager\TrackingController::class, 'store']);
    Route::get('/scan/edit/{id}', [Manager\TrackingController::class, 'edit']);
    Route::post('/scan/edit/{id}', [Manager\TrackingController::class, 'update']);
    Route::get('/scan/delete/{id}', [Manager\TrackingController::class, 'destroy']);
    Route::get('/scans', [Manager\TrackingController::class, 'search']);

    // ---- Reports ----
    Route::get('/reports/index', [Manager\ReportController::class, 'index']);

    // ---- Admins CRUD ----
    Route::get('/admins/index', [Manager\AdminController::class, 'index']);
    Route::get('/admins/add', [Manager\AdminController::class, 'create']);
    Route::post('/admins/add', [Manager\AdminController::class, 'store']);
    Route::get('/admins/edit/{id}', [Manager\AdminController::class, 'edit']);
    Route::post('/admins/edit/{id}', [Manager\AdminController::class, 'update']);
    Route::get('/admins/delete/{id}', [Manager\AdminController::class, 'destroy']);

    // ---- Logs ----
    Route::get('/logs/view/{file?}', [Manager\LogController::class, 'view']);

    // ---- Affiliate Links ----
    Route::get('/affiliate-links', [Manager\AffiliateLinkController::class, 'index']);
    Route::get('/affiliate-links/add', [Manager\AffiliateLinkController::class, 'create']);
    Route::post('/affiliate-links/add', [Manager\AffiliateLinkController::class, 'store']);
    Route::get('/affiliate-links/edit/{id}', [Manager\AffiliateLinkController::class, 'edit']);
    Route::post('/affiliate-links/edit/{id}', [Manager\AffiliateLinkController::class, 'update']);
    Route::get('/affiliate-links/delete/{id}', [Manager\AffiliateLinkController::class, 'destroy']);
};

// Manager routes
Route::prefix('manager')
    ->middleware(['auth:admin', 'warehouse.ip', 'role:manager', 'admin.layout'])
    ->group($adminRoutes);

// Employee routes (same controllers, different role)
Route::prefix('employee')
    ->middleware(['auth:admin', 'warehouse.ip', 'role:employee', 'admin.layout'])
    ->group($adminRoutes);
