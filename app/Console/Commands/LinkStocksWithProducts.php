<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Models\Product;
use Illuminate\Console\Command;

class LinkStocksWithProducts extends Command
{
    protected $signature = 'stocks:link-products {--force : Force update even if stock already has product_id}';
    protected $description = 'Link stocks with products based on title matching and update stock data';

    public function handle()
    {
        $this->info('Starting stock-product linking process...');
        
        $stocks = Stock::whereNull('product_id')->orWhere('product_id', '')->get();
        
        if ($stocks->isEmpty()) {
            $this->info('No stocks found that need linking.');
            return;
        }
        
        $this->info("Found {$stocks->count()} stocks to process.");
        
        $linked = 0;
        $updated = 0;
        
        foreach ($stocks as $stock) {
            // Try to find a product by name
            $product = Product::where('name', 'like', "%{$stock->title}%")
                ->orWhere('name', 'like', "%" . str_replace(' ', '%', $stock->title) . "%")
                ->first();
            
            if ($product) {
                $stock->product_id = $product->id;
                $stock->syncWithProduct();
                $stock->save();
                
                $this->line("✓ Linked stock '{$stock->title}' with product '{$product->name}'");
                $linked++;
                $updated++;
            } else {
                $this->line("✗ No product found for stock '{$stock->title}'");
            }
        }
        
        $this->info("\nLinking completed:");
        $this->info("- Linked: {$linked} stocks");
        $this->info("- Updated: {$updated} stocks");
        
        if ($linked > 0) {
            $this->info("\nYou can now see actual product names instead of placeholder titles in your stock list.");
            $this->info("Stock quantities will automatically update when orders are delivered or shipped.");
        }
    }
}
