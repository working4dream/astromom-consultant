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
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
            $table->string('audio_path')->nullable()->after('video_path');
            $table->boolean('is_read')->default(false)->after('audio_path');
            $table->unsignedBigInteger('reply_to_id')->nullable()->after('is_read');
            DB::statement("ALTER TABLE messages MODIFY message_types ENUM('text', 'image', 'video', 'audio')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('audio_path');
            $table->string('pdf_path')->nullable()->after('video_path');
            $table->dropColumn('is_read');
            $table->dropColumn('reply_to_id');
            DB::statement("ALTER TABLE messages MODIFY message_types ENUM('text', 'image', 'video', 'pdf')");
        });
    }
};
