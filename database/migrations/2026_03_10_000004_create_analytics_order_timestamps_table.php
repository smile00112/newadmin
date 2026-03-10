<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_order_timestamps', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id')->unique();
            $table->string('channel', 50)->nullable()->index();
            $table->unsignedBigInteger('location_id')->nullable()->index();
            $table->string('order_type', 30)->nullable()->comment('dine_in, take_away');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('preparing_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('within_sla')->nullable();
            $table->unsignedInteger('sla_seconds')->nullable()->comment('SLA threshold used');
            $table->unsignedInteger('total_seconds')->nullable()->comment('created → ready');

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('location_id')->references('id')->on('analytics_locations')->nullOnDelete();

            $table->index(['channel', 'created_at']);
            $table->index(['location_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_order_timestamps');
    }
};
