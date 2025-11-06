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
            $table->json('message_links')->nullable()->after('message_text')->comment('Ссылки на фото или видео');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_mailing_lists', function (Blueprint $table) {
            $table->dropColumn('message_links');
        });
    }
};
