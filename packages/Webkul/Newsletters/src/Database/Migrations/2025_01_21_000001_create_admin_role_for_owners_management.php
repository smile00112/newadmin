<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Проверяем, существует ли уже роль с таким именем
        $existingRole = DB::table('roles')->where('name', 'Admin')->first();
        
        if (!$existingRole) {
            DB::table('roles')->insert([
                'name' => 'Admin',
                'description' => 'Administrator role with full access to manage all company owners',
                'permission_type' => 'all',
                'permissions' => json_encode([
                    'newsletters.owners.view',
                    'newsletters.owners.edit',
                    'newsletters.owners.delete',
                    'newsletters.owners.toggle-status',
                    'newsletters.owners.topup',
                    'newsletters.admin-accounts.view',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('name', 'Admin')->delete();
    }
};




