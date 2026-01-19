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
        Schema::create('iiko_order_syncs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->string('iiko_order_id')->nullable()->index();
            $table->string('iiko_order_number')->nullable();
            $table->enum('sync_status', ['pending', 'synced', 'failed', 'cancelled'])->default('pending')->index();
            $table->json('sync_data')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->unique('order_id');
            $table->index('sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iiko_order_syncs');
    }
};
