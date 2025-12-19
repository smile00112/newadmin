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
        Schema::create('newsletters_telegram_bot_instances', function (Blueprint $table) {
            $table->id();
            $table->string('bot_token');
            $table->string('bot_username')->nullable();
            $table->string('bot_name')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('mailing_list_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

            $table->foreign('mailing_list_id')
                ->references('id')
                ->on('newsletters_mailing_lists')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_telegram_bot_instances');
    }
};

