<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Checkout;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Facades\ProductImage;
use Webkul\RestApi\Http\Resources\Concerns\ConstructorIngredients;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\ProductResource;

class CartItemResource extends JsonResource
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
        $additional = $this->resolveAdditional();

        $data = [
            'id'                    => $this->id,
            'local_id'              => $additional['local_id'] ?? null,
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

                if (! $product) {
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

        // Для configurable и configurable_constructor добавляем выбранные модификации.
        if (in_array($this->type, ['configurable', 'configurable_constructor'], true)) {
            $modifications = $this->getModificationsSummary($additional);

            if (! empty($modifications)) {
                $data['modifications'] = $modifications;
            }
        }

        // Для constructor и configurable_constructor добавляем выбранные ингредиенты.
        if (in_array($this->type, ['constructor', 'configurable_constructor'], true)) {
            $ingredients = $this->getIngredientsFromAdditional($additional);

            if (! empty($ingredients)) {
                $data['ingredients'] = $ingredients;
            }
        }

        return $data;
    }

    /**
     * Полный набор данных (оригинальная логика).
     */
    private function toFullArray($request): array
    {
        $additional = $this->resolveAdditional();

        return [
            'id'                             => $this->id,
            'local_id'                       => $additional['local_id'] ?? null,
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
            'additional'                     => $additional,
            'child'                          => new self($this->child),
            'children'                       => $this->when($this->children->isNotEmpty(), self::collection($this->children)),
            'product'                        => $this->when($this->product_id, new ProductResource($this->product)),
            'created_at'                     => $this->created_at,
            'updated_at'                     => $this->updated_at,
        ];
    }

    /**
     * Сформировать краткую информацию о выбранных модификациях configurable товара.
     *
     * Ожидает структуру additional['attributes'] в виде:
     *  { attribute_code: { attribute_name, option_id, option_label } }.
     */
    private function getModificationsSummary(array $additional): array
    {
        $attributes = $additional['attributes'] ?? [];

        if (! is_array($attributes) || empty($attributes)) {
            return [];
        }

        $result = [];

        foreach ($attributes as $code => $attribute) {
            if (! is_array($attribute)) {
                continue;
            }

            $optionId = $attribute['option_id'] ?? null;
            $optionLabel = $attribute['option_label'] ?? null;
            $attributeName = $attribute['attribute_name'] ?? $code;

            $result[] = [
                'code'         => (string) $code,
                'name'         => (string) $attributeName,
                'option_id'    => $optionId !== null ? (int) $optionId : null,
                'option_label' => $optionLabel !== null ? (string) $optionLabel : null,
            ];
        }

        return $result;
    }

    /**
     * Структура additional может быть массивом или JSON-строкой.
     */
    private function resolveAdditional(): array
    {
        $rawAdditional = $this->resource->additional ?? [];

        if (is_array($rawAdditional)) {
            return $rawAdditional;
        }

        $decoded = json_decode((string) $rawAdditional, true);

        return is_array($decoded) ? $decoded : [];
    }
}
