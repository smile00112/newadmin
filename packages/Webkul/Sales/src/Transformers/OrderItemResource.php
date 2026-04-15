<?php

namespace Webkul\Sales\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Indicates if the resource's collection keys should be preserved.
     *
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $product = $this->product;
        $baseImage = null;
        $images = [];
        $additional = $this->resolveAdditionalWithoutLocalId();

        if ($product) {
            $baseImage = product_image()->getProductBaseImage($product);
            $images = product_image()->getGalleryImages($product);
        }

        return [
            'product_id'            => $this->product_id,
            'product_type'          => $product ? get_class($product) : null,
            'sku'                   => $this->sku,
            'type'                  => $this->type,
            'name'                  => $this->name,
            'weight'                => $this->weight,
            'total_weight'          => $this->total_weight,
            'qty_ordered'           => $this->parent_id ? ($this->quantity ?? 1) * $this->parent->quantity : ($this->quantity ?? 1),
            'price'                 => $this->price,
            'price_incl_tax'        => $this->price_incl_tax,
            'base_price'            => $this->base_price,
            'base_price_incl_tax'   => $this->base_price_incl_tax,
            'total'                 => $this->total,
            'total_incl_tax'        => $this->total_incl_tax,
            'base_total'            => $this->base_total,
            'base_total_incl_tax'   => $this->base_total_incl_tax,
            'tax_percent'           => $this->tax_percent,
            'tax_amount'            => $this->tax_amount,
            'base_tax_amount'       => $this->base_tax_amount,
            'tax_category_id'       => $this->tax_category_id,
            'discount_percent'      => $this->discount_percent,
            'discount_amount'       => $this->discount_amount,
            'base_discount_amount'  => $this->base_discount_amount,
            'base_image'            => $baseImage,
            'images'                => $images,
            'additional'            => array_merge($additional, ['locale' => core()->getCurrentLocale()->code]),
            'children'              => self::collection($this->children)->jsonSerialize(),
        ];
    }

    private function resolveAdditionalWithoutLocalId(): array
    {
        $additional = $this->resource->additional ?? [];

        if (! is_array($additional)) {
            $decoded = json_decode((string) $additional, true);
            $additional = is_array($decoded) ? $decoded : [];
        }

        unset($additional['local_id']);

        return $additional;
    }
}
