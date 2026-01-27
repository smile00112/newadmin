<?php

namespace Webkul\IikoIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\IikoIntegration\Contracts\IikoPromotion as IikoPromotionContract;

class IikoPromotion extends Model implements IikoPromotionContract
{
    /**
     * The table associated with the model.
     */
    protected $table = 'iiko_promotions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'organization_id',
        'iiko_id',
        'name',
        'description',
        'is_active',
        'promotion_data',
        'synced_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'promotion_data' => 'array',
        'is_active'      => 'boolean',
        'synced_at'      => 'datetime',
    ];
}
