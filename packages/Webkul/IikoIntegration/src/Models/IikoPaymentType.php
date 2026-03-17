<?php

namespace Webkul\IikoIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\IikoIntegration\Contracts\IikoPaymentType as IikoPaymentTypeContract;

class IikoPaymentType extends Model implements IikoPaymentTypeContract
{
    /**
     * The table associated with the model.
     */
    protected $table = 'iiko_payment_types';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'organization_id',
        'iiko_id',
        'name',
        'kind',
        'payment_method_code',
        'is_active',
        'payment_type_data',
        'synced_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'payment_type_data' => 'array',
        'is_active'         => 'boolean',
        'synced_at'         => 'datetime',
    ];
}
