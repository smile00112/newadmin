<?php

namespace Webkul\TochkaPayment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webkul\TochkaPayment\Contracts\TochkaPaymentHistory as TochkaPaymentHistoryContract;

class TochkaPaymentHistory extends Model implements TochkaPaymentHistoryContract
{
    use HasFactory;

    protected $table = 'tochka_payment_histories';

    protected $fillable = [
        'external_order_id',
        'order_id',
        'amount',
        'client_name',
        'client_email',
        'client_phone',
        'payment_url',
        'transaction_id',
        'status',
        'request_data',
        'callback_data',
        'webhook_sent',
        'webhook_response',
        'webhook_attempts',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'request_data' => 'array',
        'callback_data' => 'array',
        'webhook_sent' => 'boolean',
        'webhook_attempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the status label.
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => __('tochka-payment::app.admin.payment-history.status.pending'),
            'paid' => __('tochka-payment::app.admin.payment-history.status.paid'),
            'failed' => __('tochka-payment::app.admin.payment-history.status.failed'),
            'cancelled' => __('tochka-payment::app.admin.payment-history.status.cancelled'),
            default => $this->status,
        };
    }

    /**
     * Check if payment is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is paid.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if payment is failed.
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is cancelled.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
