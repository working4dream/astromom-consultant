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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->text('message')->nullable();
            $table->enum('message_types', ['text', 'image', 'video', 'pdf']);
            $table->string('image_path')->nullable();
            $table->string('video_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->unsignedBigInteger('status')->default(21);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
