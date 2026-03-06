<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Facades\ProductImage;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\Concerns\ProductResourceFields;

class NomenclatureProductResource extends JsonResource
{
    use ProductResourceFields;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $product = $this->product ?? $this;
        $productTypeInstance = $product->getTypeInstance();
        $minimalPrice = $productTypeInstance->getMinimalPrice();
        $hasDiscount = $productTypeInstance->haveDiscount();

        return array_merge(
            [
                'id'                 => $product->id,
                'sku'                => $product->sku,
                'type'               => $product->type,
                'name'               => $product->name,
                'price'              => core()->convertPrice($minimalPrice),
                'formatted_price'    => core()->currency($minimalPrice),
                'short_description'  => $this->cleanHtmlDescription($product->short_description),
                'description'        => $this->cleanHtmlDescription($product->description),
                'images'             => ProductImageResource::collection($product->images),
                'videos'             => ProductVideoResource::collection($product->videos),
                'base_image'         => ProductImage::getProductBaseImage($product),
                'category_image'     => $this->getCategoryImage($product),
                'show_as_big_in_category' => (bool) ($product->show_as_big_in_category ?? false),
                'is_half_portion'            => (bool) ($product->is_half_portion ?? false),
                'half_portion_pair_product_id' => $product->half_portion_pair_product_id,
                'in_stock'           => $product->haveSufficientQuantity(1),
                'show_quantity_changer' => $this->when(
                    $product->type !== 'grouped',
                    $productTypeInstance->showQuantityBox()
                ),
                'attributes'         => $this->getProductAttributes($product),
                'nutrition'          => $this->getNutritionData($product),
                'super_attributes'   => $this->when(
                    $productTypeInstance->isComposite(),
                    AttributeResource::collection($product->super_attributes)
                ),
                'up_sells'           => $product->relationLoaded('up_sells')
                    ? $product->up_sells->pluck('id')->values()->all()
                    : [],
                'cross_sells'        => $product->relationLoaded('cross_sells')
                    ? $product->cross_sells->pluck('id')->values()->all()
                    : [],
                'drinks'             => $this->getDrinksIds($product),
                'constructor_options' => $this->getConstructorOptionsWithProductIds($product),
            ],
            $this->specialPriceInfo($product, $productTypeInstance, $minimalPrice, $hasDiscount)
        );
    }

    /**
     * Get drinks as array of product IDs.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getDrinksIds($product): array
    {
        if (! $product->relationLoaded('drinks')) {
            return [];
        }

        return $product->drinks->pluck('id')->values()->all();
    }

    /**
     * Get constructor options with group products as array of IDs.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getConstructorOptionsWithProductIds($product): array
    {
        if ($product->type !== 'constructor') {
            return [];
        }

        if (! $product->relationLoaded('constructor')) {
            $product->load('constructor.groups.products');
        }

        if ($product->constructor->isEmpty()) {
            return [];
        }

        return $product->constructor->map(function ($constructor) {
            return [
                'id'               => $constructor->id,
                'visible'          => $constructor->visible,
                'required'         => $constructor->required,
                'combo'            => $constructor->combo,
                'discount'         => $constructor->discount,
                'design'           => $constructor->design,
                'discount_type'    => $constructor->discount_type,
                'discount_value'   => $constructor->discount_value,
                'min_selected_sum' => $constructor->min_selected_sum,
                'groups'           => $constructor->groups->map(function ($group) {
                    return [
                        'id'                          => $group->id,
                        'name'                        => $group->name,
                        'field_type'                  => $group->field_type,
                        'checked_type'                => $group->checked_type,
                        'quantity_min'                => $group->quantity_min,
                        'quantity_max'                => $group->quantity_max,
                        'show_title'                  => $group->show_title,
                        'opened_by_default'           => $group->opened_by_default,
                        'zero_price'                  => $group->zero_price,
                        'required'                    => $group->required,
                        'hidden'                      => $group->hidden,
                        'sort'                        => $group->sort,
                        'double_portions'             => $group->double_portions,
                        'half_portions'               => $group->half_portions,
                        'ingredients_incompatibilities_id' => $group->ingredients_incompatibilities_id,
                        'sale_by_sizes'               => (bool) ($group->sale_by_sizes ?? false),
                        'portion_sizes'               => $this->normalizePortionSizes($group->portion_sizes ?? []),
                        'product_ids'                 => $group->products->pluck('id')->values()->all(),
                    ];
                })->sortBy('sort')->values()->all(),
            ];
        })->values()->all();
    }
}
