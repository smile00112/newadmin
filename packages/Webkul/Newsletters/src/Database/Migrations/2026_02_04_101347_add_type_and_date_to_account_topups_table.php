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
        Schema::table('account_topups', function (Blueprint $table) {
            $table->enum('type', ['topup', 'deduction'])->default('topup')->after('account_id');
            $table->dateTime('transaction_date')->nullable()->after('amount');
            $table->index(['type', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_topups', function (Blueprint $table) {
            $table->dropIndex(['type', 'transaction_date']);
            $table->dropColumn(['type', 'transaction_date']);
        });
    }
};
