<?php

namespace Webkul\PushNotification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\PushNotification\Contracts\PushCampaign as PushCampaignContract;

class PushCampaign extends Model implements PushCampaignContract
{
    protected $fillable = [
        'name',
        'title',
        'body',
        'image_url',
        'deep_link',
        'data',
        'segment_filters',
        'status',
        'scheduled_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'opened_count',
        'created_by',
    ];

    protected $casts = [
        'data'             => 'array',
        'segment_filters'  => 'array',
        'scheduled_at'     => 'datetime',
        'total_recipients' => 'integer',
        'sent_count'       => 'integer',
        'delivered_count'  => 'integer',
        'opened_count'     => 'integer',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(PushCampaignLog::class, 'campaign_id');
    }

    /**
     * Conversion rate as percentage (opened / total_recipients * 100).
     */
    public function getConversionRateAttribute(): float
    {
        if ($this->total_recipients === 0) {
            return 0.0;
        }

        return round($this->opened_count / $this->total_recipients * 100, 1);
    }

    /**
     * Delivery rate as percentage (delivered / total_recipients * 100).
     */
    public function getDeliveryRateAttribute(): float
    {
        if ($this->total_recipients === 0) {
            return 0.0;
        }

        return round($this->delivered_count / $this->total_recipients * 100, 1);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Черновик',
            'scheduled' => 'Запланирована',
            'sending'   => 'Отправляется',
            'sent'      => 'Отправлена',
            'failed'    => 'Ошибка',
            default     => $this->status,
        };
    }
}
