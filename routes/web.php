<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\DeliveryInvoiceController;
use Illuminate\Support\Facades\Route;

// Redirect root to login if not authenticated
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// Main dashboard route that redirects based on user role
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Role-specific dashboard routes
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
    });

Route::middleware(['auth', 'verified', 'role:manager'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/manager', [DashboardController::class, 'manager'])->name('dashboard.manager');
    });

Route::middleware(['auth', 'verified', 'role:agent'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/agent', [DashboardController::class, 'agent'])->name('dashboard.agent');
    });

// Role, Permission, and User management routes (superadmin only)
Route::middleware(['auth', 'verified', 'role:superadmin'])
    ->group(function () {
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
        Route::resource('users', UserController::class);
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/list', [ProductController::class, 'index']);

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/list', [OrderController::class, 'index']);

Route::put('/orders/{id}', [OrderController::class, 'update']);

// API endpoint to fetch order statuses for frontend dropdowns
Route::get('/order-statuses/list', function () {
    return \App\Models\OrderStatus::all();
});

// Dashboard overview data
Route::get('/dashboard/overview-data', [\App\Http\Controllers\DashboardController::class, 'overviewData']);
Route::get('/dashboard/analytics-data', [\App\Http\Controllers\DashboardController::class, 'analyticsData']);

Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index']);
Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store']);

Route::get('/orders/delivery-note', [PdfController::class, 'deliveryNote']);
Route::get('/orders/invoices', [PdfController::class, 'invoices']);
Route::get('/orders/delivery-invoice', [PdfController::class, 'deliveryInvoice']);

Route::get('/delivery-invoices', [DeliveryInvoiceController::class, 'index']);
Route::get('/delivery-invoices/{id}/download', [DeliveryInvoiceController::class, 'download']);

require __DIR__.'/auth.php';
