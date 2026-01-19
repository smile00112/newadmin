<?php

namespace Webkul\IikoIntegration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\IikoIntegration\Contracts\IikoOrderSync as IikoOrderSyncContract;
use Webkul\Sales\Models\Order;

class IikoOrderSync extends Model implements IikoOrderSyncContract
{
    /**
     * The table associated with the model.
     */
    protected $table = 'iiko_order_syncs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'iiko_order_id',
        'iiko_order_number',
        'sync_status',
        'sync_data',
        'synced_at',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'sync_data'  => 'array',
        'synced_at'  => 'datetime',
    ];

    /**
     * Sync status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SYNCED = 'synced';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the order that owns the sync.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
