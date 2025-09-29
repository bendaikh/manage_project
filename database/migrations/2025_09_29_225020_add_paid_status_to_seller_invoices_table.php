<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('seller_invoices', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('pdf_path');
            $table->timestamp('paid_at')->nullable()->after('is_paid');
            $table->unsignedBigInteger('paid_by')->nullable()->after('paid_at');
            
            // Foreign key for paid_by
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('seller_invoices', function (Blueprint $table) {
            $table->dropForeign(['paid_by']);
            $table->dropColumn(['is_paid', 'paid_at', 'paid_by']);
        });
    }
};