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
        Schema::create('astrologer_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('astrologer_id');
            $table->integer('future_days')->default(0);
            $table->integer('duration_minutes')->default(30);
            $table->json('schedule');
            $table->decimal('morning_price', 8, 2)->nullable();
            $table->decimal('afternoon_price', 8, 2)->nullable();
            $table->decimal('evening_price', 8, 2)->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->boolean('is_availability')->default(0);
            $table->timestamps();

            $table->foreign('astrologer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('astrologer_schedules');
    }
};
