<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_invoices', function (Blueprint $table) {
            $table->id();
            $table->date('invoice_date')->unique();
            $table->integer('order_count');
            $table->decimal('total_amount', 12, 2);
            $table->string('pdf_path');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_invoices');
    }
}; 