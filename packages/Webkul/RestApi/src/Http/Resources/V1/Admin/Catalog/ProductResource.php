<?php

namespace Webkul\RestApi\Http\Resources\V1\Admin\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /**
         * Not able to use individual key in the resource because
         * attributes are system defined and custom defined.
         *
         * @var array
         */
        $mainAttributes = $this->resource->toArray();

        return [
            /**
             * Main attributes.
             */
            ...$mainAttributes,

            'sku' => $this->resource->sku,

            /**
             * Additional attributes.
             */
            'images'         => ProductImageResource::collection($this->images),
            'videos'         => ProductVideoResource::collection($this->videos),
            'category_image' => $this->getCategoryImage($this->resource),
            'additional'     => $this->additional,
        ];
    }

    /**
     * Get category image URL.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array|null
     */
    private function getCategoryImage($product)
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
}
