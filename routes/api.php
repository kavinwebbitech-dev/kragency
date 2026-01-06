<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthenticatedSessionController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\WithdrawController;
use App\Http\Controllers\Api\BankDetailController;

Route::post('customer/login', [AuthenticatedSessionController::class, 'login']);
Route::get('customer/whatsapp-link', [AuthenticatedSessionController::class, 'whatsappLink']);
Route::get('customer/results', [CustomerController::class, 'results']);
Route::get('customer/game-schedule', [CustomerController::class, 'index']);
// Route::get('customer/play-now/{id}/{time_id?}', [CustomerController::class, 'playGame']);
Route::get('customer/play-now/{providerId}/{timeId?}',[CustomerController::class, 'playGameApi']);


Route::prefix('customer')->group(function () {
    Route::middleware(['auth:sanctum', 'onlyCustomer'])->group(function () {

        Route::post('logout', [AuthenticatedSessionController::class, 'logout']);
        Route::get('profile', [CustomerController::class, 'profile']);
        Route::get('wallet', [CustomerController::class, 'wallet']);
        Route::get('viewcart', [CustomerController::class, 'viewcart']);
        Route::post('cart-add', [CustomerController::class, 'addToCart']);
        Route::get('cart', [CustomerController::class, 'getCart']);
        Route::post('cart-remove', [CustomerController::class, 'removeFromCart']);
        Route::post('place-order', [CustomerController::class, 'placeOrder']);
        Route::get('orders-history', [CustomerController::class, 'customerOrderDetails']);
        Route::get('payment-history', [CustomerController::class, 'paymentHistory']);
        Route::get('withdraw', [WithdrawController::class, 'walletInfo']);
        Route::post('withdraw-request', [WithdrawController::class, 'submitRequest']);
        Route::get('withdraw-history', [WithdrawController::class, 'withdrawHistory']);
        Route::get('bank-details', [BankDetailController::class, 'show']);
        Route::post('bank-details-store', [BankDetailController::class, 'store']);
    });
});
