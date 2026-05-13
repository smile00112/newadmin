<?php

namespace Webkul\PushNotification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\PushNotification\Contracts\PushCampaignLog as PushCampaignLogContract;

class PushCampaignLog extends Model implements PushCampaignLogContract
{
    protected $fillable = [
        'campaign_id',
        'customer_id',
        'token',
        'status',
        'error_message',
        'sent_at',
        'opened_at',
    ];

    protected $casts = [
        'sent_at'   => 'datetime',
        'opened_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PushCampaign::class, 'campaign_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass(), 'customer_id');
    }
}
