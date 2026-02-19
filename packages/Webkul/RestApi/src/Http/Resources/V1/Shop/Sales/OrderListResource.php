<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Sales;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderListResource extends JsonResource
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
            'increment_id'          => $this->increment_id,
            'status'                => $this->status,
            'status_label'          => $this->status_label ?? $this->status,
            'items'                 => OrderListItemResource::collection($this->items),
            'grand_total'           => $this->grand_total,
            'formatted_grand_total' => core()->formatPrice($this->grand_total, $this->order_currency_code),
            'order_currency_code'   => $this->order_currency_code,
            'created_at'            => $this->created_at,
        ];
    }
}
