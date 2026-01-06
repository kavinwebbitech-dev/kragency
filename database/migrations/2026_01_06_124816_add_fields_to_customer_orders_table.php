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
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->decimal('bonus_opening_balance', 10, 2)->after('closing_balance');
            $table->decimal('bonus_closing_balance', 10, 2)->after('bonus_opening_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropColumn(['bonus_opening_balance','bonus_closing_balance']);
        });
    }
};
