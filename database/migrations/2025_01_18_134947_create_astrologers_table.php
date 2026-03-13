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
        Schema::create('astrologers', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('password')->nullable();
            $table->integer('otp')->nullable();
            $table->string('about_me')->nullable();
            $table->enum('gender', ['Male', 'Female','Other'])->nullable();
            $table->string('professional_title')->nullable();
            $table->string('image')->nullable();
            $table->json('social_links')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->text('address')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('zego_user_id')->nullable();
            $table->string('device_token')->nullable();
            $table->string('consultancy_area')->nullable();
            $table->unsignedBigInteger('is_offline')->default(0);
            $table->string('offline_message')->nullable();
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
            $table->boolean('status')->default(false);;
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('astrologers');
    }
};
