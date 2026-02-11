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
        Schema::create('tochka_payment_webhooks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('payment_history_id')->nullable();
            $table->string('webhook_type')->comment('acquiringInternetPayment, incomingPayment, etc.');
            $table->text('raw_payload')->comment('Original JWT token');
            $table->json('decoded_data')->nullable()->comment('Decoded webhook data');
            $table->string('status')->default('pending')->comment('pending, processed, failed');
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('payment_history_id');
            $table->index('webhook_type');
            $table->index('status');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('payment_history_id')->references('id')->on('tochka_payment_history')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tochka_payment_webhooks');
    }
};
