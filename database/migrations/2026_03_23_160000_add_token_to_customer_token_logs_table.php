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
        Schema::table('customer_token_logs', function (Blueprint $table) {
            // Plaintext Sanctum token value (sensitive data).
            // Nullable to keep backward compatibility with existing rows.
            $table->text('token')->nullable()->after('token_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_token_logs', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};

