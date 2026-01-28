<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->decimal('cashback_percent', 5, 2)->default(0);
            $table->string('calculation_type')->default('total_spent'); // orders_count, total_spent, cart_value
            $table->decimal('threshold_value', 12, 4)->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonus_levels');
    }
};
