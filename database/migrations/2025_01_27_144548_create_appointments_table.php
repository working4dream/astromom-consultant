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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('astrologer_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('booking_id')->unique();
            $table->date('date');
            $table->enum('connect_type',['chat','voice','video']);
            $table->string('duration');
            $table->bigInteger('duration_second');
            $table->string('time_period');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->decimal('gst', 10, 2)->default(0.00);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->tinyInteger('booking_status')->default(15); // 15=Booked
            $table->string('payment_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
