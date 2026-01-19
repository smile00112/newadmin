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
        Schema::create('iiko_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('sync_type', ['order', 'menu', 'organization', 'webhook', 'api_request'])->index();
            $table->string('entity_id')->nullable()->index();
            $table->enum('status', ['success', 'error', 'pending'])->default('pending')->index();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['sync_type', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iiko_sync_logs');
    }
};
