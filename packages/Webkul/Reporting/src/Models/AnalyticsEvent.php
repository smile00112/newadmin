<?php

namespace Webkul\Reporting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_name',
        'customer_id',
        'session_id',
        'order_id',
        'channel',
        'location_id',
        'device_type',
        'properties',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Customer\Models\Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Sales\Models\Order::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(AnalyticsLocation::class, 'location_id');
    }
}
