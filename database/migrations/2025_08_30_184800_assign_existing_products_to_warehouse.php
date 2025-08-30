<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;
use App\Models\Warehouse;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the first warehouse or create a default one
        $warehouse = Warehouse::first();
        
        if (!$warehouse) {
            $warehouse = Warehouse::create([
                'name' => 'Default Warehouse',
                'location' => 'Main Location',
                'status' => 'active',
                'description' => 'Default warehouse for existing products'
            ]);
        }
        
        // Assign all products without warehouse to the first warehouse
        Product::whereNull('warehouse_id')->update(['warehouse_id' => $warehouse->id]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be safely reversed as it would leave products without warehouses
        // which would violate the new required constraint
    }
};
