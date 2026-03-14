<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Sales;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\RestApi\Http\Resources\Concerns\ConstructorIngredients;

class OrderListItemResource extends JsonResource
{
    use ConstructorIngredients;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $currencyCode = $this->order?->order_currency_code ?? config('app.currency');

        $product = $this->product;
        $baseImage = null;
        $images = [];

        if ($product) {
            $baseImage = product_image()->getProductBaseImage($product);
            $images = product_image()->getGalleryImages($product);
        }

        return [
            'id'               => $this->id,
            'product_id'       => $this->product_id,
            'type'             => $this->type,
            'name'             => $this->name,
            'sku'              => $this->sku,
            'qty_ordered'      => $this->qty_ordered,
            'price'            => $this->price,
            'formatted_price'  => core()->formatPrice($this->price, $currencyCode),
            'total'            => $this->total,
            'formatted_total'  => core()->formatPrice($this->total, $currencyCode),
            'base_image'       => $baseImage,
            'images'           => $images,
            'ingredients'      => $this->when(
                in_array($this->type, ['constructor', 'configurable_constructor'], true),
                function () {
                    $additional = is_array($this->resource->additional)
                        ? $this->resource->additional
                        : (array) json_decode($this->resource->additional ?? '{}', true);

                    return $this->getIngredientsFromAdditional($additional);
                }
            ),
        ];
    }
}
