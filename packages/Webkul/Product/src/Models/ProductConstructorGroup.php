<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\Product\Contracts\ProductConstructorGroup as ProductConstructorGroupContract;

class ProductConstructorGroup extends Model implements ProductConstructorGroupContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_constructor_group';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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
        'sort',
        'parent_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity_min' => 'integer',
        'quantity_max' => 'integer',
        'show_title' => 'boolean',
        'opened_by_default' => 'boolean',
        'zero_price' => 'boolean',
        'required' => 'boolean',
        'hidden' => 'boolean',
        'sort' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get the constructor that owns the group.
     */
    public function constructor(): BelongsTo
    {
        return $this->belongsTo(ProductConstructor::class, 'parent_id');
    }

    /**
     * Get the products for the group.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_constructor_group_products',
            'group_id',
            'product_id'
        )->withPivot('sort', 'default');
    }

    /**
     * Get the field type options.
     *
     * @return array
     */
    public static function getFieldTypeOptions(): array
    {
        return [
            'checkbox' => 'Чекбокс',
            'radio' => 'Радио',
            'list' => 'Список',
        ];
    }

    /**
     * Get the checked type options.
     *
     * @return array
     */
    public static function getCheckedTypeOptions(): array
    {
        return [
            'once' => 'Только один продукт',
            'multiple' => 'Несколько продуктов',
        ];
    }
}
