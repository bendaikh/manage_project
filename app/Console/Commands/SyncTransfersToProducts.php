<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stock;
use App\Models\Product;

class SyncTransfersToProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:transfers-to-products {--dry-run : Show what would be synced without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync warehouse transfers from stock records to product_warehouse table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }
        
        // Get all stocks that have warehouse relationships
        $stocks = Stock::whereHas('warehouses')->get();
        
        $syncedCount = 0;
        $skippedCount = 0;
        
        foreach ($stocks as $stock) {
            $this->line("Processing stock ID: {$stock->id} - {$stock->title}");
            
            // Find the corresponding product
            $product = Product::where('sku', $stock->reference)
                            ->where('seller_id', $stock->seller_id)
                            ->first();
            
            if (!$product) {
                $this->line("  No product found for SKU: {$stock->reference}, Seller ID: {$stock->seller_id}");
                $skippedCount++;
                continue;
            }
            
            $this->line("  Found product ID: {$product->id} - {$product->name}");
            
            // Get all warehouse relationships from the stock
            $warehouseQuantities = [];
            $stockWarehouses = $stock->warehouses()->get();
            
            foreach ($stockWarehouses as $warehouseStock) {
                $warehouseId = $warehouseStock->id;
                $quantity = $warehouseStock->pivot->quantity;
                
                // Check if warehouse exists and has quantity > 0
                $warehouse = \App\Models\Warehouse::find($warehouseId);
                if ($warehouse && $quantity > 0) {
                    $warehouseQuantities[$warehouseId] = $quantity;
                } elseif (!$warehouse) {
                    $this->warn("Skipping invalid warehouse ID: {$warehouseId} for stock '{$stock->title}'");
                }
            }
            
            if (!empty($warehouseQuantities)) {
                if ($dryRun) {
                    $this->line("  Would sync with warehouses:");
                    foreach ($warehouseQuantities as $warehouseId => $quantity) {
                        $warehouse = \App\Models\Warehouse::find($warehouseId);
                        $this->line("    - {$warehouse->name}: {$quantity}");
                    }
                } else {
                    $this->line("  Syncing with warehouses:");
                    foreach ($warehouseQuantities as $warehouseId => $quantity) {
                        $warehouse = \App\Models\Warehouse::find($warehouseId);
                        $this->line("    - {$warehouse->name}: {$quantity}");
                    }
                    
                    try {
                        // Clear existing relationships and add new ones
                        $product->warehouses()->detach();
                        foreach ($warehouseQuantities as $warehouseId => $quantity) {
                            $product->warehouses()->attach($warehouseId, ['quantity' => $quantity]);
                        }
                        $product->updateStockQuantity();
                        $this->line("  SUCCESS: Synced product '{$product->name}' (SKU: {$product->sku})");
                    } catch (Exception $e) {
                        $this->error("  ERROR syncing product '{$product->name}': " . $e->getMessage());
                        $this->line("  Warehouse quantities: " . json_encode($warehouseQuantities));
                        return 1;
                    }
                }
                $syncedCount++;
            } else {
                $this->line("  No valid warehouse quantities found");
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
