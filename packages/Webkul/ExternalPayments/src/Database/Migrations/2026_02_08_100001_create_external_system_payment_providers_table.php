<?php

declare(strict_types=1);

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
        Schema::create('external_system_payment_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_system_id')->constrained('external_systems')->cascadeOnDelete();
            $table->string('payment_provider', 64);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['external_system_id', 'payment_provider'], 'ex_id_pprov');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_system_payment_providers');
    }
};
