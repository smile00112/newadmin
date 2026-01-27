<?php

namespace Webkul\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Inventory\Contracts\InventorySource as InventorySourceContract;
use Webkul\Inventory\Database\Factories\InventorySourceFactory;

class InventorySource extends Model implements InventorySourceContract
{
    use HasFactory;

    protected $guarded = ['_token'];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return InventorySourceFactory::new();
    }

    /**
     * Get the pickup points that belong to the inventory source.
     */
    public function pickup_points(): HasMany
    {
        return $this->hasMany(PickupPointProxy::modelClass());
    }
}
