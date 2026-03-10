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
        if (Schema::hasTable('bonus_levels')) {
            return;
        }

        Schema::create('bonus_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->decimal('cashback_percent', 5, 2)->default(0);
            $table->integer('min_orders')->nullable();
            $table->decimal('min_amount', 12, 4)->nullable();
            $table->decimal('min_cart_value', 12, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_levels');
    }
};
