<?php

namespace Webkul\Reporting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsPaymentAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'customer_id',
        'session_id',
        'channel',
        'payment_method',
        'amount',
        'currency',
        'status',
        'fail_reason',
        'duration_seconds',
        'created_at',
    ];

    protected $casts = [
        'amount'     => 'decimal:4',
        'created_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Sales\Models\Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Customer\Models\Customer::class);
    }
}
