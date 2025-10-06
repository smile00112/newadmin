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
            $table->boolean('combo')->default(false);
            $table->boolean('discount')->default(false);
            $table->string('design')->default('category'); // line (В строку) / category (Категория - товар) / table (Таблица)
            $table->string('discount_type')->default(null)->nullable(); // null : Нет / percent : Процентная / fixed : Фиксированная
            $table->tinyInteger('discount_value')->default(0)->nullable(); // величина скидки
            $table->tinyInteger('min_selected_sum')->default(0)->nullable(); // Мин. сумма ингредиентов

            $table->integer('parent_id')->unsigned();
            $table->foreign('parent_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::create('product_constructor_group', function (Blueprint $table) {
            $table->increments('id');
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

            $table->integer('parent_id')->unsigned();
            $table->foreign('parent_id')->references('id')->on('product_constructor')->onDelete('cascade');
        });

        Schema::create('product_constructor_group_products', function (Blueprint $table) {
            $table->integer('group_id')->unsigned();
            $table->integer('product_id')->unsigned();

            $table->primary(['group_id', 'product_id']);
            $table->foreign('group_id')->references('id')->on('product_constructor_group')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_constructor_group_products');
        Schema::dropIfExists('product_constructor_group');
        Schema::dropIfExists('product_constructor');
    }
};
