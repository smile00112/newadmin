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
        // Remove mailing_list_id from mail_instances
        Schema::table('newsletters_mail_instances', function (Blueprint $table) {
            $table->dropForeign(['mailing_list_id']);
            $table->dropColumn('mailing_list_id');
        });

        // Remove mailing_list_id from telegram_bot_instances
        Schema::table('newsletters_telegram_bot_instances', function (Blueprint $table) {
            $table->dropForeign(['mailing_list_id']);
            $table->dropColumn('mailing_list_id');
        });

        // Remove mailing_list_id from whatsapp_instances
        Schema::table('newsletters_whatsapp_instances', function (Blueprint $table) {
            $table->dropForeign(['mailing_list_id']);
            $table->dropColumn('mailing_list_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore mailing_list_id to mail_instances
        Schema::table('newsletters_mail_instances', function (Blueprint $table) {
            $table->unsignedBigInteger('mailing_list_id')->nullable()->after('company_id');
            $table->foreign('mailing_list_id')
                ->references('id')
                ->on('newsletters_mailing_lists')
                ->onDelete('set null');
        });

        // Restore mailing_list_id to telegram_bot_instances
        Schema::table('newsletters_telegram_bot_instances', function (Blueprint $table) {
            $table->unsignedBigInteger('mailing_list_id')->nullable()->after('company_id');
            $table->foreign('mailing_list_id')
                ->references('id')
                ->on('newsletters_mailing_lists')
                ->onDelete('set null');
        });

        // Restore mailing_list_id to whatsapp_instances
        Schema::table('newsletters_whatsapp_instances', function (Blueprint $table) {
            $table->unsignedBigInteger('mailing_list_id')->nullable()->after('company_id');
            $table->foreign('mailing_list_id')
                ->references('id')
                ->on('newsletters_mailing_lists')
                ->onDelete('set null');
        });
    }
};




