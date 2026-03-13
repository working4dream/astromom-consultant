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
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('call_id');
            $table->unsignedBigInteger('caller_id');
            $table->unsignedBigInteger('receiver_id');
            $table->enum('type', ['video', 'audio']);
            $table->unsignedBigInteger('status')->nullable();
            $table->date('date')->nullable();
            $table->time('started_at')->nullable();
            $table->time('ended_at')->nullable();
            $table->integer('duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
