<?php

namespace Webkul\IikoIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\IikoIntegration\Contracts\IikoTerminalGroup as IikoTerminalGroupContract;

class IikoTerminalGroup extends Model implements IikoTerminalGroupContract
{
    /**
     * The table associated with the model.
     */
    protected $table = 'iiko_terminal_groups';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'organization_id',
        'iiko_id',
        'name',
        'terminal_group_data',
        'synced_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'terminal_group_data' => 'array',
        'synced_at'           => 'datetime',
    ];
}
