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
        Schema::table('newsletters_mailing_lists', function (Blueprint $table) {
            $table->string('mailing_hours_from', 5)->nullable()->after('start_at')->comment('Время начала рассылки (формат HH:MM)');
            $table->string('mailing_hours_to', 5)->nullable()->after('mailing_hours_from')->comment('Время окончания рассылки (формат HH:MM)');
            $table->integer('message_delay')->unsigned()->default(5)->after('mailing_hours_to')->comment('Задержка между сообщениями в секундах');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_mailing_lists', function (Blueprint $table) {
            $table->dropColumn(['mailing_hours_from', 'mailing_hours_to', 'message_delay']);
        });
    }
};

