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
        Schema::create('newsletters_mailing_list_whatsapp_instance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mailing_list_id');
            $table->unsignedBigInteger('whatsapp_instance_id');
            $table->timestamps();

            $table->foreign('mailing_list_id', 'mlwa_mll_wa')
                ->references('id')
                ->on('newsletters_mailing_lists')
                ->onDelete('cascade');

            $table->foreign('whatsapp_instance_id', 'mlwa_wai')
                ->references('id')
                ->on('newsletters_whatsapp_instances')
                ->onDelete('cascade');

            $table->unique(['mailing_list_id', 'whatsapp_instance_id'], 'mlwa_ml_wai_uk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_mailing_list_whatsapp_instance');
    }
};



