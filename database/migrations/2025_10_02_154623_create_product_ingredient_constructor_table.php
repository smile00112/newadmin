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
        /*
         * Constructor to product with pivot fields
        */
        Schema::create('product_constructor', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('visible')->default(true);
            $table->boolean('required')->default(false);
            


            $table->integer('parent_id')->unsigned();
            $table->foreign('parent_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::create('product_constructor_group', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150)->nullable();
            $table->string('type', 25);
            $table->tinyInteger('min')->unsigned();
            $table->tinyInteger('max')->unsigned();



            $table->integer('parent_id')->unsigned();
            $table->integer('child_id')->unsigned();

            $table->foreign('parent_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('child_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::create('product_constructor_ingredients', function (Blueprint $table) {
            $table->integer('parent_id')->unsigned();
            $table->integer('child_id')->unsigned();

            $table->foreign('parent_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('child_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_ingredients_constructor');
    }
};
