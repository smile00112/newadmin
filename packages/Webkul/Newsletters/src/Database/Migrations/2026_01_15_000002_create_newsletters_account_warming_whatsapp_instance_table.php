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
        Schema::create('newsletters_account_warming_whatsapp_instance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_warming_id');
            $table->unsignedBigInteger('whatsapp_instance_id');
            $table->timestamps();

            $table->foreign('account_warming_id', 'awwi_aw')
                ->references('id')
                ->on('newsletters_account_warmings')
                ->onDelete('cascade');

            $table->foreign('whatsapp_instance_id', 'awwi_wai')
                ->references('id')
                ->on('newsletters_whatsapp_instances')
                ->onDelete('cascade');

            $table->unique(['account_warming_id', 'whatsapp_instance_id'], 'awwi_aw_wai_uk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_account_warming_whatsapp_instance');
    }
};


