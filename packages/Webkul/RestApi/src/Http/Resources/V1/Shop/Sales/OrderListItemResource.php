<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Sales;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderListItemResource extends JsonResource
{
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
            'name'             => $this->name,
            'sku'              => $this->sku,
            'qty_ordered'      => $this->qty_ordered,
            'price'            => $this->price,
            'formatted_price'  => core()->formatPrice($this->price, $currencyCode),
            'total'            => $this->total,
            'formatted_total'  => core()->formatPrice($this->total, $currencyCode),
            'base_image'       => $baseImage,
            'images'           => $images,
        ];
    }
}
