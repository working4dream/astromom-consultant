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
            $table->string('profile_picture')->nullable();
            $table->string('consultancy_area')->nullable();
            $table->decimal('hourly_rate')->default(0);
            $table->decimal('hourly_old_rate')->default(0);
            $table->text('description')->nullable();
            $table->string('expertise')->nullable();
            $table->text('philosophy')->nullable();
            $table->string('language')->nullable();
            $table->string('response_time')->nullable();
            $table->string('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamp('experience')->nullable();
            $table->boolean('status')->default(false);
            $table->softDeletes();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
        });
        Schema::dropIfExists('astrologers');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_picture');
            $table->dropColumn('consultancy_area');
            $table->dropColumn('hourly_rate');
            $table->dropColumn('hourly_old_rate');
            $table->dropColumn('description');
            $table->dropColumn('expertise');
            $table->dropColumn('philosophy');
            $table->dropColumn('language');
            $table->dropColumn('response_time');
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
            $table->dropColumn('experience');
            $table->dropColumn('status');
            $table->dropColumn('deleted_at');
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
        });
    }
};
