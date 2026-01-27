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
        Schema::create('iiko_payment_types', function (Blueprint $table) {
            $table->id();
            $table->string('organization_id')->index();
            $table->string('iiko_id')->index();
            $table->string('name')->nullable();
            $table->string('kind')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('payment_type_data')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'iiko_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iiko_payment_types');
    }
};
