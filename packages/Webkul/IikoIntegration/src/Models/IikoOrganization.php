<?php

namespace Webkul\IikoIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\IikoIntegration\Contracts\IikoOrganization as IikoOrganizationContract;

class IikoOrganization extends Model implements IikoOrganizationContract
{
    /**
     * The table associated with the model.
     */
    protected $table = 'iiko_organizations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'iiko_id',
        'name',
        'organization_data',
        'synced_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'organization_data' => 'array',
        'synced_at'         => 'datetime',
    ];
}
