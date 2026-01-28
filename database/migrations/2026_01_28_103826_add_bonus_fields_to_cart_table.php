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
        Schema::table('cart', function (Blueprint $table) {
            $table->decimal('bonus_amount', 12, 4)->default(0)->after('base_discount_amount');
            $table->decimal('base_bonus_amount', 12, 4)->default(0)->after('bonus_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart', function (Blueprint $table) {
            $table->dropColumn(['bonus_amount', 'base_bonus_amount']);
        });
    }
};
