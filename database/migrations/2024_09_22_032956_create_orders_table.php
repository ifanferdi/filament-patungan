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
            $table->integer('promo')->default(100);
            $table->integer('order_fee')->default(0);
            $table->integer('delivery_fee')->default(0);
            $table->integer('tip')->default(0);
            $table->integer('total_fee')->default(0);
            $table->integer('discount')->default(0);
            $table->integer('discount_percent')->default(0);
            $table->integer('additional_discount')->default(0);
            $table->integer('additional_discount_percent')->default(0);
            $table->integer('total')->default(0);
            $table->integer('total_with_promo')->default(0);
            $table->integer('total_items')->default(0);
            $table->foreignId('author_id')->references('id')->on('users')
                ->cascadeOnDelete();
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
