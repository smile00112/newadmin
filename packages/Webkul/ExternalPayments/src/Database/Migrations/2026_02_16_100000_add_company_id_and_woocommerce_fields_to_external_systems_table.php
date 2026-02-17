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
            $table->unsignedInteger('company_id')->nullable()->after('id');
            $table->string('woocommerce_site_url', 500)->nullable()->after('webhook_url');
            // $table->string('woocommerce_consumer_key', 255)->nullable()->after('woocommerce_site_url');
            //$table->string('woocommerce_consumer_secret', 255)->nullable()->after('woocommerce_consumer_key');
            $table->string('paid_order_status', 50)->default('processing');

            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_systems', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
            $table->dropColumn([
                'company_id',
                'woocommerce_site_url',
                //'woocommerce_consumer_key',
                //'woocommerce_consumer_secret',
                'paid_order_status',
            ]);
        });
    }
};
