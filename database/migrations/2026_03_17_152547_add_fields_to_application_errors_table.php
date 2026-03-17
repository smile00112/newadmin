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
        Schema::table('application_errors', function (Blueprint $table): void {
            $table->string('level')
                ->default('error')
                ->after('source');

            $table->string('platform')
                ->nullable()
                ->after('level');

            $table->boolean('is_read')
                ->default(false)
                ->after('platform');

            $table->string('assigned_to')
                ->nullable()
                ->after('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_errors', function (Blueprint $table): void {
            $table->dropColumn([
                'level',
                'platform',
                'is_read',
                'assigned_to',
            ]);
        });
    }
};
