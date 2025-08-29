<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Product identification
            $table->string('reference')->nullable()->after('title');
            $table->string('barcode')->nullable()->after('reference');
            
            // Inventory quantities
            $table->integer('initial_quantity')->default(0)->after('quantity');
            $table->integer('delivered_quantity')->default(0)->after('initial_quantity');
            $table->integer('damaged_quantity')->default(0)->after('delivered_quantity');
            $table->integer('in_progress_quantity')->default(0)->after('damaged_quantity');
            $table->integer('remaining_quantity')->default(0)->after('in_progress_quantity');
            
            // Pricing
            $table->decimal('purchase_price', 10, 2)->nullable()->after('remaining_quantity');
            $table->decimal('selling_price', 10, 2)->nullable()->after('purchase_price');
            
            // Status and tracking
            $table->enum('status', ['in_stock', 'out_of_stock', 'low_stock'])->default('in_stock')->after('selling_price');
            $table->string('warehouse_location')->nullable()->after('status');
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('warehouse_location');
            $table->string('last_updated_by')->nullable()->after('warehouse_id');
            $table->timestamp('last_updated_at')->nullable()->after('last_updated_by');
            
            // Additional product details
            $table->string('product_link')->nullable()->after('last_updated_at');
            $table->string('photo')->nullable()->after('product_link');
            $table->text('notes')->nullable()->after('photo');
        });
    }

    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Drop foreign key if it exists
            if (Schema::hasColumn('stocks', 'warehouse_id')) {
                try {
                    $table->dropForeign(['warehouse_id']);
                } catch (Exception $e) {
                    // Foreign key might not exist, continue
                }
            }
            
            $table->dropColumn([
                'reference',
                'barcode',
                'initial_quantity',
                'delivered_quantity',
                'damaged_quantity',
                'in_progress_quantity',
                'remaining_quantity',
                'purchase_price',
                'selling_price',
                'status',
                'warehouse_location',
                'warehouse_id',
                'last_updated_by',
                'last_updated_at',
                'product_link',
                'photo',
                'notes'
            ]);
        });
    }
};
