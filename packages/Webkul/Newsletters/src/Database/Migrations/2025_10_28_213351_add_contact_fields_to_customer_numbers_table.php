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
            $table->unsignedBigInteger('contact_id')->nullable()->after('id')->comment('ID контакта');
            $table->enum('status', ['waiting_for_send', 'sent', 'failed'])->default('waiting_for_send')->after('whatsapp_instance_id')->comment('Статус отправки сообщения');
            $table->dateTime('sent_at')->nullable()->after('status')->comment('Время отправки сообщения');

            $table->foreign('contact_id')->references('id')->on('newsletters_contacts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_customer_numbers', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropColumn(['contact_id', 'status', 'sent_at']);
        });
    }
};
