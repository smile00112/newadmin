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
        Schema::table('inventory_sources', function (Blueprint $table) {
            $table->string('iiko_organization_id')->nullable()->index()->after('status');
            $table->string('iiko_terminal_id')->nullable()->index()->after('iiko_organization_id');
            
            $table->unique('iiko_terminal_id', 'inventory_sources_iiko_terminal_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_sources', function (Blueprint $table) {
            $table->dropUnique('inventory_sources_iiko_terminal_id_unique');
            $table->dropIndex(['iiko_organization_id']);
            $table->dropIndex(['iiko_terminal_id']);
            $table->dropColumn(['iiko_organization_id', 'iiko_terminal_id']);
        });
    }
};
