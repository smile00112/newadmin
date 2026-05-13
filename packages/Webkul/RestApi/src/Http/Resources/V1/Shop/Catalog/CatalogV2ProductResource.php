<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Webkul\Product\Facades\ProductImage;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\Concerns\ProductResourceFields;

class CatalogV2ProductResource extends JsonResource
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
        $categoryImage = $this->getCategoryImage($product) ?? ProductImage::getProductBaseImage($product);

        $minPrice = $productTypeInstance->getMinimalPrice();

        // PRICE_DBG: лог цены для товаров с "матч" в имени
        if (stripos((string) $product->name, 'матч') !== false || (int) $product->id === 151) {
            try {
                $cgId = optional(\Webkul\Customer\Facades\Customer::user())->customer_group_id
                    ?? optional(app(\Webkul\Customer\Repositories\CustomerGroupRepository::class)->findOneByField('code', 'guest'))->id;
                Log::info('PRICE_DBG_CATALOG_V2', [
                    'src'              => 'catalog-v2',
                    'product_id'       => (int) $product->id,
                    'sku'              => $product->sku,
                    'name'             => $product->name,
                    'type'             => $product->type,
                    'min_price'        => $minPrice,
                    'product_price'    => $product->price,
                    'special_price'    => $product->special_price,
                    'channel_id'       => core()->getCurrentChannel()->id,
                    'customer_group_id' => $cgId,
                    'customer_id'      => optional(\Webkul\Customer\Facades\Customer::user())->id,
                    'price_indices'    => $product->relationLoaded('price_indices')
                        ? $product->price_indices->map(fn ($i) => [
                            'channel_id'        => $i->channel_id,
                            'customer_group_id' => $i->customer_group_id,
                            'min_price'         => $i->min_price,
                            'regular_min_price' => $i->regular_min_price ?? null,
                            'max_price'         => $i->max_price ?? null,
                        ])->all()
                        : 'not_loaded',
                ]);
            } catch (\Throwable $e) {
                Log::warning('PRICE_DBG_CATALOG_V2_ERR', ['err' => $e->getMessage()]);
            }
        }

        return [
            'id'                      => $product->id,
            'sku'                     => $product->sku,
            'type'                    => $product->type,
            'name'                    => $product->name,
            'price'                   => core()->convertPrice($minPrice),
            'formatted_price'         => core()->currency($minPrice),
            'short_description'       => $this->cleanHtmlDescription($product->short_description),
            'description'             => $this->cleanHtmlDescription($product->description),
            'category_image'          => $categoryImage,
            'videos'                  => ProductVideoResource::collection($product->videos),
            'show_as_big_in_category' => (bool) ($product->show_as_big_in_category ?? false),
            'in_stock'                => $product->haveSufficientQuantity(1),
            'nutrition'               => $this->getNutritionData($product),
            'weight'                  => $product->weight !== null ? (float) $product->weight : null,
            'volume'                  => $product->volume !== null && $product->volume !== '' ? (float) $product->volume : null,
            'up_sells'                => $this->getRelationIds($product, 'up_sells'),
            'cross_sells'             => $this->getRelationIds($product, 'cross_sells'),
            'drinks'                  => $this->getDrinksIds($product),
            'constructor_options'     => $this->getConstructorOptionsWithProductIds($product),
        ];
    }

    /**
     * Get related product IDs by relation name.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @param  string  $relation
     * @return array
     */
    private function getRelationIds($product, string $relation): array
    {
        if (! $product->relationLoaded($relation)) {
            $product->load($relation);
        }

        return $product->{$relation}
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
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
     * Handles both constructor and configurable_constructor types.
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
                'design'            => $constructor->design,
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
                        'ingredients_incompatibilities' => $group->incompatibilityTemplate
                            ? $group->incompatibilityTemplate->incompatibilities->map(fn ($i) => [
                                'parent_id'  => $i->parent_id,
                                'product_id' => $i->product_id,
                            ])->all()
                            : [],
                        'sale_by_sizes'               => (bool) ($group->sale_by_sizes ?? false),
                        'portion_sizes'               => $this->normalizePortionSizes($group->portion_sizes ?? []),
                        'product_ids'                 => $group->products->pluck('id')->values()->all(),
                    ];
                })->sortBy('sort')->values()->all(),
            ];
        })->values()->all();
    }
}
