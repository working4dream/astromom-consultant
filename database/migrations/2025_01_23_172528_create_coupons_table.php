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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code');
            $table->enum('discount_type',['percentage','fixed']);
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->decimal('max_discount', 10, 2)->default(0);
            $table->string('start_date')->nullable();
            $table->string('expiry_date')->nullable();
            $table->boolean('active')->default(false);
            $table->unsignedBigInteger('creator_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
