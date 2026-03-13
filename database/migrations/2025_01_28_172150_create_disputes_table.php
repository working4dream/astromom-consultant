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
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('booking_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('ticket_id')->unique();
            $table->string('reason');
            $table->string('other_reason')->nullable();
            $table->date('appointment_date');
            $table->longText('description');
            $table->string('file')->nullable();
            $table->tinyInteger('status')->comment('0=closed,1=open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
