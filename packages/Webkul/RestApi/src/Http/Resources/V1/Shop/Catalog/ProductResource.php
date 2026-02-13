<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Webkul\Product\Facades\ProductImage;
use Webkul\Product\Helpers\BundleOption;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /* assign product */
        $product = $this->product ?? $this;

        /* get type instance - cache it to avoid multiple calls */
        $productTypeInstance = $product->getTypeInstance();

        /* generating resource */
        return [
            /* product's information */
            'id'                 => $product->id,
            'sku'                => $product->sku,
            'type'               => $product->type,
            'name'               => $product->name,
            'price'              => core()->convertPrice($productTypeInstance->getMinimalPrice()),
            'formatted_price'    => core()->currency($productTypeInstance->getMinimalPrice()),
            'short_description'  => $this->cleanHtmlDescription($product->short_description),
            'description'        => $this->cleanHtmlDescription($product->description),
            'images'             => ProductImageResource::collection($product->images),
            'videos'             => ProductVideoResource::collection($product->videos),
            'base_image'         => ProductImage::getProductBaseImage($product),
            'category_image'     => $this->getCategoryImage($product),

            /* product's checks */
            'in_stock'              => $product->haveSufficientQuantity(1),
            'is_saved'              => false,
            'show_quantity_changer' => $this->when(
                $product->type !== 'grouped',
                $productTypeInstance->showQuantityBox()
            ),

            /* product attributes with their options */
            'attributes' => $this->getProductAttributes($product),

            /* nutrition information (КЖБУ) */
            'nutrition' => $this->getNutritionData($product),

            /* product's extra information */
            $this->merge($this->allProductExtraInfo($product, $productTypeInstance)),

            /* special price cases */
            $this->merge($this->specialPriceInfo($product, $productTypeInstance)),

            /* super attributes */
            $this->mergeWhen($productTypeInstance->isComposite(), [
                'super_attributes' => AttributeResource::collection($product->super_attributes),
            ]),

            /* drinks for product */
            $this->mergeWhen(
                in_array($product->type, ['simple', 'constructor', 'configurable', 'grouped', 'bundle']),
                $this->getDrinksInfo($product)
            ),
        ];
    }

    /**
     * Get product attributes with their available options.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getProductAttributes($product)
    {
        $attributes = [];

        $attributeFamily = $product->attribute_family;

        if (!$attributeFamily) {
            return $attributes;
        }

        $customAttributes = $attributeFamily->custom_attributes;

        foreach ($customAttributes as $attribute) {
            // Only include attributes that have options (select, multiselect, checkbox)
            if (!in_array($attribute->type, ['select', 'multiselect', 'checkbox'])) {
                continue;
            }

            if (!$attribute->options || $attribute->options->count() === 0) {
                continue;
            }

            $currentValue = $product->getCustomAttributeValue($attribute);

            $attributeData = [
                'id'            => $attribute->id,
                'code'          => $attribute->code,
                'name'          => $attribute->admin_name ?? $attribute->code,
                'type'          => $attribute->type,
                'current_value' => $currentValue,
                'options'       => $attribute->options->map(function ($option) {
                    return [
                        'id'    => $option->id,
                        'code'  => $option->admin_name ?? $option->id,
                        'label' => $option->label ?? $option->admin_name,
                    ];
                })->values()->toArray(),
            ];

            $attributes[] = $attributeData;
        }

        return $attributes;
    }

    /**
     * Get special price information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @param  \Webkul\Product\Type\AbstractType  $productTypeInstance
     * @return array
     */
    private function specialPriceInfo($product = null, $productTypeInstance = null)
    {
        $product = $product ?? $this->product ?? $this;
        $productTypeInstance = $productTypeInstance ?? $product->getTypeInstance();

        return [
            'special_price'           => $this->when(
                $productTypeInstance->haveDiscount(),
                core()->convertPrice($productTypeInstance->getMinimalPrice())
            ),
            'formatted_special_price' => $this->when(
                $productTypeInstance->haveDiscount(),
                core()->currency($productTypeInstance->getMinimalPrice())
            ),
            'regular_price'           => $this->when(
                $productTypeInstance->haveDiscount(),
                data_get($productTypeInstance->getProductPrices(), 'regular.price')
            ),
            'formatted_regular_price' => $this->when(
                $productTypeInstance->haveDiscount(),
                data_get($productTypeInstance->getProductPrices(), 'regular.formatted_price')
            ),
        ];
    }

    /**
     * Get all product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @param  \Webkul\Product\Type\AbstractType  $productTypeInstance
     * @return array
     */
    private function allProductExtraInfo($product = null, $productTypeInstance = null)
    {
        $product = $product ?? $this->product ?? $this;
        $productTypeInstance = $productTypeInstance ?? $product->getTypeInstance();

        return [
            /* grouped product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Grouped,
                $product->type == 'grouped'
                    ? $this->getGroupedProductInfo($product)
                    : null
            ),

            /* bundle product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Bundle,
                $product->type == 'bundle'
                    ? $this->getBundleProductInfo($product)
                    : null
            ),

            /* configurable product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Configurable,
                $product->type == 'configurable'
                    ? $this->getConfigurableProductInfo($product)
                    : null
            ),

            /* downloadable product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Downloadable,
                $product->type == 'downloadable'
                    ? $this->getDownloadableProductInfo($product)
                    : null
            ),

            /* booking product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Booking,
                $product->type == 'booking'
                    ? $this->getBookingProductInfo($product)
                    : null
            ),

            /* constructor product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Constructor,
                $product->type == 'constructor'
                    ? $this->getConstructorProductInfo($product)
                    : null
            ),
        ];
    }

    /**
     * Get grouped product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getGroupedProductInfo($product)
    {
        return [
            'grouped_products' => $product->grouped_products->map(function ($groupedProduct) {
                $associatedProduct = $groupedProduct->associated_product;

                $data = $associatedProduct->toArray();

                return array_merge($data, [
                    'qty'                   => $groupedProduct->qty,
                    'isSaleable'            => $associatedProduct->getTypeInstance()->isSaleable(),
                    'formatted_price'       => $associatedProduct->getTypeInstance()->getPriceHtml(),
                    'show_quantity_changer' => $associatedProduct->getTypeInstance()->showQuantityBox(),
                ]);
            }),
        ];
    }

    /**
     * Get bundle product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getBundleProductInfo($product)
    {
        return [
            'bundle_options' => app(BundleOption::class)->getBundleConfig($product),
        ];
    }

    /**
     * Get configurable product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getConfigurableProductInfo($product)
    {
        return [
            'variants' => $product->variants,
        ];
    }

    /**
     * Get downloadable product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getDownloadableProductInfo($product)
    {
        return [
            'downloadable_links' => $product->downloadable_links->map(function ($downloadableLink) {
                $data = $downloadableLink->toArray();

                if (isset($data['sample_file'])) {
                    $data['price'] = core()->currency($downloadableLink->price);
                    $data['sample_download_url'] = route('shop.downloadable.download_sample', ['type' => 'link', 'id' => $downloadableLink['id']]);
                }

                return $data;
            }),

            'downloadable_samples' => $product->downloadable_samples->map(function ($downloadableSample) {
                $sample = $downloadableSample->toArray();
                $data = $sample;
                $data['download_url'] = route('shop.downloadable.download_sample', ['type' => 'sample', 'id' => $sample['id']]);

                return $data;
            }),
        ];
    }

    /**
     * Get booking product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getBookingProductInfo($product)
    {
        return [
            'booking' => $product->booking_products,
        ];
    }

    /**
     * Get constructor product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getConstructorProductInfo($product)
    {
        // Check if constructor is already loaded (via eager loading)
        if (!$product->relationLoaded('constructor')) {
            // Only load if not already loaded
            $product->load('constructor.groups.products.images');
        }

        // Return empty array if no constructor exists
        if ($product->constructor->isEmpty()) {
            return [
                'constructor_options' => [],
            ];
        }

        $constructorOptions = $product->constructor->map(function ($constructor) {
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
                        'opened_by_default'          => $group->opened_by_default,
                        'zero_price'                  => $group->zero_price,
                        'required'                    => $group->required,
                        'hidden'                      => $group->hidden,
                        'sort'                        => $group->sort,
                        'double_portions'             => $group->double_portions,
                        'half_portions'               => $group->half_portions,
                        'ingredients_incompatibilities_id' => $group->ingredients_incompatibilities_id,
                        'products'                    => $group->products->map(function ($groupProduct) {
                            $productTypeInstance = $groupProduct->getTypeInstance();

                            return [
                                'id'             => $groupProduct->id,
                                'sku'            => $groupProduct->sku,
                                'name'           => $groupProduct->name,
                                'price'          => core()->convertPrice($productTypeInstance->getMinimalPrice()),
                                'formatted_price' => core()->currency($productTypeInstance->getMinimalPrice()),
                                'in_stock'       => $groupProduct->haveSufficientQuantity(1),
                                'sort'           => $groupProduct->pivot->sort ?? 0,
                                'default'        => (bool) ($groupProduct->pivot->default ?? false),
                                'base_image'     => ProductImage::getProductBaseImage($groupProduct),
                                'description'    => $this->cleanHtmlDescription($groupProduct->description),
                                'nutrition'      => $this->getNutritionData($groupProduct),
                            ];
                        })->sortBy('sort')->values(),
                    ];
                })->sortBy('sort')->values(),
            ];
        });

        return [
            'constructor_options' => $constructorOptions,
        ];
    }

    /**
     * Get category image URL.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array|null
     */
    private function getCategoryImage($product)
    {
        $categoryImagePath = $product->category_image;

        if (empty($categoryImagePath)) {
            return null;
        }

        return [
            'path'               => $categoryImagePath,
            'url'                => Storage::url($categoryImagePath),
            'original_image_url' => Storage::url($categoryImagePath),
            'small_image_url'    => url('cache/small/'.$categoryImagePath),
            'medium_image_url'   => url('cache/medium/'.$categoryImagePath),
            'large_image_url'    => url('cache/large/'.$categoryImagePath),
        ];
    }

    /**
     * Get drinks information for the product.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getDrinksInfo($product)
    {
        // Check if drinks are already loaded (via eager loading)
        if (!$product->relationLoaded('drinks')) {
            // Only load if not already loaded
            $product->load(['drinks' => function ($query) {
                $query->with('images')
                    ->orderByPivot('sort', 'asc');
            }]);
        }

        // Return empty array if no drinks exist
        if ($product->drinks->isEmpty()) {
            return [
                'drinks' => [],
            ];
        }

        $drinks = $product->drinks->map(function ($drink) {
            $drinkTypeInstance = $drink->getTypeInstance();

            return [
                'id'                 => $drink->id,
                'sku'                => $drink->sku,
                'name'               => $drink->name,
                'price'              => core()->convertPrice($drinkTypeInstance->getMinimalPrice()),
                'formatted_price'    => core()->currency($drinkTypeInstance->getMinimalPrice()),
                'in_stock'           => $drink->haveSufficientQuantity(1),
                'sort'               => $drink->pivot->sort ?? 0,
                'default'            => (bool) ($drink->pivot->default ?? false),
                'base_image'         => ProductImage::getProductBaseImage($drink),
                'images'             => ProductImageResource::collection($drink->images),
                'description'        => $this->cleanHtmlDescription($drink->description),
                'nutrition'          => $this->getNutritionData($drink),
            ];
        });

        return [
            'drinks' => $drinks->values()->all(),
        ];
    }

    /**
     * Get nutrition information (КЖБУ).
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getNutritionData($product)
    {
        $nutrition = [
            'calories' => null,
            'proteins' => null,
            'fats'     => null,
            'carbs'    => null,
        ];

        // Получаем значения КЖБУ из атрибутов товара
        $nutritionCodes = ['calories', 'proteins', 'fats', 'carbs'];
        
        foreach ($nutritionCodes as $code) {
            $value = $product->{$code};
            
            if ($value !== null && $value !== '') {
                // Преобразуем в число, если это строка
                $nutrition[$code] = is_numeric($value) ? (float) $value : $value;
            }
        }

        // Возвращаем null, если все значения пустые
        if (empty(array_filter($nutrition))) {
            return null;
        }

        return $nutrition;
    }

    /**
     * Clean HTML tags from description text.
     *
     * @param  string|null  $description
     * @return string|null
     */
    private function cleanHtmlDescription($description)
    {
        if (empty($description)) {
            return null;
        }

        // Remove HTML tags and decode HTML entities
        $cleaned = strip_tags($description);
        $cleaned = html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Trim whitespace
        $cleaned = trim($cleaned);
        
        return !empty($cleaned) ? $cleaned : null;
    }
}
