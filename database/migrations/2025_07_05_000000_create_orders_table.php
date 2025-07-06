<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('seller');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->string('client_name');
            $table->decimal('price', 10, 2);
            $table->string('client_address');
            $table->string('zone')->nullable();
            $table->string('client_phone')->nullable();
            $table->text('comment')->nullable();
            $table->foreignId('order_status_id')->constrained('order_statuses');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}; 