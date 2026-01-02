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
        Schema::create('schedule_provider', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('betting_providers_id');
            $table->foreign('betting_providers_id')->references('id')->on('betting_providers')->onDelete('cascade');
            $table->foreignId('slot_id')->constrained('provider_slots')->cascadeOnDelete();
            $table->foreignId('slot_time_id')->constrained('provider_times')->cascadeOnDelete();
            $table->string('result')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_provider');
    }
};
