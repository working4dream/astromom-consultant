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
        Schema::create('software_numerology_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('astrologer_id');
            $table->string('name');
            $table->date('dob');
            $table->string('language');
            $table->string('phone');
            $table->text('solution')->nullable();
            $table->string('file_engine');
            $table->integer('environment');
            $table->string('file_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software_numerology_reports');
    }
};
