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
        Schema::create('customer_token_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('customer_id');
            $table->string('token_name')->nullable();
            $table->text('abilities')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');

            $table->index('customer_id');
            $table->index('issued_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_token_logs');
    }
};

