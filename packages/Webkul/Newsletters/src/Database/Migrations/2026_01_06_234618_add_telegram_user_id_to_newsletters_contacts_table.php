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
        Schema::table('newsletters_contacts', function (Blueprint $table) {
            $table->string('telegram_user_id')->nullable()->after('phone')->comment('ID пользователя в Telegram');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_contacts', function (Blueprint $table) {
            $table->dropColumn('telegram_user_id');
        });
    }
};
