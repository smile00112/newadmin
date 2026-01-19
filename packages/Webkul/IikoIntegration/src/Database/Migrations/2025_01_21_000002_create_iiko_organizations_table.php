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
        Schema::create('iiko_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('iiko_id')->unique()->index();
            $table->string('name')->nullable();
            $table->json('organization_data')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index('iiko_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iiko_organizations');
    }
};
