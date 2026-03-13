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
        Schema::table('numerology_reports', function (Blueprint $table) {
            $table->boolean('is_viewed')->default(false);
            $table->boolean('is_downloaded')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('numerology_reports', function (Blueprint $table) {
            $table->dropColumn('is_viewed');
            $table->dropColumn('is_downloaded');
        });
    }
};
