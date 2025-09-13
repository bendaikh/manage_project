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
        Schema::create('upsells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stocks')->onDelete('cascade');
            $table->string('name')->nullable(); // Upsell product name (optional)
            $table->text('description')->nullable(); // Upsell description (optional)
            $table->integer('quantity'); // Available quantity for upsell
            $table->decimal('price', 10, 2); // Upsell price
            $table->boolean('is_active')->default(true); // Whether upsell is active
            $table->integer('sort_order')->default(0); // For ordering multiple upsells
            $table->timestamps();
            
            // Index for better performance
            $table->index(['stock_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upsells');
    }
};
