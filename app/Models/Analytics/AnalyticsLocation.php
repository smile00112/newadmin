<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class AnalyticsLocation extends Model
{
    protected $fillable = [
        'name',
        'code',
        'zone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kiosks()
    {
        return $this->hasMany(AnalyticsKioskStatus::class, 'location_id');
    }

    public function dailyKpis()
    {
        return $this->hasMany(AnalyticsDailyKpi::class, 'location_id');
    }
}
