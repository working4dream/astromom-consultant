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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('customer_banner')->nullable();
            $table->string('expert_banner')->nullable();
            $table->string('link')->nullable();
            $table->tinyInteger('type')->nullable()->comment('1=customer,2=expert');
            $table->boolean('is_active')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('date_range')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
