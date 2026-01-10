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
        Schema::create('newsletters_account_warmings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('selected_account_ids');
            $table->json('phrases'); // [{"question": "...", "answer": "..."}, ...]
            $table->integer('delay_from')->nullable();
            $table->integer('delay_to')->nullable();
            $table->boolean('active')->default(false);
            $table->string('status')->default('created'); // created, pending, running, paused, completed
            $table->unsignedBigInteger('company_id')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_account_warmings');
    }
};


