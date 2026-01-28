<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Bonus\Models\BonusHistory;
use Webkul\Bonus\Models\BonusLevel;

trait BonusTrait
{
    /**
     * Get the bonus level that owns the customer.
     *
     * @return BelongsTo
     */
    public function bonusLevel(): BelongsTo
    {
        return $this->belongsTo(BonusLevel::class, 'bonus_level_id');
    }

    /**
     * Get bonus history for the customer.
     *
     * @return HasMany
     */
    public function bonusHistory(): HasMany
    {
        return $this->hasMany(BonusHistory::class, 'customer_id');
    }

    /**
     * Get available bonus balance (not expired).
     *
     * @return float
     */
    public function getAvailableBonusBalance(): float
    {
        return (float) $this->bonusHistory()
            ->active()
            ->sum('amount');
    }

    /**
     * Get total bonus balance (including expired).
     *
     * @return float
     */
    public function getTotalBonusBalance(): float
    {
        return (float) ($this->bonus_balance ?? 0);
    }
}
