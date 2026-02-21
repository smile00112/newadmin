<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account_topups', function (Blueprint $table): void {
            $table->string('provider_key', 64)->nullable()->after('type');
            $table->string('provider_payment_id', 191)->nullable()->after('provider_key');
            $table->enum('status', ['pending', 'paid', 'failed'])->default('paid')->after('provider_payment_id');
            $table->dateTime('paid_at')->nullable()->after('transaction_date');
            $table->text('payment_url')->nullable()->after('paid_at');

            $table->index('status');
            $table->index(['provider_key', 'provider_payment_id'], 'at_provider_payment_index');
            $table->unique(['provider_key', 'provider_payment_id'], 'at_provider_payment_unique');
        });

        DB::table('account_topups')
            ->whereNull('status')
            ->update(['status' => 'paid']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_topups', function (Blueprint $table): void {
            $table->dropUnique('at_provider_payment_unique');
            $table->dropIndex('at_provider_payment_index');
            $table->dropIndex(['status']);

            $table->dropColumn([
                'provider_key',
                'provider_payment_id',
                'status',
                'paid_at',
                'payment_url',
            ]);
        });
    }
};
