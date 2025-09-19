<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class UpdateProductStockQuantities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-stock-quantities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update stock_quantity field for all products based on warehouse quantities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating product stock quantities...');
        
        $products = Product::with('warehouses')->get();
        $updatedCount = 0;
        
        foreach ($products as $product) {
            $oldQuantity = $product->stock_quantity;
            $newQuantity = $product->updateStockQuantity();
            
            if ($oldQuantity != $newQuantity) {
                $this->line("Updated {$product->name}: {$oldQuantity} → {$newQuantity}");
                $updatedCount++;
            }
        }
        
        $this->info("Updated {$updatedCount} products out of {$products->count()} total products.");
        
        return 0;
    }
}
