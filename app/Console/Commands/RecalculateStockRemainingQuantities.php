<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stock;

class RecalculateStockRemainingQuantities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recalculate:stock-remaining-quantities {--dry-run : Show what would be recalculated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate remaining quantities for all stocks based on warehouse distributions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }
        
        // Get all stocks
        $stocks = Stock::with('warehouses')->get();
        
        $updatedCount = 0;
        
        foreach ($stocks as $stock) {
            // Calculate total warehouse quantity
            $totalWarehouseQuantity = $stock->warehouses()->sum('warehouse_stock.quantity');
            $oldRemaining = $stock->remaining_quantity;
            
            if ($totalWarehouseQuantity > 0) {
                $newRemaining = $totalWarehouseQuantity;
                
                if ($oldRemaining != $newRemaining) {
                    if ($dryRun) {
                        $this->line("Stock '{$stock->title}' (ID: {$stock->id}):");
                        $this->line("  Old remaining: {$oldRemaining}");
                        $this->line("  New remaining: {$newRemaining}");
                        $this->line("  Warehouse distribution: {$totalWarehouseQuantity}");
                    } else {
                        $stock->remaining_quantity = $newRemaining;
                        $stock->save();
                        $this->line("Updated stock '{$stock->title}' (ID: {$stock->id}): {$oldRemaining} → {$newRemaining}");
                    }
                    $updatedCount++;
                }
            }
        }
        
        if ($dryRun) {
            $this->info("DRY RUN COMPLETE:");
            $this->info("Would update: {$updatedCount} stocks");
        } else {
            $this->info("RECALCULATION COMPLETE:");
            $this->info("Updated: {$updatedCount} stocks");
        }
        
        return 0;
    }
}