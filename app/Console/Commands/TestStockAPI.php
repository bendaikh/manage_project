<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestStockAPI extends Command
{
    protected $signature = 'stocks:test-api {user-id}';
    protected $description = 'Test stock API endpoints for a specific user';

    public function handle()
    {
        $userId = $this->argument('user-id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return;
        }
        
        $this->info("Testing API for user: {$user->name} (ID: {$userId})");
        $roles = $user->roles->pluck('name')->implode(', ');
        $this->info("Roles: {$roles}");
        
        // Simulate logging in as this user
        Auth::login($user);
        
        // Test statistics endpoint logic
        $this->info("\n=== Testing Statistics Endpoint ===");
        
        $baseQuery = Stock::query();
        
        if ($user->hasRole('seller')) {
            $baseQuery->where('seller_id', $user->id);
            $this->info("Filtering by seller_id: {$user->id}");
        } else {
            $this->info("No seller filtering applied (admin/manager/agent)");
        }
        
        $stats = [
            'total_products' => (clone $baseQuery)->count(),
            'in_stock' => (clone $baseQuery)->where('status', 'in_stock')->count(),
            'low_stock' => (clone $baseQuery)->where('status', 'low_stock')->count(),
            'out_of_stock' => (clone $baseQuery)->where('status', 'out_of_stock')->count(),
            'total_initial_quantity' => (clone $baseQuery)->sum('initial_quantity'),
            'total_remaining_quantity' => (clone $baseQuery)->sum('remaining_quantity'),
            'total_delivered_quantity' => (clone $baseQuery)->sum('delivered_quantity'),
            'total_damaged_quantity' => (clone $baseQuery)->sum('damaged_quantity'),
        ];
        
        $this->info("Statistics response:");
        foreach ($stats as $key => $value) {
            $this->line("  {$key}: {$value}");
        }
        
        // Test index endpoint logic
        $this->info("\n=== Testing Index Endpoint ===");
        
        $query = Stock::query()->with(['seller', 'shipment', 'product']);
        
        if ($user->hasRole('seller')) {
            $query->where('seller_id', $user->id);
        }
        
        $stocks = $query->orderByDesc('created_at')->get();
        
        $this->info("Stocks response count: {$stocks->count()}");
        foreach ($stocks as $stock) {
            $this->line("  - ID: {$stock->id}, Title: {$stock->title}, Status: {$stock->status}, Seller: {$stock->seller_id}");
        }
        
        // Check if user has any roles
        if ($user->roles->isEmpty()) {
            $this->warn("WARNING: User has no roles assigned!");
            $this->info("This might cause issues with role-based filtering.");
        }
    }
}
