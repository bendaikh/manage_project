<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Add reference column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'reference')) {
                $table->string('reference')->nullable()->after('title');
            }
            
            // Add barcode column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'barcode')) {
                $table->string('barcode')->nullable()->after('reference');
            }
            
            // Add initial_quantity column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'initial_quantity')) {
                $table->integer('initial_quantity')->default(0)->after('quantity');
            }
            
            // Add delivered_quantity column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'delivered_quantity')) {
                $table->integer('delivered_quantity')->default(0)->after('initial_quantity');
            }
            
            // Add damaged_quantity column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'damaged_quantity')) {
                $table->integer('damaged_quantity')->default(0)->after('delivered_quantity');
            }
            
            // Add in_progress_quantity column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'in_progress_quantity')) {
                $table->integer('in_progress_quantity')->default(0)->after('damaged_quantity');
            }
            
            // Add remaining_quantity column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'remaining_quantity')) {
                $table->integer('remaining_quantity')->default(0)->after('in_progress_quantity');
            }
            
            // Add purchase_price column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'purchase_price')) {
                $table->decimal('purchase_price', 10, 2)->nullable()->after('remaining_quantity');
            }
            
            // Add selling_price column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'selling_price')) {
                $table->decimal('selling_price', 10, 2)->nullable()->after('purchase_price');
            }
            
            // Add status column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'status')) {
                $table->enum('status', ['in_stock', 'out_of_stock', 'low_stock'])->default('in_stock')->after('selling_price');
            }
            
            // Add warehouse_location column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'warehouse_location')) {
                $table->string('warehouse_location')->nullable()->after('status');
            }
            
            // Add warehouse_id column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('warehouse_location');
            }
            
            // Add last_updated_by column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'last_updated_by')) {
                $table->string('last_updated_by')->nullable()->after('warehouse_id');
            }
            
            // Add last_updated_at column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'last_updated_at')) {
                $table->timestamp('last_updated_at')->nullable()->after('last_updated_by');
            }
            
            // Add product_link column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'product_link')) {
                $table->string('product_link')->nullable()->after('last_updated_at');
            }
            
            // Add photo column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'photo')) {
                $table->string('photo')->nullable()->after('product_link');
            }
            
            // Add notes column if it doesn't exist
            if (!Schema::hasColumn('stocks', 'notes')) {
                $table->text('notes')->nullable()->after('photo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $columns = [
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
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('stocks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
