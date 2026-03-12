<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_kiosk_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id')->index();
            $table->string('kiosk_code', 50)->index();
            $table->enum('status', ['online', 'offline', 'degraded'])->default('online');
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->unsignedInteger('uptime_today_seconds')->default(0);
            $table->unsignedInteger('downtime_today_seconds')->default(0);
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('analytics_locations')->cascadeOnDelete();
            $table->unique(['location_id', 'kiosk_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_kiosk_status');
    }
};
