<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Customer\Models\CustomerProxy;

class BonusLevel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bonus_levels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'sort_order',
        'cashback_percent',
        'min_orders',
        'min_amount',
        'min_cart_value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'cashback_percent' => 'decimal:2',
        'min_amount' => 'decimal:4',
        'min_cart_value' => 'decimal:4',
    ];

    /**
     * Get customers for this level.
     *
     * @return HasMany
     */
    public function customers(): HasMany
    {
        return $this->hasMany(CustomerProxy::modelClass(), 'bonus_level_id');
    }

    /**
     * Check if customer meets level requirements.
     *
     * @param  int|null  $ordersCount
     * @param  float|null  $totalSpent
     * @param  float|null  $cartValue
     * @return bool
     */
    public function meetsRequirements(?int $ordersCount = null, ?float $totalSpent = null, ?float $cartValue = null): bool
    {
        if ($this->min_orders !== null && ($ordersCount === null || $ordersCount < $this->min_orders)) {
            return false;
        }

        if ($this->min_amount !== null && ($totalSpent === null || $totalSpent < $this->min_amount)) {
            return false;
        }

        if ($this->min_cart_value !== null && ($cartValue === null || $cartValue < $this->min_cart_value)) {
            return false;
        }

        return true;
    }
}
