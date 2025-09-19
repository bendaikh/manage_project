<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Stock;

class SyncAllProductWarehouses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync-all-warehouses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all product warehouse quantities with their corresponding stock quantities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing all product warehouse quantities...');
        
        $products = Product::with('warehouses')->get();
        $syncedCount = 0;
        
        foreach ($products as $product) {
            // Try to find matching stock record
            $stock = Stock::where('reference', $product->sku)->first();
            
            if (!$stock) {
                $stock = Stock::where('title', 'like', '%' . $product->name . '%')->first();
            }
            
            if ($stock) {
                // Sync warehouse quantities from stock to product
                $stockWarehouses = $stock->warehouses()->get();
                
                if ($stockWarehouses->count() > 0) {
                    $warehouseData = [];
                    foreach ($stockWarehouses as $warehouse) {
                        $warehouseData[$warehouse->id] = ['quantity' => $warehouse->pivot->quantity];
                    }
                    
                    // Sync the quantities to the product's warehouse pivot table
                    $product->warehouses()->sync($warehouseData);
                    
                    // Update the product's total stock quantity
                    $totalQuantity = $product->updateStockQuantity();
                    
                    $this->line("Synced '{$product->name}': {$totalQuantity} total units");
                    $syncedCount++;
                }
            } else {
                // If no stock record, just update the total from existing warehouse assignments
                if ($product->warehouses->count() > 0) {
                    $totalQuantity = $product->updateStockQuantity();
                    $this->line("Updated '{$product->name}': {$totalQuantity} total units");
                    $syncedCount++;
                }
            }
        }
        
        $this->info("Synced {$syncedCount} products out of {$products->count()} total products.");
        
        return 0;
    }
}
