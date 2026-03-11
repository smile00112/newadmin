<?php

namespace Webkul\Reporting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsDailyKpi extends Model
{
    protected $table = 'analytics_daily_kpi';

    protected $fillable = [
        'date',
        'channel',
        'location_id',
        'total_orders',
        'online_orders',
        'online_order_share',
        'gmv',
        'aov',
        'orders_within_sla',
        'sla_pct',
        'avg_order_ready_seconds',
        'repeat_customers',
        'repeat_rate',
        'dau',
        'new_users',
        'sessions',
        'sessions_with_order',
        'session_to_order_rate',
        'revenue_app',
        'revenue_kiosk',
        'revenue_cashier',
        'discounted_orders',
        'discount_total',
        'avg_accept_seconds',
        'avg_prepare_seconds',
        'avg_serve_seconds',
        'incorrect_orders',
        'cancelled_orders',
        'refunded_orders',
        'payment_attempts',
        'payment_successes',
        'payment_success_rate',
        'complaints',
        'incidents_resolved',
    ];

    protected $casts = [
        'date'               => 'date',
        'gmv'                => 'decimal:4',
        'aov'                => 'decimal:4',
        'online_order_share' => 'decimal:4',
        'sla_pct'            => 'decimal:4',
        'repeat_rate'        => 'decimal:4',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(AnalyticsLocation::class, 'location_id');
    }
}
