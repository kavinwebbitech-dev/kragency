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
        Schema::table('customer_order_items', function (Blueprint $table) {
            $table->decimal('win_amount', 10, 2)->nullable()->after('amount');
            $table->string('win_status')->nullable()->after('win_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_order_items', function (Blueprint $table) {
            $table->dropColumn(['win_amount', 'win_status']);
        });
    }
};
