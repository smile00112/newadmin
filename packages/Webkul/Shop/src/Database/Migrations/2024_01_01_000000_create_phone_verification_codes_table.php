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
        Schema::create('phone_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('code', 6);
            $table->string('auth_type', 10)->nullable();
            $table->integer('customer_id')->unsigned();
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index(['phone', 'code', 'expires_at', 'customer_id', 'auth_type'], 'phone_verification_codes_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_verification_codes');
    }
};
