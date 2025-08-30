<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Models\Order;
use Illuminate\Console\Command;

class UpdateStockQuantitiesFromOrders extends Command
{
    protected $signature = 'stocks:update-from-orders {--force : Force update all stocks}';
    protected $description = 'Update stock quantities based on existing orders';

    public function handle()
    {
        $this->info('Starting stock quantity update from orders...');
        
        $stocks = Stock::whereNotNull('product_id')->get();
        
        if ($stocks->isEmpty()) {
            $this->info('No stocks with linked products found.');
            return;
        }
        
        $this->info("Found {$stocks->count()} stocks with linked products.");
        
        $updated = 0;
        
        foreach ($stocks as $stock) {
            // Reset quantities
            $stock->delivered_quantity = 0;
            $stock->in_progress_quantity = 0;
            
            // Get orders for this product and seller
            $orders = Order::where('product_id', $stock->product_id)
                ->where('belongs_to', $stock->seller_id)
                ->with('orderStatus')
                ->get();
            
            foreach ($orders as $order) {
                switch ($order->orderStatus->name) {
                    case 'Delivered':
                        $stock->delivered_quantity += $order->quantity;
                        break;
                    case 'Shipped':
                        $stock->in_progress_quantity += $order->quantity;
                        break;
                }
            }
            
            $stock->recalculateRemainingQuantity();
            $stock->last_updated_by = 'System (Order Sync)';
            $stock->last_updated_at = now();
            $stock->save();
            
            $this->line("✓ Updated stock '{$stock->title}' - Delivered: {$stock->delivered_quantity}, In Progress: {$stock->in_progress_quantity}, Remaining: {$stock->remaining_quantity}");
            $updated++;
        }
        
        $this->info("\nUpdate completed:");
        $this->info("- Updated: {$updated} stocks");
        $this->info("\nStock quantities are now synchronized with order statuses.");
    }
}
