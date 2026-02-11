<?php

namespace Webkul\TochkaPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Webkul\Newsletters\Traits\BelongsToCompany;

class TochkaPaymentHistory extends Model
{
    use HasFactory, BelongsToCompany;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tochka_payment_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'external_order_id',
        'order_id',
        'amount',
        'client_name',
        'client_email',
        'client_phone',
        'payment_url',
        'status',
        'operation_id',
        'consumer_id',
        'payment_link_id',
        'request_data',
        'response_data',
        'webhook_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'request_data' => 'array',
        'response_data' => 'array',
        'webhook_data' => 'array',
    ];

    /**
     * Payment status constants.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the company that owns the payment.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Newsletters\Models\Company::class);
    }

    /**
     * Get the webhooks for the payment.
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(TochkaPaymentWebhookProxy::modelClass(), 'payment_history_id');
    }

    /**
     * Scope a query to only include payments for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Find payment by operation ID.
     */
    public static function findByOperationId(string $operationId): ?self
    {
        return static::where('operation_id', $operationId)->first();
    }
}
