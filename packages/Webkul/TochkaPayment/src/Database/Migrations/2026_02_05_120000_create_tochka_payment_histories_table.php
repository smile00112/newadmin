<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tochka_payment_histories', function (Blueprint $table) {
            $table->id();
            $table->string('external_order_id')->nullable()->comment('ID заказа от внешней системы');
            $table->string('order_id')->unique()->comment('Уникальный ID заказа для банка (формат: {id}|{timestamp})');
            $table->decimal('amount', 15, 2)->comment('Сумма платежа');
            $table->string('client_name')->comment('Имя клиента');
            $table->string('client_email')->comment('Email клиента');
            $table->string('client_phone')->comment('Телефон клиента');
            $table->text('payment_url')->nullable()->comment('Ссылка на оплату');
            $table->string('transaction_id')->nullable()->comment('ID транзакции от банка');
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending')->comment('Статус платежа');
            $table->json('request_data')->nullable()->comment('Данные запроса к банку');
            $table->json('callback_data')->nullable()->comment('Данные callback от банка');
            $table->boolean('webhook_sent')->default(false)->comment('Отправлено ли уведомление');
            $table->text('webhook_response')->nullable()->comment('Ответ от стороннего сервера');
            $table->integer('webhook_attempts')->default(0)->comment('Количество попыток отправки');
            $table->timestamps();

            $table->index('status');
            $table->index('order_id');
            $table->index('transaction_id');
            $table->index('external_order_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tochka_payment_histories');
    }
};
