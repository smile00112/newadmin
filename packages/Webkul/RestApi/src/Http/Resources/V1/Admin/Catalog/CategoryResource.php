<?php

namespace Webkul\RestApi\Http\Resources\V1\Admin\Catalog;

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
            'id'                    => $this->id,
            'name'                  => $this->name,
            'slug'                  => $this->slug,
            'display_mode'          => $this->display_mode,
            'product_display_type' => $this->product_display_type,
            'description'           => $this->description,
            'status'                => $this->status,
            'banner_url'            => $this->banner_url,
            'logo_url'              => $this->logo_url,
            'position'              => $this->position,
            'additional'            => $this->additional,
        ];
    }
}
