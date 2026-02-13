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
            'description'           => $this->cleanHtmlDescription($this->description),
            'status'                => $this->status,
            'banner_url'            => $this->banner_url,
            'logo_url'              => $this->logo_url,
            'position'              => $this->position,
            'additional'            => $this->additional,
        ];
    }

    /**
     * Clean HTML tags from description text.
     *
     * @param  string|null  $description
     * @return string|null
     */
    private function cleanHtmlDescription($description)
    {
        if (empty($description)) {
            return null;
        }

        // Remove HTML tags and decode HTML entities
        $cleaned = strip_tags($description);
        $cleaned = html_entity_decode($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Trim whitespace
        $cleaned = trim($cleaned);

        return !empty($cleaned) ? $cleaned : null;
    }
}
