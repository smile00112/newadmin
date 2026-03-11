<?php

namespace Webkul\Reporting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsIncident extends Model
{
    protected $fillable = [
        'order_id',
        'customer_id',
        'channel',
        'location_id',
        'type',
        'subject',
        'description',
        'rating',
        'feedback_theme',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Sales\Models\Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Customer\Models\Customer::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(AnalyticsLocation::class, 'location_id');
    }
}
