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
        $product = $this->resource;
        $productTypeInstance = $product->getTypeInstance();
        $minimalPrice = $productTypeInstance->getMinimalPrice();
        $hasDiscount = $productTypeInstance->haveDiscount();

        $filteredSuperAttributes = $productTypeInstance->isComposite()
            ? $this->getFilteredSuperAttributes($product)
            : null;

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
                'images'             => ProductImageResource::collection($product->images ?? collect()),
                'videos'             => ProductVideoResource::collection($product->videos ?? collect()),
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
                    AttributeResource::collection($filteredSuperAttributes ?? collect())
                ),
                'up_sells'           => $product->relationLoaded('up_sells')
                    ? $product->up_sells->pluck('id')->values()->all()
                    : [],
                'cross_sells'        => $product->relationLoaded('cross_sells')
                    ? $product->cross_sells->pluck('id')->values()->all()
                    : [],
                'drinks'             => $this->getDrinksWithDefault($product),
                'variants'           => $this->getVariantsData(
                    $product,
                    $filteredSuperAttributes?->pluck('id')->toArray()
                ),
                'constructor_options' => $this->getConstructorOptionsWithProductIds($product),
            ],
            $this->specialPriceInfo($product, $productTypeInstance, $minimalPrice, $hasDiscount)
        );
    }

    /**
     * Get super attributes filtered by existing variants (configurable and configurable_constructor).
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return \Illuminate\Support\Collection
     */
    private function getFilteredSuperAttributes($product)
    {
        if (! $product->relationLoaded('super_attributes') || $product->super_attributes === null) {
            return collect();
        }

        if (! in_array($product->type, ['configurable', 'configurable_constructor']) || ! $product->relationLoaded('variants')) {
            return $product->super_attributes ?? collect();
        }

        $variantAttributeValues = $product->variants
            ->flatMap(fn ($variant) => $variant->attribute_values ?? collect());

        $variantAttributeIds = $variantAttributeValues->pluck('attribute_id')->unique()->all();

        $variantOptionIdsByAttribute = $variantAttributeValues
            ->filter(fn ($av) => $av->attribute_id)
            ->groupBy('attribute_id')
            ->map(function ($group) {
                $optionIds = collect();
                foreach ($group as $av) {
                    if (isset($av->integer_value) && $av->integer_value) {
                        $optionIds->push((int) $av->integer_value);
                    }
                    if (isset($av->text_value) && $av->text_value) {
                        $ids = is_numeric($av->text_value)
                            ? [(int) $av->text_value]
                            : array_map('intval', array_filter(explode(',', $av->text_value)));
                        $optionIds = $optionIds->merge($ids);
                    }
                }

                return $optionIds->unique()->values()->all();
            });

        return $product->super_attributes->filter(
            fn ($attr) => in_array($attr->id, $variantAttributeIds)
        )->map(function ($attr) use ($variantOptionIdsByAttribute) {
            $optionIds = $variantOptionIdsByAttribute[$attr->id] ?? [];
            $filteredOptions = $attr->relationLoaded('options')
                ? $attr->options->filter(fn ($opt) => in_array((int) $opt->id, $optionIds, true))->values()
                : collect();
            $attr->setRelation('options', $filteredOptions);

            return $attr;
        })->values();
    }

    /**
     * Get variants data for configurable and configurable_constructor products.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @param  array<int>|null  $superAttributeIds  Filtered super attribute IDs (when provided, used for variant attribute_values)
     * @return array
     */
    private function getVariantsData($product, ?array $superAttributeIds = null): array
    {
        if (! in_array($product->type, ['configurable', 'configurable_constructor'])) {
            return [];
        }

        if (! $product->relationLoaded('variants')) {
            $product->load('variants.attribute_values');
        }

        $superAttributeIds = $superAttributeIds ?? ($product->relationLoaded('super_attributes')
            ? $product->super_attributes->pluck('id')->toArray()
            : []);

        return $product->variants->map(function ($variant) use ($superAttributeIds) {
            $arr = $variant->toArray();

            if (
                isset($arr['attribute_values'])
                && is_array($arr['attribute_values'])
                && ! empty($superAttributeIds)
            ) {
                $arr['attribute_values'] = array_values(array_filter(
                    $arr['attribute_values'],
                    fn ($av) => isset($av['attribute_id']) && in_array($av['attribute_id'], $superAttributeIds, true)
                ));
            }

            $variantTypeInstance = $variant->getTypeInstance();
            $variantMinPrice = $variantTypeInstance->getMinimalPrice();

            return array_merge($arr, [
                'price'           => core()->convertPrice($variantMinPrice),
                'formatted_price' => core()->currency($variantMinPrice),
                'in_stock'        => $variant->haveSufficientQuantity(1),
                'nutrition'       => $this->getNutritionData($variant),
                'weight'          => $variant->weight !== null ? (float) $variant->weight : null,
                'volume'          => $variant->volume !== null && $variant->volume !== '' ? (float) $variant->volume : null,
            ]);
        })->values()->all();
    }

    /**
     * Get drinks as array of objects with ID and default flag.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getDrinksWithDefault($product): array
    {
        if (! $product->relationLoaded('drinks')) {
            return [];
        }

        if ($product->drinks->isEmpty()) {
            return [];
        }

        $sortedDrinks = $product->drinks
            ->sortBy(fn ($drink) => $drink->pivot->sort ?? 0)
            ->values();

        return $sortedDrinks->map(function ($drink) {
            return [
                'id'      => $drink->id,
                'default' => (bool) ($drink->pivot->default ?? false),
            ];
        })->values()->all();
    }

    /**
     * Get constructor options with group products as array of IDs.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getConstructorOptionsWithProductIds($product): array
    {
        if (! in_array($product->type, ['constructor', 'configurable_constructor'])) {
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
                        'products'                    => ($group->products ?? collect())
                            ->sortBy(fn ($p) => $p->pivot->sort ?? 0)
                            ->values()
                            ->map(function ($groupProduct) {
                                return [
                                    'id'      => $groupProduct->id,
                                    'default' => (bool) ($groupProduct->pivot->default ?? false),
                                ];
                            })
                            ->all(),
                    ];
                })->sortBy('sort')->values()->all(),
            ];
        })->values()->all();
    }
}
