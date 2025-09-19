<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;

class QuantitySyncService
{
    /**
     * Sync warehouse quantities between product and stock systems
     * This ensures all quantities stay synchronized across the system
     */
    public static function syncWarehouseQuantities($productId, $warehouseId, $newQuantity)
    {
        $product = Product::find($productId);
        if (!$product) {
            return false;
        }

        $warehouse = Warehouse::find($warehouseId);
        if (!$warehouse) {
            return false;
        }

        // Update product_warehouse pivot table
        $product->warehouses()->updateExistingPivot($warehouseId, [
            'quantity' => $newQuantity
        ]);

        // Update product total stock quantity
        $product->updateStockQuantity();

        // Find and update corresponding stock record
        $stock = self::findMatchingStock($product);
        if ($stock) {
            // Update warehouse_stock pivot table
            $stock->warehouses()->updateExistingPivot($warehouseId, [
                'quantity' => $newQuantity
            ]);

            // Recalculate stock remaining quantity
            $stock->recalculateRemainingQuantity()->save();
        }

        return true;
    }

    /**
     * Sync all warehouse quantities for a product
     */
    public static function syncAllProductWarehouses($productId)
    {
        $product = Product::with('warehouses')->find($productId);
        if (!$product) {
            return false;
        }

        // Update product total stock quantity
        $product->updateStockQuantity();

        // Find and update corresponding stock record
        $stock = self::findMatchingStock($product);
        if ($stock) {
            // Sync all warehouse quantities from product to stock
            $warehouseData = [];
            foreach ($product->warehouses as $warehouse) {
                $warehouseData[$warehouse->id] = ['quantity' => $warehouse->pivot->quantity];
            }

            $stock->warehouses()->sync($warehouseData);
            $stock->recalculateRemainingQuantity()->save();
        }

        return true;
    }

    /**
     * Sync all warehouse quantities for a stock
     */
    public static function syncAllStockWarehouses($stockId)
    {
        $stock = Stock::with('warehouses')->find($stockId);
        if (!$stock) {
            return false;
        }

        // Recalculate stock remaining quantity
        $stock->recalculateRemainingQuantity()->save();

        // Find and update corresponding product
        $product = self::findMatchingProduct($stock);
        if ($product) {
            // Sync all warehouse quantities from stock to product
            $warehouseData = [];
            foreach ($stock->warehouses as $warehouse) {
                $warehouseData[$warehouse->id] = ['quantity' => $warehouse->pivot->quantity];
            }

            $product->warehouses()->sync($warehouseData);
            $product->updateStockQuantity();
        }

        return true;
    }

    /**
     * Find matching stock record for a product
     */
    private static function findMatchingStock($product)
    {
        // Try to find by SKU first
        $stock = Stock::where('reference', $product->sku)->first();
        
        if (!$stock) {
            // Try to find by product name
            $stock = Stock::where('title', 'like', '%' . $product->name . '%')->first();
        }
        
        if (!$stock) {
            // Try case-insensitive reference matching
            $stock = Stock::whereRaw('LOWER(reference) = LOWER(?)', [$product->sku])->first();
        }

        return $stock;
    }

    /**
     * Find matching product record for a stock
     */
    private static function findMatchingProduct($stock)
    {
        // Try to find by SKU first
        $product = Product::where('sku', $stock->reference)->first();
        
        if (!$product) {
            // Try to find by product name
            $product = Product::where('name', 'like', '%' . $stock->title . '%')->first();
        }

        return $product;
    }

    /**
     * Sync a single warehouse quantity from stock to product
     */
    public static function syncStockToProduct($stockId, $warehouseId, $newQuantity)
    {
        $stock = Stock::find($stockId);
        if (!$stock) {
            return false;
        }

        // Update warehouse_stock pivot table
        $stock->warehouses()->updateExistingPivot($warehouseId, [
            'quantity' => $newQuantity
        ]);

        // Recalculate stock remaining quantity
        $stock->recalculateRemainingQuantity()->save();

        // Find and update corresponding product
        $product = self::findMatchingProduct($stock);
        if ($product) {
            // Update product_warehouse pivot table
            $product->warehouses()->updateExistingPivot($warehouseId, [
                'quantity' => $newQuantity
            ]);

            // Update product total stock quantity
            $product->updateStockQuantity();
        }

        return true;
    }
}
