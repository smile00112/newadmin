<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_incidents', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id')->nullable()->index();
            $table->unsignedInteger('customer_id')->nullable()->index();
            $table->string('channel', 50)->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->enum('type', ['complaint', 'incorrect_order', 'refund', 'cancel', 'feedback'])->index();
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('rating')->nullable()->comment('1-5');
            $table->string('feedback_theme', 100)->nullable()->index();
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('location_id')->references('id')->on('analytics_locations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_incidents');
    }
};
