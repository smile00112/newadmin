<?php

namespace Webkul\Shop\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'parent_id'    => $this->parent_id,
            'name'         => $this->name,
            'slug'         => $this->slug,
            'status'               => $this->status,
            'position'             => $this->position,
            'display_mode'          => $this->display_mode,
            'product_display_type' => $this->product_display_type,
            'description'          => $this->description,
            'logo'         => $this->when($this->logo_path, [
                'small_image_url'    => cache_image_url($this->logo_path, 'small'),
                'medium_image_url'   => cache_image_url($this->logo_path, 'medium'),
                'large_image_url'    => cache_image_url($this->logo_path, 'large'),
                'original_image_url' => cache_image_url($this->logo_path, 'original'),
            ]),
            'banner'       => $this->when($this->banner_path, [
                'small_image_url'    => cache_image_url($this->banner_path, 'small'),
                'medium_image_url'   => cache_image_url($this->banner_path, 'medium'),
                'large_image_url'    => cache_image_url($this->banner_path, 'large'),
                'original_image_url' => cache_image_url($this->banner_path, 'original'),
            ]),
            'meta'         => [
                'title'       => $this->meta_title,
                'keywords'    => $this->meta_keywords,
                'description' => $this->meta_description,
            ],
            'translations' => $this->translations,
            'additional'   => $this->additional,
        ];
    }
}
