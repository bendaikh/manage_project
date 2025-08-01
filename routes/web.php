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
use App\Http\Controllers\OrderImportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\IncomesController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\TransfersController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\OrderAssignmentController;
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
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/products/{product}/edit', [ProductController::class, 'edit']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/list', [OrderController::class, 'index']);

Route::put('/orders/{id}', [OrderController::class, 'update']);
Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

// Order Import Routes
Route::post('/orders/import', [OrderImportController::class, 'import']);
Route::get('/orders/import/template', [OrderImportController::class, 'downloadTemplate']);
Route::get('/orders/import/stats', [OrderImportController::class, 'getImportStats']);

// API endpoint to fetch order statuses for frontend dropdowns
Route::get('/order-statuses/list', function () {
    return \App\Models\OrderStatus::all();
});

// Dashboard overview data
Route::get('/dashboard/overview-data', [\App\Http\Controllers\DashboardController::class, 'overviewData']);
Route::get('/dashboard/analytics-data', [\App\Http\Controllers\DashboardController::class, 'analyticsData']);

Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index']);
Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store']);
Route::put('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update']);
Route::delete('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy']);

Route::get('/orders/delivery-note', [PdfController::class, 'deliveryNote']);
Route::get('/orders/invoices', [PdfController::class, 'invoices']);
Route::get('/orders/delivery-invoice', [PdfController::class, 'deliveryInvoice']);

Route::get('/delivery-invoices', [DeliveryInvoiceController::class, 'index']);
Route::get('/delivery-invoices/{id}/download', [DeliveryInvoiceController::class, 'download']);

// Settings Routes
Route::middleware(['auth', 'verified', 'role:superadmin'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/update', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/get', [SettingsController::class, 'getSettings'])->name('settings.get');
    Route::delete('/settings/logo', [SettingsController::class, 'deleteLogo'])->name('settings.deleteLogo');
});

// Accounting Routes
Route::middleware(['auth', 'verified'])->prefix('accounting')->name('accounting.')->group(function () {
    // Balance page
    Route::get('/balance', [AccountingController::class, 'balance'])->name('balance');
    // Balance data JSON
    Route::get('/balance-data', [AccountingController::class, 'balanceData'])->name('balance.data');
    // Incomes module
    Route::prefix('incomes')->name('incomes.')->group(function () {
        Route::get('/', [IncomesController::class, 'index'])->name('index');
        Route::get('/create', [IncomesController::class, 'create'])->name('create');
        Route::post('/', [IncomesController::class, 'store'])->name('store');
        Route::get('/{income}/edit', [IncomesController::class, 'edit'])->name('edit');
        Route::put('/{income}', [IncomesController::class, 'update'])->name('update');
        Route::delete('/{income}', [IncomesController::class, 'destroy'])->name('destroy');
        
        // Income categories
        Route::get('/categories', [IncomesController::class, 'categories'])->name('categories');
        Route::post('/categories', [IncomesController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [IncomesController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [IncomesController::class, 'destroyCategory'])->name('categories.destroy');
    });
    
    // Expenses module
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [ExpensesController::class, 'index'])->name('index');
        Route::get('/create', [ExpensesController::class, 'create'])->name('create');
        Route::post('/', [ExpensesController::class, 'store'])->name('store');
        Route::get('/{expense}/edit', [ExpensesController::class, 'edit'])->name('edit');
        Route::put('/{expense}', [ExpensesController::class, 'update'])->name('update');
        Route::delete('/{expense}', [ExpensesController::class, 'destroy'])->name('destroy');
        
        // Expense categories
        Route::get('/categories', [ExpensesController::class, 'categories'])->name('categories');
        Route::post('/categories', [ExpensesController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [ExpensesController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [ExpensesController::class, 'destroyCategory'])->name('categories.destroy');
        
        // Refunds
        Route::get('/refunds', [ExpensesController::class, 'refunds'])->name('refunds');
        Route::post('/refunds', [ExpensesController::class, 'storeRefund'])->name('refunds.store');
        Route::put('/refunds/{refund}', [ExpensesController::class, 'updateRefund'])->name('refunds.update');
        Route::delete('/refunds/{refund}', [ExpensesController::class, 'destroyRefund'])->name('refunds.destroy');
    });
    
    // Transfers
    Route::resource('transfers', TransfersController::class);
    
    // User Transfers
    Route::get('/user-transfers', [\App\Http\Controllers\UserTransferController::class, 'index'])->name('user-transfers.index');
    Route::post('/user-transfers', [\App\Http\Controllers\UserTransferController::class, 'store'])->name('user-transfers.store');
    Route::put('/user-transfers/{userTransfer}', [\App\Http\Controllers\UserTransferController::class, 'update'])->name('user-transfers.update');
    Route::delete('/user-transfers/{userTransfer}', [\App\Http\Controllers\UserTransferController::class, 'destroy'])->name('user-transfers.destroy');
    
    // Test route to check if users exist
    Route::get('/test-users', function() {
        $users = \App\Models\User::with('roles')->get();
        return response()->json($users);
    });
    
    // Accounts
    Route::resource('accounts', AccountController::class);
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
});

// API route for getting users by role (outside accounting prefix)
Route::get('/api/users/by-role/{roleName}', [\App\Http\Controllers\UserTransferController::class, 'getUsersByRole'])->middleware('auth');

// AI Chat API Route
Route::post('/api/ai-chat', [AiChatController::class, 'chat'])->middleware(['auth', 'verified']);

// Test route to check database
Route::get('/test-db', function() {
    $roles = \App\Models\Role::all();
    $users = \App\Models\User::with('roles')->get();
    
    $result = [
        'roles' => $roles->pluck('name'),
        'users' => $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')
            ];
        })
    ];
    
    return response()->json($result);
});

