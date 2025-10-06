<?php

namespace Webkul\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Product\Contracts\ProductConstructor as ProductConstructorContract;

class ProductConstructor extends Model implements ProductConstructorContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_constructor';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'visible',
        'required',
        'combo',
        'discount',
        'design',
        'discount_type',
        'discount_value',
        'min_selected_sum',
        'parent_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'visible' => 'boolean',
        'required' => 'boolean',
        'combo' => 'boolean',
        'discount' => 'boolean',
        'discount_value' => 'integer',
        'min_selected_sum' => 'integer',
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
     * Get the product that owns the constructor.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    /**
     * Get the constructor groups for the constructor.
     */
    public function groups(): HasMany
    {
        return $this->hasMany(ProductConstructorGroup::class, 'parent_id');
    }

    /**
     * Get the design options.
     *
     * @return array
     */
    public static function getDesignOptions(): array
    {
        return [
            'line' => 'В строку',
            'category' => 'Категория - товар',
            'table' => 'Таблица',
        ];
    }

    /**
     * Get the discount type options.
     *
     * @return array
     */
    public static function getDiscountTypeOptions(): array
    {
        return [
            null => 'Нет',
            'percent' => 'Процентная',
            'fixed' => 'Фиксированная',
        ];
    }
}
