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
            $table->unsignedBigInteger('whatsapp_instance_id')->nullable();
            $table->foreign('whatsapp_instance_id')->references('id')->on('newsletters_whatsapp_instances')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_customer_numbers', function (Blueprint $table) {
            $table->dropForeign(['whatsapp_instance_id']);
            $table->dropColumn('mailing_list_id');
        });
    }
};
