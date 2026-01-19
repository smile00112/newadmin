<?php

namespace Webkul\IikoIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\IikoIntegration\Contracts\IikoSyncLog as IikoSyncLogContract;

class IikoSyncLog extends Model implements IikoSyncLogContract
{
    /**
     * The table associated with the model.
     */
    protected $table = 'iiko_sync_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'sync_type',
        'entity_id',
        'status',
        'request_data',
        'response_data',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'request_data'  => 'array',
        'response_data' => 'array',
    ];

    /**
     * Sync type constants.
     */
    public const TYPE_ORDER = 'order';
    public const TYPE_MENU = 'menu';
    public const TYPE_ORGANIZATION = 'organization';
    public const TYPE_WEBHOOK = 'webhook';
    public const TYPE_API_REQUEST = 'api_request';

    /**
     * Status constants.
     */
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';
    public const STATUS_PENDING = 'pending';
}
