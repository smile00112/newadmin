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
            $table->enum('channel_type', ['whatsapp', 'email', 'telegram'])
                ->default('whatsapp')
                ->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_mailing_lists', function (Blueprint $table) {
            $table->dropColumn('channel_type');
        });
    }
};



