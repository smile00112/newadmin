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
        Schema::table('newsletters_whatsapp_instances', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('mailing_list_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_whatsapp_instances', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
