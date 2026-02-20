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
        Schema::create('tochka_payment_buyers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->comment('Company that owns this buyer');
            $table->string('client_email')->comment('Buyer email');
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('consumer_id')->nullable()->comment('Consumer ID from Tochka Bank API');
            $table->timestamps();

            $table->unique(['company_id', 'client_email']);
            $table->index('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tochka_payment_buyers');
    }
};
