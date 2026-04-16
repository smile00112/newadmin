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
        $imageProduct = $product;

        if ($product) {
            // For variants, prefer parent product for images if variant has none
            if ($product->parent_id && $product->parent) {
                $variantImages = $product->images ?? collect();
                if ($variantImages->isEmpty()) {
                    $imageProduct = $product->parent;
                }
            }
            $baseImage = product_image()->getProductBaseImage($imageProduct);
            $images = product_image()->getGalleryImages($imageProduct);
        }

        // Resolve display name: if product is a variant (has parent_id), use parent product's name
        $displayName = $this->name;
        if ($product && $product->parent_id) {
            $parentName = \Webkul\Product\Models\ProductFlat::where('product_id', $product->parent_id)
                ->where('locale', app()->getLocale())
                ->value('name');
            if ($parentName) {
                $displayName = $parentName;
            }
        }

        return [
            'id'               => $this->id,
            'product_id'       => $this->product_id,
            'type'             => $this->type,
            'name'             => $displayName,
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
