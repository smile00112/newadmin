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
        Schema::create('newsletters_account_warming_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_warming_id');
            $table->unsignedBigInteger('whatsapp_instance_id');
            $table->integer('messages_sent')->default(0);
            $table->integer('messages_received')->default(0);
            $table->dateTime('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('account_warming_id', 'awi_id_aw_c')
                ->references('id')
                ->on('newsletters_account_warmings')
                ->onDelete('cascade');

            $table->foreign('whatsapp_instance_id', 'wai_id_wi_c')
                ->references('id')
                ->on('newsletters_whatsapp_instances')
                ->onDelete('cascade');

            $table->unique(['account_warming_id', 'whatsapp_instance_id'], 'awp_aw_wai_uk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletters_account_warming_participants');
    }
};


