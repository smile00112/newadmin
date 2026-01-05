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
        Schema::create('newsletters_contact_filter_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('filter_id');
            $table->string('field')->comment('Поле для фильтрации: gender, last_order_date, registration_date, birth_date, orders_count, average_check, total_check, average_order_rating, favorite_category, favorite_dish, store');
            $table->string('operator')->comment('Оператор: between, gte, lte, equals, in');
            $table->decimal('value_from', 15, 2)->nullable()->comment('Начальное значение для диапазона (число или дата как timestamp)');
            $table->decimal('value_to', 15, 2)->nullable()->comment('Конечное значение для диапазона (число или дата как timestamp)');
            $table->string('value')->nullable()->comment('Одиночное значение (строка, число или дата)');
            $table->json('values')->nullable()->comment('Массив значений для оператора in');
            $table->integer('sort_order')->default(0)->comment('Порядок сортировки условий');
            $table->timestamps();

            $table->foreign('filter_id')->references('id')->on('newsletters_contact_filters')->onDelete('cascade');
            $table->index('filter_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_contact_filter_conditions');
    }
};

