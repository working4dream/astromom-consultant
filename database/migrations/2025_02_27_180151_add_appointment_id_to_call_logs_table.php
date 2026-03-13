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
        Schema::table('call_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('appointment_id')->after('call_id')->nullable();
            $table->string('call_time')->after('appointment_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->dropColumn('appointment_id');
            $table->dropColumn('call_time');
        });
    }
};
