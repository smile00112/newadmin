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
        Schema::table('alfabank_saved_cards', function (Blueprint $table) {
            $table->string('client_id', 255)->nullable()->after('customer_id');
            $table->index('client_id');
            $table->unique(['client_id', 'binding_id'], 'alfabank_saved_cards_client_binding_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alfabank_saved_cards', function (Blueprint $table) {
            $table->dropUnique('alfabank_saved_cards_client_binding_unique');
            $table->dropIndex(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
