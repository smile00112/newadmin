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
        Schema::table('bonus_levels', function (Blueprint $table) {
            // Add threshold_value column if it doesn't exist
            if (!Schema::hasColumn('bonus_levels', 'threshold_value')) {
                $table->decimal('threshold_value', 12, 4)->default(0)->after('cashback_percent');
            }
            
            // Add is_active column if it doesn't exist
            if (!Schema::hasColumn('bonus_levels', 'is_active')) {
                $table->boolean('is_active')->default(1)->after('sort_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bonus_levels', function (Blueprint $table) {
            if (Schema::hasColumn('bonus_levels', 'threshold_value')) {
                $table->dropColumn('threshold_value');
            }
            
            if (Schema::hasColumn('bonus_levels', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
