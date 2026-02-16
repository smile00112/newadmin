<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExternalSystem extends Model
{
    protected $table = 'external_systems';

    protected $fillable = [
        'name',
        'api_token',
        'webhook_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_token',
    ];

    public function paymentProviders(): HasMany
    {
        return $this->hasMany(ExternalSystemPaymentProvider::class, 'external_system_id');
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(ExternalPaymentRequest::class, 'external_system_id');
    }

    public function getDefaultProviderAttribute(): ?string
    {
        $default = $this->paymentProviders()->where('is_default', true)->first();

        return $default?->payment_provider;
    }
}
