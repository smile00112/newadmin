<?php

namespace Webkul\Sales\Models;

use Illuminate\Database\Eloquent\Model;

class OrderWorkflowSetting extends Model
{
    protected $table = 'order_workflow_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Get a setting by key.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set (create or update) a setting value.
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings as key => value array.
     */
    public static function allAsArray(): array
    {
        return static::pluck('value', 'key')->toArray();
    }
}
