<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

class SavedCardResource extends JsonResource
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
            'id'         => $this->id,
            'binding_id' => $this->binding_id,
            'card_mask'  => $this->card_mask,
            'card_type'  => $this->card_type,
            'is_active'  => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
