<?php

namespace Webkul\Bonus\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Bonus\Contracts\BonusTransaction as BonusTransactionContract;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Sales\Models\OrderProxy;

class BonusTransaction extends Model implements BonusTransactionContract
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'order_id',
        'type',
        'amount',
        'currency_code',
        'description',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:4',
        'expires_at' => 'datetime',
    ];

    /**
     * Transaction types.
     */
    public const TYPE_ACCRUAL = 'accrual';
    public const TYPE_DEDUCTION = 'deduction';
    public const TYPE_RETURN = 'return';

    /**
     * Get the customer that owns the transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }

    /**
     * Get the order associated with the transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderProxy::modelClass());
    }

    /**
     * Scope a query to only include accrual transactions.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccruals($query)
    {
        return $query->where('type', self::TYPE_ACCRUAL);
    }

    /**
     * Scope a query to only include non-expired transactions.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}
