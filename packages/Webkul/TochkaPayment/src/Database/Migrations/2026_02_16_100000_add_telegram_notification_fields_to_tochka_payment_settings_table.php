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
        Schema::table('tochka_payment_settings', function (Blueprint $table) {
            $table->string('telegram_bot_token')->nullable()->after('is_active')->comment('Telegram bot token for notifications');
            $table->string('telegram_chat_id')->nullable()->after('telegram_bot_token')->comment('Telegram chat ID for receiving notifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tochka_payment_settings', function (Blueprint $table) {
            $table->dropColumn(['telegram_bot_token', 'telegram_chat_id']);
        });
    }
};
