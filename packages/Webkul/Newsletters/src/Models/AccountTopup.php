<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Webkul\User\Models\Admin;

class AccountTopup extends Model
{
    use HasFactory;

    /**
     * Transaction type constants.
     */
    const TYPE_TOPUP = 'topup';
    const TYPE_DEDUCTION = 'deduction';
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'account_topups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'type',
        'provider_key',
        'provider_payment_id',
        'status',
        'amount',
        'transaction_date',
        'paid_at',
        'payment_url',
        'admin_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * The attributes that have default values.
     *
     * @var array
     */
    protected $attributes = [
        'type' => self::TYPE_TOPUP,
        'status' => self::STATUS_PAID,
    ];

    /**
     * Get the account that owns the topup.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(CompanyAccount::class, 'account_id');
    }

    /**
     * Get the admin who created the topup.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Scope a query to only include topup transactions.
     */
    public function scopeTopups(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_TOPUP);
    }

    /**
     * Scope a query to only include deduction transactions.
     */
    public function scopeDeductions(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_DEDUCTION);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set transaction_date to created_at if not provided
            if (!$model->transaction_date) {
                $model->transaction_date = now();
            }
        });
    }
}

