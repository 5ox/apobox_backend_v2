<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/customers/{id}', [Api\CustomerController::class, 'show']);
    Route::post('/customers', [Api\CustomerController::class, 'store']);
    Route::post('/customers/signin', [Api\CustomerController::class, 'login']);
    Route::post('/customers/{id}/notify', [Api\CustomerController::class, 'notify']);
    Route::post('/payments', [Api\CustomerController::class, 'charge']);

    Route::get('/orders/{id}', [Api\OrderController::class, 'show']);
    Route::post('/orders/{id}/add', [Api\OrderController::class, 'store']);
    Route::patch('/orders/{id}/charge', [Api\OrderController::class, 'charge']);
    Route::patch('/orders/changestatus', [Api\OrderController::class, 'changeStatus']);

    Route::post('/addresses', [Api\AddressController::class, 'store']);

    Route::get('/', [Api\ApiController::class, 'index']);
});
