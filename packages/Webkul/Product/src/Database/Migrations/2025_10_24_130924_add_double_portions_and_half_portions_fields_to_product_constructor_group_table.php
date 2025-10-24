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
        Schema::table('product_constructor_group', function (Blueprint $table) {
            $table->boolean('double_portions')->default(false); //Двойные порции
            $table->boolean('half_portions')->default(false); //Половинчатые порции

            $table->integer('ingredients_incompatibilities_id')->unsigned()->nullable();
            $table->foreign('ingredients_incompatibilities_id', 'pcg_ingr_incom_id_foreign')->references('id')->on('product_ingredients_incompatibilities_templates')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_constructor_group', function (Blueprint $table) {
            $table->dropColumn('double_portions');
            $table->dropColumn('half_portions');
            $table->dropColumn('ingredients_incompatibilities_id');
        });
    }
};
