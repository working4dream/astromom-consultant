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
        Schema::create('astrologer_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('astrologer_id')->default(0);
            $table->unsignedBigInteger('user_id')->default(0);
            $table->decimal('ratings')->default(0);
            $table->text('review')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('astrologer_id')->references('id')->on('users');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('astrologer_ratings');
    }
};
