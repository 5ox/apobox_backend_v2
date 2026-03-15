<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Customer;
use App\Http\Controllers\PageController;

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
Route::get('/confirm-close/{customerId}/{hash}', [Customer\CustomerController::class, 'confirmClose'])->where([
    'customerId' => '[0-9]+',
    'hash' => '[a-f0-9]{40}',
]);
Route::get('/pages/{page?}', [PageController::class, 'display']);
Route::get('/tos', [PageController::class, 'tos']);
Route::get('/developers/widget', [PageController::class, 'developersWidget']);

Route::middleware(['auth:customer'])->group(function () {
    Route::get('/account', [Customer\CustomerController::class, 'account'])->name('customer.account');
    Route::get('/customers/edit/{partial}', [Customer\CustomerController::class, 'editPartial'])->where('partial', '[A-Za-z_]+');
    Route::post('/customers/edit/{partial}', [Customer\CustomerController::class, 'update']);
    Route::get('/customers/change-password', [Customer\CustomerController::class, 'changePassword']);
    Route::post('/customers/change-password', [Customer\CustomerController::class, 'updatePassword']);

    Route::get('/orders', [Customer\OrderController::class, 'index']);
    Route::get('/orders/{id}', [Customer\OrderController::class, 'show'])->where('id', '[0-9]+');
    Route::get('/orders/{id}/pay', [Customer\OrderController::class, 'payManually']);
    Route::post('/orders/{id}/pay', [Customer\OrderController::class, 'processPayment']);

    Route::get('/address/add', [Customer\AddressController::class, 'create']);
    Route::post('/addresses', [Customer\AddressController::class, 'store']);
    Route::get('/address/{id}/edit', [Customer\AddressController::class, 'edit']);
    Route::post('/address/{id}/edit', [Customer\AddressController::class, 'update']);
    Route::get('/address/{id}/delete', [Customer\AddressController::class, 'destroy']);

    Route::get('/authorized_names/add', [Customer\AuthorizedNameController::class, 'create']);
    Route::post('/authorized_names/add', [Customer\AuthorizedNameController::class, 'store']);
    Route::get('/authorized_names/{id}/edit', [Customer\AuthorizedNameController::class, 'edit']);
    Route::post('/authorized_names/{id}/edit', [Customer\AuthorizedNameController::class, 'update']);
    Route::get('/authorized_names/{id}/delete', [Customer\AuthorizedNameController::class, 'destroy']);

    Route::post('/support/tickets', [Customer\CustomerController::class, 'createTicket']);
    Route::get('/support/tickets/{id}/comments', [Customer\CustomerController::class, 'ticketComments'])->where('id', '[0-9]+');
    Route::post('/support/tickets/{id}/reply', [Customer\CustomerController::class, 'replyToTicket'])->where('id', '[0-9]+');

    Route::get('/requests', [Customer\CustomPackageRequestController::class, 'index']);
    Route::get('/requests/add', [Customer\CustomPackageRequestController::class, 'create']);
    Route::post('/requests/add', [Customer\CustomPackageRequestController::class, 'store']);
    Route::get('/requests/edit/{id}', [Customer\CustomPackageRequestController::class, 'edit']);
    Route::post('/requests/edit/{id}', [Customer\CustomPackageRequestController::class, 'update']);
    Route::get('/requests/delete/{id}', [Customer\CustomPackageRequestController::class, 'destroy']);
});
