<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Catalog\Concerns;

use Illuminate\Support\Facades\Storage;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Product\Facades\ProductImage;

trait ProductResourceFields
{
    /**
     * Get product attributes with their available options.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    protected function getProductAttributes($product)
    {
        $attributes = [];

        $attributeFamily = $product->attribute_family;

        if (! $attributeFamily) {
            return $attributes;
        }

        $customAttributes = core()->getSingletonInstance(AttributeRepository::class)
            ->getFamilyAttributes($attributeFamily);

        $selectAttributes = $customAttributes
            ->filter(fn ($a) => in_array($a->type, ['select', 'multiselect', 'checkbox']));

        $selectAttributes->loadMissing('options.translations');

        foreach ($selectAttributes as $attribute) {

            if (! $attribute->options || $attribute->options->count() === 0) {
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
                        'id'           => $option->id,
                        'admin_name'   => $option->admin_name,
                        'label'        => $option->label ?? $option->admin_name,
                        'swatch_value' => $option->swatch_value,
                    ];
                })->values()->toArray(),
            ];

            $attributes[] = $attributeData;
        }

        return $attributes;
    }

    /**
     * Get nutrition information (КЖБУ).
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array|null
     */
    protected function getNutritionData($product)
    {
        $nutrition = [
            'calories' => null,
            'proteins' => null,
            'fats'     => null,
            'carbs'    => null,
        ];

        $nutritionCodes = ['calories', 'proteins', 'fats', 'carbs'];

        foreach ($nutritionCodes as $code) {
            $value = $product->{$code};

            if ($value !== null && $value !== '') {
                $nutrition[$code] = is_numeric($value) ? (float) $value : $value;
            }
        }

        if (empty(array_filter($nutrition))) {
            return null;
        }

        return $nutrition;
    }

    /**
     * Get category image URL.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array|null
     */
    protected function getCategoryImage($product)
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
     * Clean HTML tags from description text.
     *
     * @param  string|null  $description
     * @return string|null
     */
    protected function cleanHtmlDescription($description)
    {
        if (empty($description)) {
            return null;
        }

        $cleaned = strip_tags($description);
        $cleaned = html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cleaned = trim($cleaned);

        return ! empty($cleaned) ? $cleaned : null;
    }

    /**
     * Get special price information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @param  \Webkul\Product\Type\AbstractType  $productTypeInstance
     * @param  mixed  $minimalPrice  Pre-computed minimal price
     * @param  bool  $hasDiscount   Pre-computed discount flag
     * @return array
     */
    protected function specialPriceInfo($product, $productTypeInstance, $minimalPrice, bool $hasDiscount): array
    {
        if (! $hasDiscount) {
            return [];
        }

        $productPrices = $productTypeInstance->getProductPrices();

        return [
            'special_price'           => core()->convertPrice($minimalPrice),
            'formatted_special_price' => core()->currency($minimalPrice),
            'regular_price'           => data_get($productPrices, 'regular.price'),
            'formatted_regular_price' => data_get($productPrices, 'regular.formatted_price'),
        ];
    }

    /**
     * Normalize portion sizes for constructor group response.
     *
     * @param  mixed  $portionSizes
     * @return array
     */
    protected function normalizePortionSizes($portionSizes): array
    {
        if (! is_array($portionSizes)) {
            return [];
        }

        return collect($portionSizes)
            ->filter(fn ($size) => is_array($size))
            ->map(function ($size) {
                return [
                    'name'     => (string) ($size['name'] ?? ''),
                    'quantity' => (int) ($size['quantity'] ?? 0),
                    'weight'   => (int) ($size['weight'] ?? 0),
                ];
            })
            ->filter(fn ($size) => $size['name'] !== '')
            ->values()
            ->all();
    }

    /**
     * Get summary of half portion pair product (половинка).
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array|null
     */
    protected function getHalfPortionPairSummary($product)
    {
        if (! $product) {
            return null;
        }

        return [
            'id'         => $product->id,
            'sku'        => $product->sku,
            'name'       => $product->name,
            'base_image' => ProductImage::getProductBaseImage($product),
            'nutrition'  => $this->getNutritionData($product),
        ];
    }
}
