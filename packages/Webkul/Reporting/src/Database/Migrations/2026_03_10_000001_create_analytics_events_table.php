<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name', 100)->index();
            $table->unsignedInteger('customer_id')->nullable()->index();
            $table->string('session_id', 64)->nullable()->index();
            $table->unsignedInteger('order_id')->nullable()->index();
            $table->string('channel', 50)->nullable()->index();
            $table->unsignedBigInteger('location_id')->nullable()->index();
            $table->string('device_type', 30)->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();

            $table->index(['event_name', 'created_at']);
            $table->index(['customer_id', 'event_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
