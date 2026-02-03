<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Checkout;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Facades\ProductImage;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\ProductResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Если запрошен минимальный набор данных
        if ($request->get('minimal', false) || ($this->resource->additional['minimal'] ?? false)) {
            return $this->toMinimalArray($request);
        }

        // Полный набор данных
        return $this->toFullArray($request);
    }

    /**
     * Минимальный набор данных для быстрой сериализации.
     */
    private function toMinimalArray($request): array
    {
        return [
            'id'                    => $this->id,
            'quantity'              => $this->quantity,
            'sku'                   => $this->sku,
            'type'                  => $this->type,
            'name'                  => $this->name,
            'price'                 => $this->price,
            'formatted_price'       => core()->formatPrice($this->price, $this->cart->cart_currency_code),
            'total'                 => $this->total,
            'formatted_total'       => core()->formatPrice($this->total, $this->cart->cart_currency_code),
            'discount_amount'       => $this->discount_amount,
            'formatted_discount_amount' => core()->formatPrice($this->discount_amount, $this->cart->cart_currency_code),
            'tax_amount'            => $this->tax_amount,
            'formatted_tax_amount'  => core()->formatPrice($this->tax_amount, $this->cart->cart_currency_code),
            // Минимальная информация о продукте - только ID, SKU, название и базовое изображение
            'product'               => $this->when($this->product_id, function () {
                $product = $this->product;
                if (!$product) {
                    return null;
                }
                
                return [
                    'id'         => $product->id,
                    'sku'        => $product->sku,
                    'name'       => $product->name,
                    'base_image' => ProductImage::getProductBaseImage($product),
                ];
            }),
        ];
    }

    /**
     * Полный набор данных (оригинальная логика).
     */
    private function toFullArray($request): array
    {
        return [
            'id'                             => $this->id,
            'quantity'                       => $this->quantity,
            'sku'                            => $this->sku,
            'type'                           => $this->type,
            'name'                           => $this->name,
            'coupon_code'                    => $this->coupon_code,
            'weight'                         => $this->weight,
            'total_weight'                   => $this->total_weight,
            'base_total_weight'              => $this->base_total_weight,
            'price'                          => $this->price,
            'formatted_price'                => core()->formatPrice($this->price, $this->cart->cart_currency_code),
            'base_price'                     => $this->base_price,
            'formatted_base_price'           => core()->formatBasePrice($this->base_price),
            'custom_price'                   => $this->custom_price,
            'formatted_custom_price'         => core()->formatPrice($this->custom_price, $this->cart->cart_currency_code),
            'total'                          => $this->total,
            'formatted_total'                => core()->formatPrice($this->total, $this->cart->cart_currency_code),
            'base_total'                     => $this->base_total,
            'formatted_base_total'           => core()->formatBasePrice($this->base_total),
            'tax_percent'                    => $this->tax_percent,
            'tax_amount'                     => $this->tax_amount,
            'formatted_tax_amount'           => core()->formatPrice($this->tax_amount, $this->cart->cart_currency_code),
            'base_tax_amount'                => $this->base_tax_amount,
            'formatted_base_tax_amount'      => core()->formatBasePrice($this->base_tax_amount),
            'discount_percent'               => $this->discount_percent,
            'discount_amount'                => $this->discount_amount,
            'formatted_discount_amount'      => core()->formatPrice($this->discount_amount, $this->cart->cart_currency_code),
            'base_discount_amount'           => $this->base_discount_amount,
            'formatted_base_discount_amount' => core()->formatBasePrice($this->base_discount_amount),
            'additional'                     => is_array($this->resource->additional)
                ? $this->resource->additional
                : json_decode($this->resource->additional, true),
            'child'                          => new self($this->child),
            'children'                       => $this->when($this->children->isNotEmpty(), self::collection($this->children)),
            'product'                        => $this->when($this->product_id, new ProductResource($this->product)),
            'created_at'                     => $this->created_at,
            'updated_at'                     => $this->updated_at,
        ];
    }
}
