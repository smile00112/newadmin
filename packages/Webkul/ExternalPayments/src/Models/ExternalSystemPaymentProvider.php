<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalSystemPaymentProvider extends Model
{
    protected $table = 'external_system_payment_providers';

    protected $fillable = [
        'external_system_id',
        'payment_provider',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function externalSystem(): BelongsTo
    {
        return $this->belongsTo(ExternalSystem::class, 'external_system_id');
    }
}
