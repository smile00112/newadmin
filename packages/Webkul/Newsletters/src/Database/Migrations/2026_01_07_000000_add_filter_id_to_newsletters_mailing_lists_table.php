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
            $table->unsignedBigInteger('filter_id')->nullable()->after('company_id');
            $table->foreign('filter_id')->references('id')->on('newsletters_contact_filters')->onDelete('set null');
            $table->index('filter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletters_mailing_lists', function (Blueprint $table) {
            $table->dropForeign(['filter_id']);
            $table->dropIndex(['filter_id']);
            $table->dropColumn('filter_id');
        });
    }
};




