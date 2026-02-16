<?php

namespace Webkul\TochkaPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Webkul\Newsletters\Traits\BelongsToCompany;
use Webkul\TochkaPayment\Contracts\TochkaPaymentSettings as TochkaPaymentSettingsContract;

class TochkaPaymentSettings extends Model implements TochkaPaymentSettingsContract
{
    use HasFactory, BelongsToCompany;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tochka_payment_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'client_id',
        'jwt_token',
        'api_base_url',
        'webhook_url',
        'customer_code',
        'merchant_id',
        'consumer_id',
        'payment_mode',
        'save_card',
        'pre_authorization',
        'ttl',
        'min_amount',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payment_mode' => 'array',
        'save_card' => 'boolean',
        'pre_authorization' => 'boolean',
        'is_active' => 'boolean',
        'ttl' => 'integer',
        'min_amount' => 'decimal:2',
    ];

    /**
     * Get the company that owns the settings.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Newsletters\Models\Company::class);
    }

    /**
     * Scope a query to only include settings for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Get settings for current company or create default.
     *
     * @return self
     */
    public static function getForCurrentCompany(): ?self
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || !$admin->company_id) {
            return null;
        }

        return static::firstOrCreate(
            ['company_id' => $admin->company_id],
            [
                'is_active' => false,
                'ttl' => 10080,
                'min_amount' => 1.00,
                'payment_mode' => ['sbp', 'card'],
            ]
        );
    }
}
