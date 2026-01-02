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
        Schema::create('schedule_providers_slot_time', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_provider_id')->constrained('schedule_provider')->cascadeOnDelete();
            $table->unsignedBigInteger('betting_providers_id');
            $table->foreign('betting_providers_id')->references('id')->on('betting_providers')->onDelete('cascade');
            $table->time('slot_time');
            $table->foreignId('slot_time_id')->constrained('provider_times')->cascadeOnDelete();
            $table->foreignId('digit_master_id')->constrained('digit_master')->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained('provider_slots')->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_providers_slot_time');
    }
};
