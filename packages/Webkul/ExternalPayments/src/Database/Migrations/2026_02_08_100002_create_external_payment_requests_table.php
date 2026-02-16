<?php

declare(strict_types=1);

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
        Schema::create('external_payment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_system_id')->constrained('external_systems')->cascadeOnDelete();
            $table->string('payment_provider', 64);
            $table->unsignedBigInteger('provider_payment_id');
            $table->string('provider_order_id');
            $table->string('external_order_id')->nullable();
            $table->string('status', 32)->default('pending');
            $table->boolean('webhook_sent')->default(false);
            $table->timestamp('webhook_sent_at')->nullable();
            $table->timestamps();

            $table->index(['payment_provider', 'provider_payment_id'], 'ex_pprov_ppayid');
            $table->index('external_system_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_payment_requests');
    }
};
