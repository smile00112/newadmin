<?php

namespace Webkul\Shop\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Helpers\Review;

class ProductResource extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        $this->reviewHelper = app(Review::class);

        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $productTypeInstance = $this->getTypeInstance();

        $minPrice = $productTypeInstance->getMinimalPrice();

        // PRICE_DBG: лог цены для товаров с "матч" в имени
        if (stripos((string) $this->name, 'матч') !== false || (int) $this->id === 151) {
            try {
                $cgId = optional(\Webkul\Customer\Facades\Customer::user())->customer_group_id
                    ?? optional(app(\Webkul\Customer\Repositories\CustomerGroupRepository::class)->findOneByField('code', 'guest'))->id;
                \Illuminate\Support\Facades\Log::info('PRICE_DBG_SHOP_PRODUCT_RESOURCE', [
                    'src'              => 'shop-product-resource',
                    'product_id'       => (int) $this->id,
                    'sku'              => $this->sku,
                    'name'             => $this->name,
                    'type'             => $this->type,
                    'min_price'        => $minPrice,
                    'product_price'    => $this->price,
                    'special_price'    => $this->special_price,
                    'channel_id'       => core()->getCurrentChannel()->id,
                    'customer_group_id' => $cgId,
                    'customer_id'      => optional(\Webkul\Customer\Facades\Customer::user())->id,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('PRICE_DBG_SHOP_PR_ERR', ['err' => $e->getMessage()]);
            }
        }

        return [
            'id'          => $this->id,
            'sku'         => $this->sku,
            'name'        => $this->name,
            'description' => $this->description,
            'url_key'     => $this->url_key,
            'base_image'  => product_image()->getProductBaseImage($this),
            'images'      => product_image()->getGalleryImages($this),
            'is_new'      => (bool) $this->new,
            'is_featured' => (bool) $this->featured,
            'on_sale'     => (bool) $productTypeInstance->haveDiscount(),
            'is_saleable' => (bool) $productTypeInstance->isSaleable(),
            'is_wishlist' => (bool) auth()->guard()->user()?->wishlist_items
                ->where('channel_id', core()->getCurrentChannel()->id)
                ->where('product_id', $this->id)->count(),
            'min_price'   => core()->formatPrice($minPrice),
            'prices'      => $productTypeInstance->getProductPrices(),
            'price_html'  => $productTypeInstance->getPriceHtml(),
            'ratings'     => [
                'average' => $this->reviewHelper->getAverageRating($this),
                'total'   => $this->reviewHelper->getTotalRating($this),
            ],
            'reviews'     => [
                'total'   => $this->reviewHelper->getTotalReviews($this),
            ],
        ];
    }
}
