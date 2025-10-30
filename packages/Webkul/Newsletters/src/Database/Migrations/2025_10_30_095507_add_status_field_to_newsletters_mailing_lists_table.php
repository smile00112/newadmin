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
        Schema::table('newsletters_mailing_lists', function (Blueprint $table) {
            $table->string('status')->default('created'); //created, pending, completed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_mailing_lists', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
