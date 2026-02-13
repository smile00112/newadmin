<?php

namespace Webkul\Bonus\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webkul\Bonus\Contracts\BonusLevel as BonusLevelContract;

class BonusLevel extends Model implements BonusLevelContract
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'cashback_percent',
        'threshold_value',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'cashback_percent' => 'integer',
        'threshold_value' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Calculation types.
     */
    public const CALCULATION_TYPE_ORDERS_COUNT = 'orders_count';
    public const CALCULATION_TYPE_TOTAL_SPENT = 'total_spent';
    public const CALCULATION_TYPE_CART_VALUE = 'cart_value';

    /**
     * Get active levels ordered by sort order.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Get levels ordered by sort order.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
