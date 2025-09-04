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
        Schema::table('orders', function (Blueprint $table) {
            $table->date('confirmed_date')->nullable()->after('comment');
            $table->text('confirmation_comment')->nullable()->after('confirmed_date');
            $table->date('postponed_date')->nullable()->after('confirmation_comment');
            $table->text('postponed_comment')->nullable()->after('postponed_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['confirmed_date', 'confirmation_comment', 'postponed_date', 'postponed_comment']);
        });
    }
};
