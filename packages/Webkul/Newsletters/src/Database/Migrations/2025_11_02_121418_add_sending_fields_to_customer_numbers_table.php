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
        Schema::table('newsletters_customer_numbers', function (Blueprint $table) {
            $table->boolean('sending')->default(false)->nullable()->after('incoming_message')->comment('Флаг отправки сообщения');
            $table->boolean('send_error')->default(false)->nullable()->after('sending')->comment('Флаг ошибки отправки');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_customer_numbers', function (Blueprint $table) {
            $table->dropColumn(['sending', 'send_error']);
        });
    }
};







