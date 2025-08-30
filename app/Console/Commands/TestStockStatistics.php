<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestStockStatistics extends Command
{
    protected $signature = 'stocks:test-statistics {user-id}';
    protected $description = 'Test stock statistics for a specific user';

    public function handle()
    {
        $userId = $this->argument('user-id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return;
        }
        
        $this->info("Testing statistics for user: {$user->name} (ID: {$userId})");
        $roles = $user->roles->pluck('name')->implode(', ');
        $this->info("Roles: {$roles}");
        
        // Simulate logging in as this user
        Auth::login($user);
        
        // Test the statistics logic
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
        ];
        
        $this->info("\nStatistics:");
        foreach ($stats as $key => $value) {
            $this->line("  {$key}: {$value}");
        }
        
        // Show the actual stocks this user would see
        $stocks = $baseQuery->select('id', 'title', 'status', 'seller_id')->get();
        $this->info("\nStocks visible to this user:");
        foreach ($stocks as $stock) {
            $this->line("  - ID: {$stock->id}, Title: {$stock->title}, Status: {$stock->status}, Seller: {$stock->seller_id}");
        }
    }
}
