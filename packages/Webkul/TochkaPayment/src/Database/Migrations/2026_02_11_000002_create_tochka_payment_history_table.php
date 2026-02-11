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
        Schema::create('tochka_payment_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('external_order_id')->nullable()->comment('External order ID');
            $table->string('order_id')->nullable()->comment('Internal order ID');
            $table->decimal('amount', 12, 2)->comment('Payment amount');
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->text('payment_url')->nullable()->comment('Payment URL from bank');
            $table->string('status')->default('pending')->comment('pending, paid, failed, cancelled');
            $table->string('operation_id')->nullable()->comment('Operation ID from bank');
            $table->string('consumer_id')->nullable()->comment('Consumer ID from API response');
            $table->string('payment_link_id')->nullable()->comment('Payment link ID');
            $table->json('request_data')->nullable()->comment('Request data sent to bank');
            $table->json('response_data')->nullable()->comment('Response data from bank');
            $table->json('webhook_data')->nullable()->comment('Webhook data from bank');
            $table->timestamps();

            $table->index('company_id');
            $table->index('order_id');
            $table->index('operation_id');
            $table->index('status');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tochka_payment_history');
    }
};
