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
        Schema::table('newsletters_contact_groups', function (Blueprint $table) {
            $table->boolean('has_external_integration')->default(false)->after('description');
            $table->string('request_url')->nullable()->after('has_external_integration');
            $table->string('request_token')->nullable()->after('request_url');
            $table->integer('auto_request_frequency')->nullable()->after('request_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_contact_groups', function (Blueprint $table) {
            $table->dropColumn([
                'has_external_integration',
                'request_url',
                'request_token',
                'auto_request_frequency',
            ]);
        });
    }
};



