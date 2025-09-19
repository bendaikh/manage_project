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
        if ($product->warehouses()->where('warehouse_id', $warehouseId)->exists()) {
            $product->warehouses()->updateExistingPivot($warehouseId, [
                'quantity' => $newQuantity
            ]);
        } else {
            $product->warehouses()->attach($warehouseId, ['quantity' => $newQuantity]);
        }

        // Update product total stock quantity
        $product->updateStockQuantity();

        // Find and update corresponding stock record
        $stock = self::findMatchingStock($product);
        if ($stock) {
            // Update warehouse_stock pivot table
            if ($stock->warehouses()->where('warehouse_id', $warehouseId)->exists()) {
                $stock->warehouses()->updateExistingPivot($warehouseId, [
                    'quantity' => $newQuantity
                ]);
            } else {
                $stock->warehouses()->attach($warehouseId, ['quantity' => $newQuantity]);
            }

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
            $stock->warehouses()->detach();
            foreach ($product->warehouses as $warehouse) {
                $stock->warehouses()->attach($warehouse->id, ['quantity' => $warehouse->pivot->quantity]);
            }
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
            if ($product->warehouses()->where('warehouse_id', $warehouseId)->exists()) {
                $product->warehouses()->updateExistingPivot($warehouseId, [
                    'quantity' => $newQuantity
                ]);
            } else {
                $product->warehouses()->attach($warehouseId, ['quantity' => $newQuantity]);
            }

            // Update product total stock quantity
            $product->updateStockQuantity();
        }

        return true;
    }

    /**
     * Sync warehouse relationships from stock records to product_warehouse table
     * This is specifically for products created from shipments
     */
    public static function syncProductWarehouseFromStock($product, $reference = null, $sellerId = null)
    {
        if (!$product) {
            return false;
        }

        $reference = $reference ?: $product->sku;
        $sellerId = $sellerId ?: $product->seller_id;

        // Get all stock records for this product reference
        $stockRecords = Stock::where('reference', $reference)
                            ->where('seller_id', $sellerId)
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

        // Sync to product_warehouse table
        if (!empty($warehouseQuantities)) {
            $product->warehouses()->sync($warehouseQuantities);
            $product->updateStockQuantity();
            return true;
        }

        return false;
    }
}
