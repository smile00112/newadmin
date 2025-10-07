<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Product\Contracts\ProductConstructorGroupProduct as ProductConstructorGroupProductContract;

class ProductConstructorGroupProduct extends Model implements ProductConstructorGroupProductContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_constructor_group_products';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = null;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id',
        'product_id',
        'sort',
        'default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'group_id' => 'integer',
        'product_id' => 'integer',
        'sort' => 'integer',
        'default' => 'boolean',
    ];

    /**
     * Get the group that owns the pivot.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductConstructorGroup::class, 'group_id');
    }

    /**
     * Get the product that owns the pivot.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
