<?php

namespace Webkul\Reporting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsKioskStatus extends Model
{
    protected $table = 'analytics_kiosk_status';

    protected $fillable = [
        'location_id',
        'kiosk_code',
        'status',
        'last_heartbeat_at',
        'uptime_today_seconds',
        'downtime_today_seconds',
    ];

    protected $casts = [
        'last_heartbeat_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(AnalyticsLocation::class, 'location_id');
    }

    public function getUptimePercentAttribute(): float
    {
        $total = $this->uptime_today_seconds + $this->downtime_today_seconds;

        return $total > 0 ? round(($this->uptime_today_seconds / $total) * 100, 2) : 100;
    }
}
