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
            $table->string('keywords')->nullable();
            $table->boolean('is_approved')->nullable();
            $table->unsignedBigInteger('approved_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('keywords');
            $table->dropColumn('is_approved');
            $table->dropColumn('approved_id');
        });
    }
};
