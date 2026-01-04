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
        Schema::create('newsletters_mailing_list_telegram_instance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mailing_list_id');
            $table->unsignedBigInteger('telegram_instance_id');
            $table->timestamps();

            $table->foreign('mailing_list_id', 'mltg_mll')
                ->references('id')
                ->on('newsletters_mailing_lists')
                ->onDelete('cascade');

            $table->foreign('telegram_instance_id', 'mltg_tgi')
                ->references('id')
                ->on('newsletters_telegram_bot_instances')
                ->onDelete('cascade');

            $table->unique(['mailing_list_id', 'telegram_instance_id'], 'mltg_ml_tg_uk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_mailing_list_telegram_instance');
    }
};

