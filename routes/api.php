<?php

use App\Http\Controllers\AlertsController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\PurchaseItemController;
use App\Http\Controllers\Api\RefundController;
use App\Http\Controllers\Api\DevisController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SaleItemController;
use App\Http\Controllers\Api\StockmovmentController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\CompanySettingsController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::middleware('auth:api')->group(function () {
    // Route::middleware('permissions:[view_products]')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('auth.profile.update');
    Route::put('/password', [AuthController::class, 'resetPassword'])->name('auth.password.reset');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');

    // Company Settings Routes
    Route::prefix('settings')->group(function () {
        Route::get('/company', [CompanySettingsController::class, 'show'])->name('settings.company.show');
        Route::put('/company', [CompanySettingsController::class, 'update'])->name('settings.company.update');
        Route::post('/company/logo/delete', [CompanySettingsController::class, 'deleteLogo'])->name('settings.company.logo.delete');
    });

    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::prefix('reports')->controller(ReportsController::class)->group(function () {
        Route::get('/financial', 'financial')->name('reports.financial');
        Route::get('/inventory', 'inventory')->name('reports.inventory');
        Route::get('/sales', 'sales')->name('reports.sales');
        Route::get('/devis', 'devis')->name('reports.devis');
        Route::get('/purchasing', 'purchasing')->name('reports.purchasing');
    });

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('devis', DevisController::class);
    Route::apiResource('purchases', PurchaseController::class);
    Route::apiResource('purchase-items', PurchaseItemController::class);
    Route::apiResource('sales', SaleController::class);
    Route::apiResource('sale-items', SaleItemController::class);
    Route::apiResource('stock-movements', StockmovmentController::class)->only(['index']);
    Route::apiResource('refunds', RefundController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('suppliers', SupplierController::class);
    Route::post('/invoices/{id}/generate', [InvoiceController::class, 'generateInvoice'])->name('invoice');
    Route::post('/invoices/purchases/{id}/generate', [InvoiceController::class, 'generatePurchaseInvoice'])->name('invoice.purchase');

    Route::post('/devis/{devis}/send', [DevisController::class, 'send'])->name('devis.send');
    Route::post('/devis/{devis}/accept', [DevisController::class, 'accept'])->name('devis.accept');
    Route::post('/devis/{devis}/reject', [DevisController::class, 'reject'])->name('devis.reject');
    Route::post('/devis/{devis}/convert-to-sale', [DevisController::class, 'convertToSale'])->name('devis.convert');
    Route::get('/devis/{devis}/pdf', [DevisController::class, 'pdf'])->name('devis.pdf');
    // });

    // CSV routes 
    Route::get('products/export/csv', [ProductController::class, 'exportCsv']);
    Route::post('products/import/csv', [ProductController::class, 'import']);
    
    
    Route::middleware('is.admin')->group(function () {
        Route::post('/users/{id}/permissions', [UserController::class, 'assignPermissions'])->name('users.permissions');
        Route::get('/permissions', [UserController::class, 'getAvailablePermissions'])->name('permissions.index');
        Route::apiResource('users', UserController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        // AI CHAT 
        
        
        // alerts stock 
        Route::get('/alerts', [AlertsController::class, 'getAlerts']);
        Route::put('/alerts/{alert}/read', [AlertsController::class, 'markAsRead']);
        });
        });
        
        Route::post('/ai/chat', [AiChatController::class, 'chat']);
