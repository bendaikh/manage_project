<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class CheckStockStatuses extends Command
{
    protected $signature = 'stocks:check-statuses {--user-id= : Check for specific user ID}';
    protected $description = 'Check stock statuses and debug statistics';

    public function handle()
    {
        $this->info('Checking stock statuses...');
        
        $stocks = Stock::select('id', 'title', 'status', 'seller_id')->get();
        
        if ($stocks->isEmpty()) {
            $this->info('No stocks found in database.');
            return;
        }
        
        $this->info("Found {$stocks->count()} stocks:");
        
        foreach ($stocks as $stock) {
            $this->line("ID: {$stock->id}, Title: {$stock->title}, Status: {$stock->status}, Seller ID: {$stock->seller_id}");
        }
        
        // Check statistics
        $this->info("\nStatistics:");
        $this->info("Total: " . Stock::count());
        $this->info("In Stock: " . Stock::where('status', 'in_stock')->count());
        $this->info("Low Stock: " . Stock::where('status', 'low_stock')->count());
        $this->info("Out of Stock: " . Stock::where('status', 'out_of_stock')->count());
        
        // Check for null statuses
        $nullStatus = Stock::whereNull('status')->count();
        if ($nullStatus > 0) {
            $this->warn("Found {$nullStatus} stocks with NULL status!");
        }
        
        // Check for empty statuses
        $emptyStatus = Stock::where('status', '')->count();
        if ($emptyStatus > 0) {
            $this->warn("Found {$emptyStatus} stocks with empty status!");
        }
        
        // Check user filtering
        $userId = $this->option('user-id');
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $this->info("\nChecking for user ID: {$userId} ({$user->name})");
                $userStocks = Stock::where('seller_id', $userId)->get();
                $this->info("Stocks for this user: {$userStocks->count()}");
                
                foreach ($userStocks as $stock) {
                    $this->line("  - ID: {$stock->id}, Title: {$stock->title}, Status: {$stock->status}");
                }
            } else {
                $this->error("User with ID {$userId} not found!");
            }
        } else {
            // List all users with their roles
            $this->info("\nAll users:");
            $users = User::with('roles')->get();
            foreach ($users as $user) {
                $roles = $user->roles->pluck('name')->implode(', ');
                $this->line("ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Roles: {$roles}");
            }
        }
    }
}
