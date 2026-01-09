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
        // Разрешения для управления рассылками (общие для обеих ролей)
        $mailingPermissions = [
            // Mailing Lists
            'newsletters.mailing-lists',
            'newsletters.mailing-lists.create',
            'newsletters.mailing-lists.edit',
            'newsletters.mailing-lists.delete',
            'newsletters.mailing-lists.send',
            
            // WhatsApp Instances
            'newsletters.whatsapp-instances',
            'newsletters.whatsapp-instances.create',
            'newsletters.whatsapp-instances.edit',
            'newsletters.whatsapp-instances.delete',
            
            // Customer Numbers
            'newsletters.customer-numbers',
            'newsletters.customer-numbers.create',
            'newsletters.customer-numbers.edit',
            'newsletters.customer-numbers.delete',
            'newsletters.customer-numbers.import',
            
            // Stop List
            'newsletters.stop-list',
            'newsletters.stop-list.create',
            'newsletters.stop-list.edit',
            'newsletters.stop-list.delete',
            
            // Messages
            'newsletters.messages.view',
            'newsletters.messages.send',
            
            // Contact Groups
            'newsletters.contact-groups',
            'newsletters.contact-groups.create',
            'newsletters.contact-groups.edit',
            'newsletters.contact-groups.delete',
            'newsletters.contact-groups.import',
            
            // Contacts
            'newsletters.contacts.view',
            'newsletters.contacts.delete',
            
            // Reports
            'newsletters.reports.view',
        ];

        // Разрешения для владельца компании (включает все разрешения для рассылок + управление менеджерами и счетом)
        $ownerPermissions = array_merge($mailingPermissions, [
            // Managers
            'newsletters.managers',
            'newsletters.managers.create',
            'newsletters.managers.edit',
            'newsletters.managers.delete',
            
            // Account
            'newsletters.account.view',
            'newsletters.account.topup',
        ]);

        // Проверяем и создаем роль "Владелец компании"
        $ownerRole = DB::table('roles')->where('name', 'Владелец компании')->first();
        
        if (!$ownerRole) {
            DB::table('roles')->insert([
                'name' => 'Владелец компании',
                'description' => 'Владелец компании с полным доступом: создание менеджеров, пополнение счета и управление рассылками',
                'permission_type' => 'custom',
                'permissions' => json_encode($ownerPermissions),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // Обновляем разрешения, если роль уже существует
            DB::table('roles')
                ->where('name', 'Владелец компании')
                ->update([
                    'description' => 'Владелец компании с полным доступом: создание менеджеров, пополнение счета и управление рассылками',
                    'permission_type' => 'custom',
                    'permissions' => json_encode($ownerPermissions),
                    'updated_at' => now(),
                ]);
        }

        // Проверяем и создаем роль "Менеджер рассылок"
        $managerRole = DB::table('roles')->where('name', 'Менеджер рассылок')->first();
        
        if (!$managerRole) {
            DB::table('roles')->insert([
                'name' => 'Менеджер рассылок',
                'description' => 'Менеджер рассылок с доступом только к управлению рассылками',
                'permission_type' => 'custom',
                'permissions' => json_encode($mailingPermissions),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // Обновляем разрешения, если роль уже существует
            DB::table('roles')
                ->where('name', 'Менеджер рассылок')
                ->update([
                    'description' => 'Менеджер рассылок с доступом только к управлению рассылками',
                    'permission_type' => 'custom',
                    'permissions' => json_encode($mailingPermissions),
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('name', 'Владелец компании')->delete();
        DB::table('roles')->where('name', 'Менеджер рассылок')->delete();
    }
};



