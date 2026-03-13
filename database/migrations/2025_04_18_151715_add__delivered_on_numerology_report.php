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
            $table->date('delivered_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('numerology_reports', function (Blueprint $table) {
            $table->dropColumn('delivered_on');
        });
    }
};
