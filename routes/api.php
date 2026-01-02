<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthenticatedSessionController;
use App\Http\Controllers\Api\CustomerController;

Route::post('customer/login', [AuthenticatedSessionController::class, 'login']);
Route::get('customer/whatsapp-link', [AuthenticatedSessionController::class, 'whatsappLink']);
Route::get('customer/results', [CustomerController::class, 'results']);
Route::get('customer/play-now/{providerId}/{timeId?}', [CustomerController::class, 'playGame']);


Route::prefix('customer')->group(function () {
    Route::middleware(['auth:sanctum', 'onlyCustomer'])->group(function () {

        Route::post('logout', [AuthenticatedSessionController::class, 'logout']);
        Route::get('wallet', [CustomerController::class, 'index']);
        Route::post('place-order', [CustomerController::class, 'placeOrder']);
        Route::get('payment-history', [CustomerController::class, 'paymentHistory']);
        Route::post('cart/add', [CustomerController::class, 'addToCart']);
        Route::get('cart', [CustomerController::class, 'getCart']);
        Route::post('cart/remove', [CustomerController::class, 'removeFromCart']);
        Route::get('orders', [CustomerController::class, 'orderDetails']);
        Route::get('rules', [CustomerController::class, 'rules']);
    });
});
