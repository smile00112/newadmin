<?php

namespace Webkul\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Inventory\Contracts\PickupPoint as PickupPointContract;

class PickupPoint extends Model implements PickupPointContract
{
    use HasFactory;

    protected $guarded = ['_token'];

    /**
     * Get the inventory source that owns the pickup point.
     */
    public function inventory_source(): BelongsTo
    {
        return $this->belongsTo(InventorySourceProxy::modelClass());
    }
}
