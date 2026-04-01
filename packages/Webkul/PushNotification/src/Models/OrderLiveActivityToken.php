<?php

namespace Webkul\PushNotification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\PushNotification\Contracts\OrderLiveActivityToken as OrderLiveActivityTokenContract;
use Webkul\Sales\Models\OrderProxy;

class OrderLiveActivityToken extends Model implements OrderLiveActivityTokenContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_live_activity_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'order_id',
        'customer_id',
        'order_increment_id',
        'push_token',
        'last_apns_timestamp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_apns_timestamp' => 'integer',
    ];

    /**
     * Get the order associated with this token.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderProxy::modelClass());
    }

    /**
     * Get the customer associated with this token.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }
}
