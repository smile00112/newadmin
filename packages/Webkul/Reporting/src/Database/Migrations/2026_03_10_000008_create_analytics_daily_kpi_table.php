<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Daily pre-aggregated KPI snapshot for fast dashboard queries
        Schema::create('analytics_daily_kpi', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('channel', 50)->nullable();
            $table->unsignedBigInteger('location_id')->nullable();

            // North Star
            $table->unsignedInteger('total_orders')->default(0);
            $table->unsignedInteger('online_orders')->default(0);
            $table->decimal('online_order_share', 8, 4)->default(0);
            $table->decimal('gmv', 14, 4)->default(0);
            $table->decimal('aov', 12, 4)->default(0);
            $table->unsignedInteger('orders_within_sla')->default(0);
            $table->decimal('sla_pct', 8, 4)->default(0);
            $table->unsignedInteger('avg_order_ready_seconds')->default(0);
            $table->unsignedInteger('repeat_customers')->default(0);
            $table->decimal('repeat_rate', 8, 4)->default(0);

            // Users
            $table->unsignedInteger('dau')->default(0);
            $table->unsignedInteger('new_users')->default(0);
            $table->unsignedInteger('sessions')->default(0);
            $table->unsignedInteger('sessions_with_order')->default(0);
            $table->decimal('session_to_order_rate', 8, 4)->default(0);

            // Financial
            $table->decimal('revenue_app', 14, 4)->default(0);
            $table->decimal('revenue_kiosk', 14, 4)->default(0);
            $table->decimal('revenue_cashier', 14, 4)->default(0);
            $table->unsignedInteger('discounted_orders')->default(0);
            $table->decimal('discount_total', 14, 4)->default(0);

            // Operations
            $table->unsignedInteger('avg_accept_seconds')->default(0);
            $table->unsignedInteger('avg_prepare_seconds')->default(0);
            $table->unsignedInteger('avg_serve_seconds')->default(0);
            $table->unsignedInteger('incorrect_orders')->default(0);
            $table->unsignedInteger('cancelled_orders')->default(0);
            $table->unsignedInteger('refunded_orders')->default(0);

            // Payments
            $table->unsignedInteger('payment_attempts')->default(0);
            $table->unsignedInteger('payment_successes')->default(0);
            $table->decimal('payment_success_rate', 8, 4)->default(0);

            // Incidents
            $table->unsignedInteger('complaints')->default(0);
            $table->unsignedInteger('incidents_resolved')->default(0);

            $table->timestamps();

            $table->unique(['date', 'channel', 'location_id'], 'daily_kpi_unique');
            $table->foreign('location_id')->references('id')->on('analytics_locations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_kpi');
    }
};
