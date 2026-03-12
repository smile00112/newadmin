<?php

namespace Webkul\Reporting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsOrderTimestamp extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'channel',
        'location_id',
        'order_type',
        'created_at',
        'accepted_at',
        'preparing_at',
        'ready_at',
        'served_at',
        'completed_at',
        'cancelled_at',
        'within_sla',
        'sla_seconds',
        'total_seconds',
    ];

    protected $casts = [
        'created_at'   => 'datetime',
        'accepted_at'  => 'datetime',
        'preparing_at' => 'datetime',
        'ready_at'     => 'datetime',
        'served_at'    => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'within_sla'   => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Sales\Models\Order::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(AnalyticsLocation::class, 'location_id');
    }

    public function getAcceptDurationAttribute(): ?int
    {
        if (! $this->accepted_at) {
            return null;
        }

        return $this->created_at->diffInSeconds($this->accepted_at);
    }

    public function getPrepareDurationAttribute(): ?int
    {
        if (! $this->accepted_at || ! $this->ready_at) {
            return null;
        }

        return $this->accepted_at->diffInSeconds($this->ready_at);
    }

    public function getHandoffDurationAttribute(): ?int
    {
        if (! $this->ready_at || ! $this->served_at) {
            return null;
        }

        return $this->ready_at->diffInSeconds($this->served_at);
    }
}
