<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthenticatedSessionController;

Route::post('customer/login', [AuthenticatedSessionController::class, 'login']);
Route::get('customer/whatsapp-link', [AuthenticatedSessionController::class,'whatsappLink']);

Route::middleware(['auth:sanctum', 'onlyCustomer'])
    ->post('customer/logout', [AuthenticatedSessionController::class, 'logout']);
