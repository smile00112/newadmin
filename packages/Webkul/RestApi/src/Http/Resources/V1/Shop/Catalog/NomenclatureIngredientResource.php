<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Facades\ProductImage;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\ProductVideoResource;

class NomenclatureIngredientResource extends JsonResource
{
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

        return [
            'id'              => $product->id,
            'sku'             => $product->sku,
            'name'            => $product->name,
            'price'           => core()->convertPrice($productTypeInstance->getMinimalPrice()),
            'formatted_price' => core()->currency($productTypeInstance->getMinimalPrice()),
            'base_image'      => ProductImage::getProductBaseImage($product),
            'in_stock'        => $product->haveSufficientQuantity(1),
            'videos'          => ProductVideoResource::collection($product->videos),
            'nutrition'       => $this->getNutritionData($product),
        ];
    }

    /**
     * Get nutrition information (КЖБУ).
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array|null
     */
    private function getNutritionData($product)
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
}
