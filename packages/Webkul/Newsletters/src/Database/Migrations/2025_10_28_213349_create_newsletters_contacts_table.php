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
        Schema::create('newsletters_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->comment('ФИО');
            $table->string('phone')->comment('Телефон');
            $table->string('email')->nullable()->comment('Email');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->comment('Пол');
            $table->date('last_order_date')->nullable()->comment('Дата последнего заказа');
            $table->date('registration_date')->nullable()->comment('Дата регистрации');
            $table->date('birth_date')->nullable()->comment('Дата рождения');
            $table->unsignedInteger('orders_count')->default(0)->nullable()->comment('Количество заказов');
            $table->decimal('average_check', 10, 2)->nullable()->comment('Средний чек');
            $table->decimal('total_check', 12, 2)->default(0)->nullable()->comment('Общий чек');
            $table->decimal('average_order_rating', 3, 2)->nullable()->comment('Средняя оценка заказа');
            $table->string('favorite_category')->nullable()->comment('Любимая категория');
            $table->string('favorite_dish')->nullable()->comment('Любимое блюдо');
            $table->string('store')->nullable()->comment('Магазин');
            $table->unsignedBigInteger('contact_group_id')->nullable()->comment('Группа контакта');
            $table->timestamps();

            $table->foreign('contact_group_id')->references('id')->on('newsletters_contact_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_contacts');
    }
};
