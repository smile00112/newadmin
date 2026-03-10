<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Catalog;

use Webkul\RestApi\Http\Resources\V1\Admin\Catalog\CategoryResource;

class CatalogV2Resource extends CategoryResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'products' => CatalogV2ProductResource::collection($this->products),
        ]);
    }
}