// Simple test route for agent role
Route::get('/test-agent', function() {
    $role = \App\Models\Role::where('name', 'agent')->first();
    if (!$role) {
        return response()->json(['error' => 'Agent role not found']);
    }
    
    $users = \App\Models\User::whereHas('roles', function ($query) use ($role) {
        $query->where('role_id', $role->id);
    })->get(['id', 'name', 'email']);
    
    return response()->json([
        'role' => $role->name,
        'users' => $users
    ]);
});

require __DIR__.'/auth.php';

// Ensure orders resource routes are defined for orders.index and others
Route::resource('orders', App\Http\Controllers\OrderController::class);

// Ensure products resource routes are defined for products.index and others
Route::resource('products', App\Http\Controllers\ProductController::class);

// Ensure accounting index route is defined for accounting.index
Route::get('/accounting', [App\Http\Controllers\AccountingController::class, 'index'])->name('accounting.index');

// Reports Routes
Route::middleware(['auth', 'verified'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', function () {
        return view('reports.index');
    })->name('index');
});

// Agent-specific routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/orders/process', function () {
        return view('orders.process');
    })->name('orders.process');
    
    Route::get('/support/tickets', function () {
        return view('support.tickets');
    })->name('support.tickets');
    
    Route::get('/products/catalog', function () {
        return view('products.catalog');
    })->name('products.catalog');
});

// Order Assignment Routes
Route::middleware(['auth', 'verified'])->prefix('api/order-assignments')->group(function () {
    // Get available agents (for superadmin)
    Route::get('/agents', [OrderAssignmentController::class, 'getAgents']);
    
    // Assign orders to agents (superadmin only)
    Route::post('/assign', [OrderAssignmentController::class, 'assignOrders'])
        ->middleware('permission:manage_orders');
    
    // Remove assignment from orders (superadmin only)
    Route::post('/remove', [OrderAssignmentController::class, 'removeAssignment'])
        ->middleware('permission:manage_orders');
    
    // Get assignment statistics (superadmin only)
    Route::get('/stats', [OrderAssignmentController::class, 'getAssignmentStats'])
        ->middleware('permission:manage_orders');
    
    // Get all orders with assignments (superadmin only)
    Route::get('/all-orders', [OrderAssignmentController::class, 'getAllOrdersWithAssignments'])
        ->middleware('permission:manage_orders');
    
    // Get my assigned orders (for agents)
    Route::get('/my-orders', [OrderAssignmentController::class, 'getMyAssignedOrders'])
        ->middleware('permission:view_orders');
});

// Shipments API routes
Route::middleware(['auth'])->group(function () {
    Route::get('/shipments', [\App\Http\Controllers\ShipmentController::class, 'index']);
    Route::post('/shipments', [\App\Http\Controllers\ShipmentController::class, 'store']);
    Route::get('/shipments/{id}', [\App\Http\Controllers\ShipmentController::class, 'show']);
    Route::post('/shipments/{id}/validate', [\App\Http\Controllers\ShipmentController::class, 'validateShipment']);
    Route::put('/shipments/{id}', [\App\Http\Controllers\ShipmentController::class, 'update']);
    Route::delete('/shipments/{id}', [\App\Http\Controllers\ShipmentController::class, 'destroy']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/stocks', [\App\Http\Controllers\StockController::class, 'index']);
});

Route::get('/seller-invoices', [\App\Http\Controllers\SellerInvoiceController::class, 'index']);
Route::get('/seller-invoices/{id}/download', [\App\Http\Controllers\SellerInvoiceController::class, 'download']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/history', [\App\Http\Controllers\HistoryController::class, 'index'])->name('history.index');
    Route::get('/api/history', function () {
        $perPage = request('per_page', 20);
        return \App\Models\ActionHistory::with('user')->orderByDesc('created_at')->paginate($perPage);
    });
    Route::get('/api/history/latest', function () {
        $latest = \App\Models\ActionHistory::with('user')->orderByDesc('created_at')->take(10)->get();
        return response()->json($latest);
    });
});
