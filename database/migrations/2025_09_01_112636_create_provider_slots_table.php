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
        Schema::create('provider_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('betting_provider_id')
                  ->constrained('betting_providers')
                  ->cascadeOnDelete();
            $table->foreignId('slot_id')
                  ->constrained('digit_master')
                  ->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->unsignedInteger('winning_amount');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_slots');
    }
};
