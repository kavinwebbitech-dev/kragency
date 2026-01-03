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
        Schema::table('users_wallet_transactions', function (Blueprint $table) {
            $table->decimal('bonus_amount', 12, 2)->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['bonus_amount']);
        });
    }
};
