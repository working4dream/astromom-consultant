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
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('numerology_report_purchased')->default(0)->comment('0: No, 1: Yes, 2: AstroMoM, 3: Searchy, 4: Manually');
            $table->tinyInteger('numerology_report_delivered')->default(0)->comment('0: No, 1: Yes, 2: Viewed, 3: Downloaded');
            $table->tinyInteger('free_chat_used')->default(0)->comment('0: No, 1: Yes');
            $table->tinyInteger('golden_code_used')->default(0)->comment('0: No, 1: Yes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('numerology_report_purchased');
            $table->dropColumn('numerology_report_delivered');
            $table->dropColumn('free_chat_used');
            $table->dropColumn('golden_code_used');
        });
    }
};
