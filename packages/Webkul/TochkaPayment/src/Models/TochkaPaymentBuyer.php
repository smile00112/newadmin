<?php

namespace Webkul\TochkaPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Webkul\User\Models\Admin;
use Webkul\Newsletters\Traits\BelongsToCompany;
use Webkul\TochkaPayment\Contracts\TochkaPaymentBuyer as TochkaPaymentBuyerContract;

class TochkaPaymentBuyer extends Model implements TochkaPaymentBuyerContract
{
    use HasFactory, BelongsToCompany;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tochka_payment_buyers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'owner_id',
        'client_email',
        'client_name',
        'client_phone',
        'consumer_id',
    ];

    /**
     * Get the company that owns the buyer.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Newsletters\Models\Company::class);
    }

    /**
     * Get owner admin linked to buyer.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'owner_id');
    }

    /**
     * Scope a query to only include buyers for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
