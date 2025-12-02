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
        // Add foreign key to admins table
        Schema::table('admins', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });

        // Add foreign keys to newsletters tables
        Schema::table('newsletters_mailing_lists', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        Schema::table('newsletters_whatsapp_instances', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        Schema::table('newsletters_customer_numbers', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        Schema::table('newsletters_contact_groups', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        Schema::table('newsletters_contacts', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        Schema::table('newsletters_stop_list', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::table('newsletters_mailing_lists', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::table('newsletters_whatsapp_instances', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::table('newsletters_customer_numbers', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::table('newsletters_contact_groups', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::table('newsletters_contacts', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::table('newsletters_stop_list', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
    }
};

