<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Product\Contracts\ProductIngredientsIncompatibilityTemplate as ProductIngredientsIncompatibilityTemplateContract;

class ProductIngredientsIncompatibilityTemplate extends Model implements ProductIngredientsIncompatibilityTemplateContract
{
    protected $table = 'product_ingredients_incompatibilities_templates';

    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the incompatibilities for the template.
     */
    public function incompatibilities(): HasMany
    {
        return $this->hasMany(ProductIngredientsIncompatibilityProxy::modelClass(), 'template_id');
    }
}

