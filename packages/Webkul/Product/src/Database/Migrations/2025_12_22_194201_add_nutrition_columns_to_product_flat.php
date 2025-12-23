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
        Schema::table('product_flat', function (Blueprint $table) {
            if (!Schema::hasColumn('product_flat', 'calories')) {
                $table->decimal('calories', 8, 2)->nullable()->after('weight');
            }
            
            if (!Schema::hasColumn('product_flat', 'proteins')) {
                $table->decimal('proteins', 8, 2)->nullable()->after('calories');
            }
            
            if (!Schema::hasColumn('product_flat', 'fats')) {
                $table->decimal('fats', 8, 2)->nullable()->after('proteins');
            }
            
            if (!Schema::hasColumn('product_flat', 'carbs')) {
                $table->decimal('carbs', 8, 2)->nullable()->after('fats');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_flat', function (Blueprint $table) {
            if (Schema::hasColumn('product_flat', 'calories')) {
                $table->dropColumn('calories');
            }
            
            if (Schema::hasColumn('product_flat', 'proteins')) {
                $table->dropColumn('proteins');
            }
            
            if (Schema::hasColumn('product_flat', 'fats')) {
                $table->dropColumn('fats');
            }
            
            if (Schema::hasColumn('product_flat', 'carbs')) {
                $table->dropColumn('carbs');
            }
        });
    }
};

