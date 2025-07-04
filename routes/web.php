<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
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

require __DIR__.'/auth.php';
