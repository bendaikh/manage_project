<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;

class SyncStockToProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync-stock-to-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync stock quantities from stocks table to product_warehouse pivot table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing stock quantities to products...');
        
        $stocks = Stock::with(['product', 'warehouses'])->get();
        $syncedCount = 0;
        $createdCount = 0;
        
        foreach ($stocks as $stock) {
            // Try to find matching product by SKU first, then by name
            $product = Product::where('sku', $stock->reference)->first();
            
            if (!$product) {
                $product = Product::where('name', 'like', '%' . $stock->title . '%')->first();
            }
            
            if (!$product) {
                $this->warn("No product found for stock: {$stock->title} (Reference: {$stock->reference})");
                continue;
            }
            
            $this->line("Syncing stock '{$stock->title}' to product '{$product->name}'");
            
            // Sync warehouse quantities from stock to product
            $warehouseData = [];
            foreach ($stock->warehouses as $warehouse) {
                $warehouseData[$warehouse->id] = ['quantity' => $warehouse->pivot->quantity];
                $this->line("  - {$warehouse->name}: {$warehouse->pivot->quantity} units");
            }
            
            if (!empty($warehouseData)) {
                // Sync warehouse quantities to product
                $product->warehouses()->sync($warehouseData);
                
                // Update product stock quantity
                $totalQuantity = $product->updateStockQuantity();
                
                $this->line("  → Updated product total: {$totalQuantity} units");
                $syncedCount++;
            }
        }
        
        // Also sync any products that have warehouse assignments but no stock records
        $products = Product::with('warehouses')->get();
        foreach ($products as $product) {
            if ($product->warehouses->count() > 0) {
                $product->updateStockQuantity();
                $createdCount++;
            }
        }
        
        $this->info("Synced {$syncedCount} products from stocks table");
        $this->info("Updated {$createdCount} products with existing warehouse assignments");
        $this->info("Total products processed: " . ($syncedCount + $createdCount));
        
        return 0;
    }
}
