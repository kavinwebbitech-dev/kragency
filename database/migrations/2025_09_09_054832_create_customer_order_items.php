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
        Schema::create('customer_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('customer_orders');
            $table->foreignId('game_id')->constrained('schedule_providers_slot_time');
            $table->string('digits');
            $table->integer('quantity');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_box')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_order_items');
    }
};
