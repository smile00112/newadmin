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
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('bonus_balance', 12, 4)->default(0)->after('is_suspended');
            $table->integer('bonus_level_id')->unsigned()->nullable()->after('bonus_balance');
            $table->decimal('bonus_total_spent', 12, 4)->default(0)->after('bonus_level_id');
            $table->integer('bonus_total_orders')->default(0)->after('bonus_total_spent');
            
            $table->foreign('bonus_level_id')->references('id')->on('bonus_levels')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['bonus_level_id']);
            $table->dropColumn(['bonus_balance', 'bonus_level_id', 'bonus_total_spent', 'bonus_total_orders']);
        });
    }
};
