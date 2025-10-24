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
        Schema::create('product_ingredients_incompatibilities_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->boolean('active')->default(true); //Группа скрыта
            $table->timestamps();
        });

        Schema::create('product_ingredients_incompatibilities', function (Blueprint $table) {
            $table->integer('template_id')->unsigned();
            $table->foreign('template_id', 'pii_piit_id_foreign')->references('id')->on('product_ingredients_incompatibilities_templates')->onDelete('cascade');

            $table->integer('parent_id')->unsigned();
            $table->foreign('parent_id')->references('id', 'pii_parent_id_foreign')->on('products')->onDelete('cascade');

            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id', 'pii_prod_id_foreign')->on('products')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_ingredients_incompatibilities');
        Schema::dropIfExists('product_ingredients_incompatibilities_templates');
    }
};
