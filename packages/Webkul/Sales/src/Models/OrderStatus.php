<?php

namespace Webkul\Sales\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $table = 'order_statuses';

    protected $fillable = [
        'code',
        'name',
        'icon',
        'color',
        'sort_order',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Get all statuses ordered by sort_order.
     */
    public static function ordered()
    {
        return static::orderBy('sort_order')->get();
    }

    /**
     * Get status name by code.
     */
    public static function nameByCode(string $code): string
    {
        $status = static::where('code', $code)->first();
        return $status ? $status->name : $code;
    }

    /**
     * Get all statuses as code => name map.
     */
    public static function allAsMap(): array
    {
        return static::orderBy('sort_order')
            ->pluck('name', 'code')
            ->toArray();
    }

    /**
     * Get all statuses as code => color map.
     */
    public static function colorMap(): array
    {
        return static::orderBy('sort_order')
            ->pluck('color', 'code')
            ->toArray();
    }

    /**
     * Get all statuses as array of arrays (for JSON/JS).
     */
    public static function allForJs(): array
    {
        return static::orderBy('sort_order')
            ->get(['code', 'name', 'icon', 'color', 'sort_order', 'is_system'])
            ->toArray();
    }
}
