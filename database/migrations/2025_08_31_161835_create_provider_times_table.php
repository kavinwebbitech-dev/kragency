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
        Schema::create('provider_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('betting_providers_id');
            $table->foreign('betting_providers_id')->references('id')->on('betting_providers')->onDelete('cascade');
            $table->time('time');
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_times');
    }
};
