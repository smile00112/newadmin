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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('bonus_amount_used', 12, 4)->default(0)->nullable()->after('base_shipping_discount_amount');
            $table->decimal('base_bonus_amount_used', 12, 4)->default(0)->nullable()->after('bonus_amount_used');
            $table->decimal('bonus_amount_accrued', 12, 4)->default(0)->nullable()->after('base_bonus_amount_used');
            $table->decimal('base_bonus_amount_accrued', 12, 4)->default(0)->nullable()->after('bonus_amount_accrued');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'bonus_amount_used',
                'base_bonus_amount_used',
                'bonus_amount_accrued',
                'base_bonus_amount_accrued',
            ]);
        });
    }
};
