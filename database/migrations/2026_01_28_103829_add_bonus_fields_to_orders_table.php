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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('bonus_amount', 12, 4)->default(0)->after('base_grand_total_refunded');
            $table->decimal('base_bonus_amount', 12, 4)->default(0)->after('bonus_amount');
            $table->decimal('bonus_accrued', 12, 4)->default(0)->after('base_bonus_amount');
            $table->decimal('base_bonus_accrued', 12, 4)->default(0)->after('bonus_accrued');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['bonus_amount', 'base_bonus_amount', 'bonus_accrued', 'base_bonus_accrued']);
        });
    }
};
