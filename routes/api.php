<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\PurchaseItemController;
use App\Http\Controllers\Api\RefundController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SaleItemController;
use App\Http\Controllers\Api\StockmovmentController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('auth.profile.update');
    Route::put('/password', [AuthController::class, 'resetPassword'])->name('auth.password.reset');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');

    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('purchases', PurchaseController::class);
    Route::apiResource('purchase-items', PurchaseItemController::class);
    Route::apiResource('sales', SaleController::class);
    Route::apiResource('sale-items', SaleItemController::class);
    Route::apiResource('stock-movements', StockmovmentController::class)->only(['index']);
    Route::apiResource('refunds', RefundController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('suppliers', SupplierController::class);
});
