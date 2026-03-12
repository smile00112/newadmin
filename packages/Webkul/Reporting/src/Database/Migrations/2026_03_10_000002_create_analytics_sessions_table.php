<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->unique();
            $table->unsignedInteger('customer_id')->nullable()->index();
            $table->string('channel', 50)->nullable()->index();
            $table->unsignedBigInteger('location_id')->nullable()->index();
            $table->string('device_type', 30)->nullable();
            $table->boolean('is_first_session')->default(false);
            $table->unsignedInteger('visit_number')->default(1);
            $table->unsignedInteger('page_views')->default(0);
            $table->unsignedInteger('events_count')->default(0);
            $table->boolean('has_order')->default(false);
            $table->unsignedInteger('order_id')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();

            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();

            $table->index(['customer_id', 'started_at']);
            $table->index(['channel', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_sessions');
    }
};
