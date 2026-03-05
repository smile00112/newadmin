<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Facades\ProductImage;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\Concerns\ProductResourceFields;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\ProductVideoResource;

class NomenclatureIngredientResource extends JsonResource
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

        return [
            'id'                 => $product->id,
            'sku'                => $product->sku,
            'name'               => $product->name,
            'price'              => core()->convertPrice($productTypeInstance->getMinimalPrice()),
            'formatted_price'    => core()->currency($productTypeInstance->getMinimalPrice()),
            'short_description'  => $this->cleanHtmlDescription($product->short_description),
            'description'        => $this->cleanHtmlDescription($product->description),
            'images'             => ProductImageResource::collection($product->images),
            'base_image'         => ProductImage::getProductBaseImage($product),
            'category_image'     => $this->getCategoryImage($product),
            'show_as_big_in_category' => (bool) ($product->show_as_big_in_category ?? false),
            'in_stock'           => $product->haveSufficientQuantity(1),
            'videos'             => ProductVideoResource::collection($product->videos),
            'is_half_portion'    => (bool) ($product->is_half_portion ?? false),
            'half_portion_pair_product_id' => $product->half_portion_pair_product_id,
            'attributes'         => $this->getProductAttributes($product),
            'nutrition'          => $this->getNutritionData($product),
            'weight'             => $product->weight !== null ? (float) $product->weight : null,
            'volume'             => $product->volume,
        ];
    }
}
