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
        Schema::table('newsletters_contact_filters', function (Blueprint $table) {
            $table->dropColumn([
                'field',
                'operator',
                'value_from',
                'value_to',
                'value',
                'values',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_contact_filters', function (Blueprint $table) {
            $table->string('field')->after('name')->comment('Поле для фильтрации: gender, last_order_date, registration_date, birth_date, orders_count, average_check, total_check, average_order_rating, favorite_category, favorite_dish, store');
            $table->string('operator')->after('field')->comment('Оператор: between, gte, lte, equals, in');
            $table->decimal('value_from', 15, 2)->nullable()->after('operator')->comment('Начальное значение для диапазона (число или дата как timestamp)');
            $table->decimal('value_to', 15, 2)->nullable()->after('value_from')->comment('Конечное значение для диапазона (число или дата как timestamp)');
            $table->string('value')->nullable()->after('value_to')->comment('Одиночное значение (строка, число или дата)');
            $table->json('values')->nullable()->after('value')->comment('Массив значений для оператора in');
        });
    }
};

