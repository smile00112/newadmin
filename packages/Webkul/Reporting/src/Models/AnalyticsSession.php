<?php

namespace Webkul\Reporting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'customer_id',
        'channel',
        'location_id',
        'device_type',
        'is_first_session',
        'visit_number',
        'page_views',
        'events_count',
        'has_order',
        'order_id',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'is_first_session' => 'boolean',
        'has_order'        => 'boolean',
        'started_at'       => 'datetime',
        'ended_at'         => 'datetime',
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
