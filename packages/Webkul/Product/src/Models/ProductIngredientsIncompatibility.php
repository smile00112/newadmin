<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Product\Contracts\ProductIngredientsIncompatibility as ProductIngredientsIncompatibilityContract;

class ProductIngredientsIncompatibility extends Model implements ProductIngredientsIncompatibilityContract
{
    protected $table = 'product_ingredients_incompatibilities';

    protected $fillable = [
        'template_id',
        'parent_id',
        'product_id',
    ];

    public $incrementing = false;

    /**
     * Get the template that owns the incompatibility.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProductIngredientsIncompatibilityTemplateProxy::modelClass(), 'template_id');
    }

    /**
     * Get the parent product.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductProxy::modelClass(), 'parent_id');
    }

    /**
     * Get the incompatible product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductProxy::modelClass(), 'product_id');
    }
}

