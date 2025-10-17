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
        Schema::create('newsletters_customer_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->string('name');
            $table->string('greenapi_chat_id')->nullable();
            $table->boolean('delivered')->nullable();
            $table->boolean('viewed')->nullable();
            $table->boolean('incoming_message')->nullable();
            $table->unsignedBigInteger('mailing_list_id');
            $table->timestamps();

            $table->foreign('mailing_list_id')->references('id')->on('newsletters_mailing_lists')->onDelete('cascade');
            $table->unique(['phone_number', 'mailing_list_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_customer_numbers');
    }
};
