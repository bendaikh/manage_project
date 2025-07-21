<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('reference');
            $table->integer('quantity');
            $table->text('description')->nullable();
            $table->string('link');
            $table->string('photo')->nullable();
            $table->date('shipment_date');
            $table->decimal('customs_fees', 10, 2)->nullable();
            $table->string('status')->default('Processing');
            $table->boolean('validated')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipments');
    }
}; 