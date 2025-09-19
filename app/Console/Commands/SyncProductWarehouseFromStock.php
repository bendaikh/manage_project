<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Stock;
use App\Services\QuantitySyncService;

class SyncProductWarehouseFromStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:product-warehouse-from-stock {--dry-run : Show what would be synced without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync warehouse relationships from stock records to product_warehouse table for products created from shipments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }
        
        // Get all products that have stock records but may not have warehouse relationships
        $products = Product::whereHas('stocks')->get();
        
        $syncedCount = 0;
        $skippedCount = 0;
        
        foreach ($products as $product) {
            // Check if product already has warehouse relationships
            if ($product->warehouses()->count() > 0) {
                $skippedCount++;
                continue;
            }
            
            // Get all stock records for this product
            $stockRecords = Stock::where('reference', $product->sku)
                                ->where('seller_id', $product->seller_id)
                                ->get();
            
            $warehouseQuantities = [];
            
            foreach ($stockRecords as $stock) {
                // Get warehouse relationships from warehouse_stock table
                $warehouseStocks = $stock->warehouses()->get();
                
                foreach ($warehouseStocks as $warehouseStock) {
                    $warehouseId = $warehouseStock->id;
                    $quantity = $warehouseStock->pivot->quantity;
                    
                    // Accumulate quantities for the same warehouse
                    if (isset($warehouseQuantities[$warehouseId])) {
                        $warehouseQuantities[$warehouseId] += $quantity;
                    } else {
                        $warehouseQuantities[$warehouseId] = $quantity;
                    }
                }
            }
            
            if (!empty($warehouseQuantities)) {
                if ($dryRun) {
                    $this->line("Would sync product '{$product->name}' (SKU: {$product->sku}) with warehouses:");
                    foreach ($warehouseQuantities as $warehouseId => $quantity) {
                        $warehouse = \App\Models\Warehouse::find($warehouseId);
                        $this->line("  - {$warehouse->name}: {$quantity}");
                    }
                } else {
                    // Use the service method to sync
                    QuantitySyncService::syncProductWarehouseFromStock($product);
                    $this->line("Synced product '{$product->name}' (SKU: {$product->sku})");
                }
                $syncedCount++;
            } else {
                $skippedCount++;
            }
        }
        
        if ($dryRun) {
            $this->info("DRY RUN COMPLETE:");
            $this->info("Would sync: {$syncedCount} products");
            $this->info("Would skip: {$skippedCount} products");
        } else {
            $this->info("SYNC COMPLETE:");
            $this->info("Synced: {$syncedCount} products");
            $this->info("Skipped: {$skippedCount} products");
        }
        
        return 0;
    }
}
