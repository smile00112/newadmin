<?php

namespace Webkul\Admin\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $additional = $this->resource->additional ?? [];

        if (! is_array($additional)) {
            $decoded = json_decode((string) $additional, true);
            $additional = is_array($decoded) ? $decoded : [];
        }

        unset($additional['local_id']);

        return [
            'id'         => $this->id,
            'order_id'   => $this->order_id,
            'additional' => (object) $additional,
            'product'    => new ProductResource($this->product),
        ];
    }
}
