<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('name', 255);
            $table->integer('price')->default(0);
            $table->integer('discount_by_percentage')->default(0);
            $table->integer('discount')->default(0);
            $table->integer('additional_discount')->default(0);
            $table->integer('price_after_discount')->default(0);
            $table->integer('fee')->default(0);
            $table->integer('final_price')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
