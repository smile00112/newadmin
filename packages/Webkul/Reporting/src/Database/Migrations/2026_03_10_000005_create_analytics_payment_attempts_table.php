<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id')->nullable()->index();
            $table->unsignedInteger('customer_id')->nullable()->index();
            $table->string('session_id', 64)->nullable();
            $table->string('channel', 50)->nullable()->index();
            $table->string('payment_method', 100)->index();
            $table->decimal('amount', 12, 4)->default(0);
            $table->string('currency', 10)->nullable();
            $table->enum('status', ['initiated', 'success', 'failed', 'timeout', 'cancelled'])->index();
            $table->string('fail_reason')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable()->comment('time to complete payment');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();

            $table->index(['payment_method', 'status', 'created_at'], 'pay_attempts_method_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_payment_attempts');
    }
};
