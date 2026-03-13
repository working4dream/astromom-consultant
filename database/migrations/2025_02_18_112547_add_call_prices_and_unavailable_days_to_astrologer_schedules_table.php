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
        Schema::table('astrologer_schedules', function (Blueprint $table) {
            $table->decimal('video_call_price_30min', 10, 2)->nullable()->after('price');
            $table->decimal('video_call_price_60min', 10, 2)->nullable()->after('video_call_price_30min');
            $table->decimal('audio_call_price_30min', 10, 2)->nullable()->after('video_call_price_60min');
            $table->decimal('audio_call_price_60min', 10, 2)->nullable()->after('audio_call_price_30min');
            $table->json('not_available_days')->nullable()->after('audio_call_price_60min');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('astrologer_schedules', function (Blueprint $table) {
            $table->dropColumn(['video_call_price_30min', 'video_call_price_60min', 'audio_call_price_30min', 'audio_call_price_60min', 'not_available_days']);
        });
    }
};
