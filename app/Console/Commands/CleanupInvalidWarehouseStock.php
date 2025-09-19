<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupInvalidWarehouseStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:invalid-warehouse-stock {--dry-run : Show what would be cleaned without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up invalid warehouse relationships from warehouse_stock table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }
        
        // Find invalid warehouse_stock relationships (warehouse_id doesn't exist)
        $invalidRelationships = DB::table('warehouse_stock')
            ->leftJoin('warehouses', 'warehouse_stock.warehouse_id', '=', 'warehouses.id')
            ->whereNull('warehouses.id')
            ->select('warehouse_stock.*')
            ->get();
        
        $this->info("Found {$invalidRelationships->count()} invalid warehouse_stock relationships:");
        
        foreach ($invalidRelationships as $relationship) {
            $this->line("Stock ID: {$relationship->stock_id}, Invalid Warehouse ID: {$relationship->warehouse_id}, Quantity: {$relationship->quantity}");
        }
        
        if ($invalidRelationships->count() > 0) {
            if ($dryRun) {
                $this->info("DRY RUN: Would remove {$invalidRelationships->count()} invalid relationships");
            } else {
                // Remove invalid relationships
                $deletedCount = DB::table('warehouse_stock')
                    ->leftJoin('warehouses', 'warehouse_stock.warehouse_id', '=', 'warehouses.id')
                    ->whereNull('warehouses.id')
                    ->delete();
                
                $this->info("Removed {$deletedCount} invalid warehouse_stock relationships");
            }
        } else {
            $this->info("No invalid warehouse_stock relationships found");
        }
        
        return 0;
    }
}
