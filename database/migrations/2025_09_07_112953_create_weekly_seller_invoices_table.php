<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('weekly_seller_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('seller'); // Seller name
            $table->date('week_start_date'); // Start of the week (Monday)
            $table->date('week_end_date'); // End of the week (Sunday)
            $table->integer('order_count');
            $table->decimal('total_amount', 12, 2);
            $table->string('pdf_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Unique index to avoid duplicates for same seller/week
            $table->unique(['seller', 'week_start_date']);
            
            // Foreign key for approved_by
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('weekly_seller_invoices');
    }
};