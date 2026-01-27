<?php

namespace Webkul\IikoIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\IikoIntegration\Contracts\IikoNomenclature as IikoNomenclatureContract;

class IikoNomenclature extends Model implements IikoNomenclatureContract
{
    /**
     * The table associated with the model.
     */
    protected $table = 'iiko_nomenclatures';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'organization_id',
        'nomenclature_data',
        'synced_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'nomenclature_data' => 'array',
        'synced_at'         => 'datetime',
    ];
}
