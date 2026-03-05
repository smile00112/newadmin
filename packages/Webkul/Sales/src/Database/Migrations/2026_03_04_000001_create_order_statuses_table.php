<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('icon', 50)->nullable();
            $table->string('color', 20)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('order_workflow_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        // Seed only statuses that have constants in Order model (real system statuses)
        // Names can be changed later in the Settings page
        $statuses = [
            ['code' => 'pending',         'name' => 'Новый',             'icon' => 'hourglass-top',          'color' => '#f59e0b', 'sort_order' => 1,  'is_system' => true],
            ['code' => 'pending_payment', 'name' => 'Ожидание оплаты',  'icon' => 'credit-card',            'color' => '#f59e0b', 'sort_order' => 2,  'is_system' => true],
            ['code' => 'processing',      'name' => 'Обработка',        'icon' => 'arrow-repeat',           'color' => '#3b82f6', 'sort_order' => 3,  'is_system' => true],
            ['code' => 'preparing',       'name' => 'Готовим',          'icon' => 'fire',                    'color' => '#6366f1', 'sort_order' => 4,  'is_system' => true],
            ['code' => 'ready',           'name' => 'Готов',            'icon' => 'check2-circle',           'color' => '#10b981', 'sort_order' => 5,  'is_system' => true],
            ['code' => 'completed',       'name' => 'Выполнен',         'icon' => 'check-circle-fill',       'color' => '#22c55e', 'sort_order' => 6,  'is_system' => true],
            ['code' => 'canceled',        'name' => 'Отменён',          'icon' => 'x-circle',                'color' => '#ef4444', 'sort_order' => 7,  'is_system' => true],
            ['code' => 'closed',          'name' => 'Закрыт',           'icon' => 'lock',                    'color' => '#6b7280', 'sort_order' => 8,  'is_system' => true],
            ['code' => 'fraud',           'name' => 'Мошенничество',    'icon' => 'exclamation-triangle',    'color' => '#dc2626', 'sort_order' => 9,  'is_system' => true],
            ['code' => 'failed',          'name' => 'Не удался',        'icon' => 'exclamation-triangle',    'color' => '#dc2626', 'sort_order' => 10, 'is_system' => true],
        ];

        $now = now();
        foreach ($statuses as $status) {
            $status['created_at'] = $now;
            $status['updated_at'] = $now;
            \Illuminate\Support\Facades\DB::table('order_statuses')->insert($status);
        }

        // Seed workflow settings — delivery/payment types come from real system config
        // Pipelines and tab groups start empty — user configures them in Settings
        $deliveryTypes = [];
        foreach (config('carriers', []) as $code => $cfg) {
            $deliveryTypes[] = ['code' => $code, 'name' => $cfg['title'] ?? $code];
        }

        $paymentTypes = [];
        foreach (config('payment_methods', []) as $code => $cfg) {
            $paymentTypes[] = ['code' => $code, 'name' => $cfg['title'] ?? $code];
        }

        $defaults = [
            'new_order_status' => json_encode('pending'),
            'delivery_types'   => json_encode($deliveryTypes),
            'payment_types'    => json_encode($paymentTypes),
            'pipelines'        => json_encode([]),
            'tab_groups'       => json_encode([]),
        ];

        foreach ($defaults as $key => $value) {
            \Illuminate\Support\Facades\DB::table('order_workflow_settings')->insert([
                'key'        => $key,
                'value'      => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_workflow_settings');
        Schema::dropIfExists('order_statuses');
    }
};
