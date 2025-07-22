<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seller_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('seller'); // Seller name
            $table->date('invoice_date');
            $table->integer('order_count');
            $table->decimal('total_amount', 12, 2);
            $table->string('pdf_path');
            $table->timestamps();
            // Unique index to avoid duplicates for same seller/date
            $table->unique(['seller', 'invoice_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('seller_invoices');
    }
}; 