<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalPaymentRequest extends Model
{
    protected $table = 'external_payment_requests';

    protected $fillable = [
        'external_system_id',
        'payment_provider',
        'provider_payment_id',
        'provider_order_id',
        'external_order_id',
        'status',
        'webhook_sent',
        'webhook_sent_at',
    ];

    protected $casts = [
        'webhook_sent'   => 'boolean',
        'webhook_sent_at' => 'datetime',
    ];

    public function externalSystem(): BelongsTo
    {
        return $this->belongsTo(ExternalSystem::class, 'external_system_id');
    }
}
