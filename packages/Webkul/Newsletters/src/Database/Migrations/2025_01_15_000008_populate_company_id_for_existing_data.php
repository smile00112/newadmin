<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Webkul\Newsletters\Models\Company;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create default company if it doesn't exist
        $defaultCompany = Company::firstOrCreate(
            ['slug' => 'default'],
            [
                'name' => 'Default Company',
                'description' => 'Default company for existing data',
                'is_active' => true,
            ]
        );

        $companyId = $defaultCompany->id;

        // Update admins table
        DB::table('admins')
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);

        // Update newsletters tables
        DB::table('newsletters_mailing_lists')
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);

        DB::table('newsletters_whatsapp_instances')
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);

        DB::table('newsletters_customer_numbers')
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);

        DB::table('newsletters_contact_groups')
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);

        DB::table('newsletters_contacts')
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);

        DB::table('newsletters_stop_list')
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set all company_id to null
        DB::table('admins')->update(['company_id' => null]);
        DB::table('newsletters_mailing_lists')->update(['company_id' => null]);
        DB::table('newsletters_whatsapp_instances')->update(['company_id' => null]);
        DB::table('newsletters_customer_numbers')->update(['company_id' => null]);
        DB::table('newsletters_contact_groups')->update(['company_id' => null]);
        DB::table('newsletters_contacts')->update(['company_id' => null]);
        DB::table('newsletters_stop_list')->update(['company_id' => null]);

        // Delete default company
        Company::where('slug', 'default')->delete();
    }
};

