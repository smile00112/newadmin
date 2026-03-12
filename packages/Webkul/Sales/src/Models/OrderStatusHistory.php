<?php

namespace Webkul\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Sales\Contracts\OrderStatusHistory as OrderStatusHistoryContract;

class OrderStatusHistory extends Model implements OrderStatusHistoryContract
{
    /**
     * Indicates if the model should be timestamped.
     *
     * We only store `created_at` which is handled by the DB default.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'old_status',
        'new_status',
        'user_type',
        'user_id',
        'user_name',
        'source',
        'created_at',
    ];

    /**
     * Get the order associated with this history entry.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderProxy::modelClass(), 'order_id');
    }

    /**
     * Get the human readable label for old status.
     */
    public function getOldStatusLabelAttribute(): ?string
    {
        if (! $this->old_status) {
            return null;
        }

        return OrderStatus::nameByCode($this->old_status);
    }

    /**
     * Get the human readable label for new status.
     */
    public function getNewStatusLabelAttribute(): string
    {
        return OrderStatus::nameByCode($this->new_status);
    }
}

