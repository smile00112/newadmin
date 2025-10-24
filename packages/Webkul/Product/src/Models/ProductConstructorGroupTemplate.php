<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\Product\Contracts\ProductConstructorGroupTemplate as ProductConstructorGroupTemplateContract;

class ProductConstructorGroupTemplate extends Model implements ProductConstructorGroupTemplateContract
{
    protected $table = 'product_constructor_group_templates';

    protected $fillable = [
        'template_name',
        'name',
        'field_type',
        'checked_type',
        'quantity_min',
        'quantity_max',
        'show_title',
        'opened_by_default',
        'zero_price',
        'required',
        'hidden',
        'double_portions',
        'half_portions',
        'sort',
        'ingredients_incompatibilities_id',
    ];

    protected $casts = [
        'show_title'         => 'boolean',
        'opened_by_default'  => 'boolean',
        'zero_price'         => 'boolean',
        'required'           => 'boolean',
        'hidden'             => 'boolean',
        'double_portions'    => 'boolean',
        'half_portions'      => 'boolean',
        'quantity_min'       => 'integer',
        'quantity_max'       => 'integer',
        'sort'               => 'integer',
    ];

    /**
     * Get the incompatibility template.
     */
    public function incompatibilityTemplate(): BelongsTo
    {
        return $this->belongsTo(ProductIngredientsIncompatibilityTemplateProxy::modelClass(), 'ingredients_incompatibilities_id');
    }

    /**
     * The products that belong to the template.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductProxy::modelClass(),
            'product_constructor_group_templates_products',
            'group_id',
            'product_id'
        )->withPivot('sort', 'default');
    }
}

