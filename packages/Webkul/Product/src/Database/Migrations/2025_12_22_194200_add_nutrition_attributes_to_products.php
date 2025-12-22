<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();
        
        // Получаем все локали
        $locales = [];
        if (Schema::hasTable('locales')) {
            $locales = DB::table('locales')->pluck('code')->toArray();
        }
        if (empty($locales)) {
            $locales = [config('app.locale', 'en')];
        }

        // Создаем атрибуты КЖБУ
        $attributes = [
            [
                'code'                => 'calories',
                'admin_name'          => 'Калории (ккал)',
                'type'                => 'price',
                'validation'          => 'decimal',
                'position'            => 100,
                'is_required'         => 0,
                'is_unique'           => 0,
                'value_per_locale'    => 0,
                'value_per_channel'   => 0,
                'default_value'       => null,
                'is_filterable'       => 1,
                'is_configurable'     => 0,
                'is_user_defined'     => 1,
                'is_visible_on_front' => 1,
                'is_comparable'       => 0,
                'enable_wysiwyg'      => 0,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'code'                => 'proteins',
                'admin_name'          => 'Белки (г)',
                'type'                => 'price',
                'validation'          => 'decimal',
                'position'            => 101,
                'is_required'         => 0,
                'is_unique'           => 0,
                'value_per_locale'    => 0,
                'value_per_channel'   => 0,
                'default_value'       => null,
                'is_filterable'       => 1,
                'is_configurable'     => 0,
                'is_user_defined'     => 1,
                'is_visible_on_front' => 1,
                'is_comparable'       => 0,
                'enable_wysiwyg'      => 0,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'code'                => 'fats',
                'admin_name'          => 'Жиры (г)',
                'type'                => 'price',
                'validation'          => 'decimal',
                'position'            => 102,
                'is_required'         => 0,
                'is_unique'           => 0,
                'value_per_locale'    => 0,
                'value_per_channel'   => 0,
                'default_value'       => null,
                'is_filterable'       => 1,
                'is_configurable'     => 0,
                'is_user_defined'     => 1,
                'is_visible_on_front' => 1,
                'is_comparable'       => 0,
                'enable_wysiwyg'      => 0,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'code'                => 'carbs',
                'admin_name'          => 'Углеводы (г)',
                'type'                => 'price',
                'validation'          => 'decimal',
                'position'            => 103,
                'is_required'         => 0,
                'is_unique'           => 0,
                'value_per_locale'    => 0,
                'value_per_channel'   => 0,
                'default_value'       => null,
                'is_filterable'       => 1,
                'is_configurable'     => 0,
                'is_user_defined'     => 1,
                'is_visible_on_front' => 1,
                'is_comparable'       => 0,
                'enable_wysiwyg'      => 0,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
        ];

        // Вставляем атрибуты
        foreach ($attributes as $attributeData) {
            // Проверяем, не существует ли уже атрибут с таким кодом
            $exists = DB::table('attributes')->where('code', $attributeData['code'])->exists();
            
            if (!$exists) {
                // Вставляем атрибут и получаем его ID
                $attributeId = DB::table('attributes')->insertGetId($attributeData);
                
                // Добавляем переводы для всех локалей
                foreach ($locales as $locale) {
                    DB::table('attribute_translations')->insert([
                        'locale'       => $locale,
                        'name'         => $attributeData['admin_name'],
                        'attribute_id' => $attributeId,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Удаляем переводы
        $attributeIds = DB::table('attributes')
            ->whereIn('code', ['calories', 'proteins', 'fats', 'carbs'])
            ->pluck('id')
            ->toArray();
            
        if (!empty($attributeIds)) {
            DB::table('attribute_translations')
                ->whereIn('attribute_id', $attributeIds)
                ->delete();
        }
        
        // Удаляем атрибуты
        DB::table('attributes')
            ->whereIn('code', ['calories', 'proteins', 'fats', 'carbs'])
            ->delete();
    }
};

