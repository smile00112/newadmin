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
        Schema::create('tochka_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('client_id')->nullable()->comment('Client ID from Tochka Bank dashboard');
            $table->text('jwt_token')->nullable()->comment('JWT token for authorization');
            $table->string('api_base_url')->nullable()->comment('Base URL for API requests');
            $table->string('webhook_url')->nullable()->comment('URL for webhook registration');
            $table->string('customer_code')->nullable()->comment('Customer code');
            $table->string('merchant_id')->nullable()->comment('Merchant identifier');
            $table->string('consumer_id')->nullable()->comment('Consumer identifier (saved from API response)');
            $table->json('payment_mode')->nullable()->comment('Payment methods: ["sbp", "card"]');
            $table->boolean('save_card')->default(false)->comment('Save card for future payments');
            $table->boolean('pre_authorization')->default(false)->comment('Use pre-authorization');
            $table->integer('ttl')->default(10080)->comment('Payment link TTL in minutes (default 7 days)');
            $table->decimal('min_amount', 10, 2)->default(1.00)->comment('Minimum payment amount');
            $table->boolean('is_active')->default(true)->comment('Is module active for company');
            $table->timestamps();

            $table->index('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tochka_payment_settings');
    }
};
