<?php

declare(strict_types=1);

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
        Schema::table('external_systems', function (Blueprint $table) {
            $table->string('woocommerce_consumer_key', 255)->nullable()->after('woocommerce_site_url');
            $table->string('woocommerce_consumer_secret', 255)->nullable()->after('woocommerce_consumer_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_systems', function (Blueprint $table) {
            $table->dropColumn(['woocommerce_consumer_key', 'woocommerce_consumer_secret']);
        });
    }
};
