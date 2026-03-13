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
        Schema::create('gemstone_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->integer('year');
            $table->integer('month');
            $table->integer('date');
            $table->integer('hour');
            $table->integer('minute');
            $table->integer('second');
            $table->decimal('lat',9,6);
            $table->decimal('lon',9,6);
            $table->string('language');
            $table->decimal('weight',8,2);
            $table->string('file_url');
            $table->json('revision_version')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gemstone_reports');
    }
};
