<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_live_activity_tokens', function (Blueprint $table) {
            $table->unsignedInteger('last_version')->default(0)->after('last_apns_timestamp');
        });
    }

    public function down(): void
    {
        Schema::table('order_live_activity_tokens', function (Blueprint $table) {
            $table->dropColumn('last_version');
        });
    }
};
