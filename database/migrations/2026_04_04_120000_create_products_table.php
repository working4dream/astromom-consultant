<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->string('duration');
            $table->integer('duration_in_min');
            $table->longText('description');
            $table->decimal('price', 8, 2);
            $table->boolean('is_gst')->default(false);
            $table->string('gst_type')->nullable();
            $table->decimal('gst_amount', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
