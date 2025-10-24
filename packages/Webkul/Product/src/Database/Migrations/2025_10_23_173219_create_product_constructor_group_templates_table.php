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
        Schema::create('product_constructor_group_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('template_name', 150)->nullable();
            $table->string('name', 150)->nullable();
            $table->string('field_type', 55)->default('checkbox'); //checkbox : Чекбокс / radio : Радио / list : Список  (Тип)
            $table->string('checked_type')->default('once'); //once : Только один продукт / multiple : Несколько продуктов   (Разрешенное количество продуктов из группы для добавления)
            $table->tinyInteger('quantity_min')->unsigned()->default(0);
            $table->tinyInteger('quantity_max')->unsigned()->default(0);
            $table->boolean('show_title')->default(true); //Показывать заголовок группы
            $table->boolean('opened_by_default')->default(true); //Группа открыта по умолчанию
            $table->boolean('zero_price')->default(true); //Все продукты группы по нулевой цене
            $table->boolean('required')->default(false); //Обязательный выбор
            $table->boolean('hidden')->default(false); //Группа скрыта
            $table->boolean('double_portions')->default(false); //Двойные порции
            $table->boolean('half_portions')->default(false); //Половинчатые порции
            $table->tinyInteger('sort')->unsigned()->default(0);

            $table->integer('ingredients_incompatibilities_id')->unsigned()->nullable();
            $table->foreign('ingredients_incompatibilities_id', 'pcgt_ingr_incom_id_foreign')->references('id')->on('product_ingredients_incompatibilities_templates')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::create('product_constructor_group_templates_products', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('sort')->unsigned()->default(0);
            $table->boolean('default')->default(false); //продукт выбран по умочанию при добавлении в корзину

            $table->integer('group_id')->unsigned();
            $table->integer('product_id')->unsigned();

            //$table->primary(['group_id', 'product_id']);
            $table->foreign('group_id', 'pcgtp_g_id_foreign')->references('id')->on('product_constructor_group_templates')->onDelete('cascade');
            $table->foreign('product_id', 'pcgtp_p_id_foreign')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_constructor_group_templates_products');
        Schema::dropIfExists('product_constructor_group_templates');
    }
};
