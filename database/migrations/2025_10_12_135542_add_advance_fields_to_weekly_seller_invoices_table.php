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
        Schema::table('weekly_seller_invoices', function (Blueprint $table) {
            $table->decimal('advance_amount', 12, 2)->default(0)->after('total_amount');
            $table->text('advance_note')->nullable()->after('advance_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_seller_invoices', function (Blueprint $table) {
            $table->dropColumn(['advance_amount', 'advance_note']);
        });
    }
};
