<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Переименовываем message_delay в message_delay_to используя DB::statement для совместимости
        if (Schema::hasColumn('newsletters_mailing_lists', 'message_delay')) {
            DB::statement('ALTER TABLE newsletters_mailing_lists CHANGE message_delay message_delay_to INTEGER UNSIGNED DEFAULT 5 COMMENT "Максимальная задержка между сообщениями в секундах"');
        }
        
        Schema::table('newsletters_mailing_lists', function (Blueprint $table) {
            // Добавляем новое поле message_delay_from
            if (!Schema::hasColumn('newsletters_mailing_lists', 'message_delay_from')) {
                $table->integer('message_delay_from')->unsigned()->default(5)->after('message_delay_to')->comment('Минимальная задержка между сообщениями в секундах');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_mailing_lists', function (Blueprint $table) {
            // Удаляем message_delay_from
            if (Schema::hasColumn('newsletters_mailing_lists', 'message_delay_from')) {
                $table->dropColumn('message_delay_from');
            }
        });
        
        // Возвращаем старое имя используя DB::statement
        if (Schema::hasColumn('newsletters_mailing_lists', 'message_delay_to')) {
            DB::statement('ALTER TABLE newsletters_mailing_lists CHANGE message_delay_to message_delay INTEGER UNSIGNED DEFAULT 5 COMMENT "Задержка между сообщениями в секундах"');
        }
    }
};
