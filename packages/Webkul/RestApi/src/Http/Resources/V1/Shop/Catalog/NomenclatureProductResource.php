<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Facades\ProductImage;

class NomenclatureProductResource extends JsonResource
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
            'type'            => $product->type,
            'name'            => $product->name,
            'price'           => core()->convertPrice($productTypeInstance->getMinimalPrice()),
            'formatted_price' => core()->currency($productTypeInstance->getMinimalPrice()),
            'base_image'      => ProductImage::getProductBaseImage($product),
            'in_stock'        => $product->haveSufficientQuantity(1),
            'weight'          => $product->weight !== null ? (float) $product->weight : null,
            'volume'          => $product->volume !== null && $product->volume !== '' ? (float) $product->volume : null,
        ];
    }
}
