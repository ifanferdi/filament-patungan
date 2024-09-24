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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('discount_percentage')->default(100);
            $table->integer('order_fee')->default(0);
            $table->integer('delivery_fee')->default(0);
            $table->integer('tip')->default(0);
            $table->integer('total_fee')->default(0);
            $table->integer('discount')->default(0);
            $table->integer('additional_discount')->default(0);
            $table->integer('bill_real')->default(0);
            $table->integer('bill_by_discount_percentage')->default(0);
            $table->integer('bill_final')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
