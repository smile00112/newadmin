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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_half_portion')->default(false)->after('additional');
            $table->unsignedInteger('half_portion_pair_product_id')->nullable()->after('is_half_portion');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('half_portion_pair_product_id')
                ->references('id')
                ->on('products')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['half_portion_pair_product_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_half_portion', 'half_portion_pair_product_id']);
        });
    }
};
