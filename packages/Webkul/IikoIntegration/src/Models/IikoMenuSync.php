<?php

namespace Webkul\IikoIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\IikoIntegration\Contracts\IikoMenuSync as IikoMenuSyncContract;

class IikoMenuSync extends Model implements IikoMenuSyncContract
{
    /**
     * The table associated with the model.
     */
    protected $table = 'iiko_menu_syncs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'organization_id',
        'external_menu_id',
        'menu_data',
        'synced_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'menu_data' => 'array',
        'synced_at' => 'datetime',
    ];
}
