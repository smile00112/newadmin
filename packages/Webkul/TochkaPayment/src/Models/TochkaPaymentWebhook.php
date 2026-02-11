<?php

namespace Webkul\TochkaPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Webkul\Newsletters\Traits\BelongsToCompany;

class TochkaPaymentWebhook extends Model
{
    use HasFactory, BelongsToCompany;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tochka_payment_webhooks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'payment_history_id',
        'webhook_type',
        'raw_payload',
        'decoded_data',
        'status',
        'processed_at',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'decoded_data' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Webhook status constants.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';

    /**
     * Webhook type constants.
     */
    const TYPE_ACQUIRING_INTERNET_PAYMENT = 'acquiringInternetPayment';
    const TYPE_INCOMING_PAYMENT = 'incomingPayment';
    const TYPE_OUTGOING_PAYMENT = 'outgoingPayment';
    const TYPE_INCOMING_SBP_PAYMENT = 'incomingSbpPayment';
    const TYPE_INCOMING_SBP_B2B_PAYMENT = 'incomingSbpB2BPayment';

    /**
     * Get the company that owns the webhook.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Newsletters\Models\Company::class);
    }

    /**
     * Get the payment history associated with the webhook.
     */
    public function paymentHistory(): BelongsTo
    {
        return $this->belongsTo(TochkaPaymentHistoryProxy::modelClass(), 'payment_history_id');
    }

    /**
     * Scope a query to only include webhooks for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to filter by webhook type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('webhook_type', $type);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Mark webhook as processed.
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark webhook as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'processed_at' => now(),
        ]);
    }
}
