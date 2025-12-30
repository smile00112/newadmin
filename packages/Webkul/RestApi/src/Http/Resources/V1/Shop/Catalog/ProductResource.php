<?php

namespace Webkul\RestApi\Http\Resources\V1\Shop\Catalog;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Webkul\Checkout\Facades\Cart;
use Webkul\Product\Facades\ProductImage;
use Webkul\Product\Helpers\BundleOption;

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
        /* assign product */
        $product = $this->product ?? $this;

        /* get type instance */
        $productTypeInstance = $product->getTypeInstance();

        /* Get review helper */
        $reviewHelper = app(\Webkul\Product\Helpers\Review::class);

        /* generating resource */
        return [
            /* product's information */
            'id'                 => $product->id,
            'sku'                => $product->sku,
            'type'               => $product->type,
            'name'               => $product->name,
            'url_key'            => $product->url_key,
            'price'              => core()->convertPrice($productTypeInstance->getMinimalPrice()),
            'formatted_price'    => core()->currency($productTypeInstance->getMinimalPrice()),
            'short_description'  => $product->short_description,
            'description'        => $product->description,
            'images'             => ProductImageResource::collection($product->images),
            'videos'             => ProductVideoResource::collection($product->videos),
            'base_image'         => ProductImage::getProductBaseImage($product),
            'category_image'     => $this->getCategoryImage($product),
            'created_at'         => $product->created_at,
            'updated_at'         => $product->updated_at,

            /* product's reviews */
            'reviews' => [
                'total'          => $total = $reviewHelper->getTotalReviews($product),
                'total_rating'   => $total ? $reviewHelper->getTotalRating($product) : 0,
                'average_rating' => $total ? $reviewHelper->getAverageRating($product) : 0,
                'percentage'     => $total ? json_encode($reviewHelper->getPercentageRating($product)) : [],
            ],

            /* product's checks */
            'in_stock'              => $product->haveSufficientQuantity(1),
            'is_saved'              => false,
            'is_item_in_cart'       => Cart::getCart(),
            'show_quantity_changer' => $this->when(
                $product->type !== 'grouped',
                $product->getTypeInstance()->showQuantityBox()
            ),

            /* product attributes with their options */
            'attributes' => $this->getProductAttributes($product),

            /* product's extra information */
            $this->merge($this->allProductExtraInfo()),

            /* special price cases */
            $this->merge($this->specialPriceInfo()),

            /* super attributes */
            $this->mergeWhen($productTypeInstance->isComposite(), [
                'super_attributes' => AttributeResource::collection($product->super_attributes),
            ]),
        ];
    }

    /**
     * Get product attributes with their available options.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getProductAttributes($product)
    {
        $attributes = [];

        $attributeFamily = $product->attribute_family;

        if (!$attributeFamily) {
            return $attributes;
        }

        $customAttributes = $attributeFamily->custom_attributes;

        foreach ($customAttributes as $attribute) {
            // Only include attributes that have options (select, multiselect, checkbox)
            if (!in_array($attribute->type, ['select', 'multiselect', 'checkbox'])) {
                continue;
            }

            if (!$attribute->options || $attribute->options->count() === 0) {
                continue;
            }

            $currentValue = $product->getCustomAttributeValue($attribute);

            $attributeData = [
                'id'            => $attribute->id,
                'code'          => $attribute->code,
                'name'          => $attribute->admin_name ?? $attribute->code,
                'type'          => $attribute->type,
                'current_value' => $currentValue,
                'options'       => $attribute->options->map(function ($option) {
                    return [
                        'id'    => $option->id,
                        'code'  => $option->admin_name ?? $option->id,
                        'label' => $option->label ?? $option->admin_name,
                    ];
                })->values()->toArray(),
            ];

            $attributes[] = $attributeData;
        }

        return $attributes;
    }

    /**
     * Get special price information.
     *
     * @return array
     */
    private function specialPriceInfo()
    {
        $product = $this->product ?? $this;

        $productTypeInstance = $product->getTypeInstance();

        return [
            'special_price'           => $this->when(
                $productTypeInstance->haveDiscount(),
                core()->convertPrice($productTypeInstance->getMinimalPrice())
            ),
            'formatted_special_price' => $this->when(
                $productTypeInstance->haveDiscount(),
                core()->currency($productTypeInstance->getMinimalPrice())
            ),
            'regular_price'           => $this->when(
                $productTypeInstance->haveDiscount(),
                data_get($productTypeInstance->getProductPrices(), 'regular.price')
            ),
            'formatted_regular_price' => $this->when(
                $productTypeInstance->haveDiscount(),
                data_get($productTypeInstance->getProductPrices(), 'regular.formatted_price')
            ),
        ];
    }

    /**
     * Get all product's extra information.
     *
     * @return array
     */
    private function allProductExtraInfo()
    {
        $product = $this->product ?? $this;

        $productTypeInstance = $product->getTypeInstance();

        return [
            /* grouped product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Grouped,
                $product->type == 'grouped'
                    ? $this->getGroupedProductInfo($product)
                    : null
            ),

            /* bundle product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Bundle,
                $product->type == 'bundle'
                    ? $this->getBundleProductInfo($product)
                    : null
            ),

            /* configurable product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Configurable,
                $product->type == 'configurable'
                    ? $this->getConfigurableProductInfo($product)
                    : null
            ),

            /* downloadable product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Downloadable,
                $product->type == 'downloadable'
                    ? $this->getDownloadableProductInfo($product)
                    : null
            ),

            /* booking product */
            $this->mergeWhen(
                $productTypeInstance instanceof \Webkul\Product\Type\Booking,
                $product->type == 'booking'
                    ? $this->getBookingProductInfo($product)
                    : null
            ),
        ];
    }

    /**
     * Get grouped product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getGroupedProductInfo($product)
    {
        return [
            'grouped_products' => $product->grouped_products->map(function ($groupedProduct) {
                $associatedProduct = $groupedProduct->associated_product;

                $data = $associatedProduct->toArray();

                return array_merge($data, [
                    'qty'                   => $groupedProduct->qty,
                    'isSaleable'            => $associatedProduct->getTypeInstance()->isSaleable(),
                    'formatted_price'       => $associatedProduct->getTypeInstance()->getPriceHtml(),
                    'show_quantity_changer' => $associatedProduct->getTypeInstance()->showQuantityBox(),
                ]);
            }),
        ];
    }

    /**
     * Get bundle product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getBundleProductInfo($product)
    {
        return [
            'bundle_options' => app(BundleOption::class)->getBundleConfig($product),
        ];
    }

    /**
     * Get configurable product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getConfigurableProductInfo($product)
    {
        return [
            'variants' => $product->variants,
        ];
    }

    /**
     * Get downloadable product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getDownloadableProductInfo($product)
    {
        return [
            'downloadable_links' => $product->downloadable_links->map(function ($downloadableLink) {
                $data = $downloadableLink->toArray();

                if (isset($data['sample_file'])) {
                    $data['price'] = core()->currency($downloadableLink->price);
                    $data['sample_download_url'] = route('shop.downloadable.download_sample', ['type' => 'link', 'id' => $downloadableLink['id']]);
                }

                return $data;
            }),

            'downloadable_samples' => $product->downloadable_samples->map(function ($downloadableSample) {
                $sample = $downloadableSample->toArray();
                $data = $sample;
                $data['download_url'] = route('shop.downloadable.download_sample', ['type' => 'sample', 'id' => $sample['id']]);

                return $data;
            }),
        ];
    }

    /**
     * Get booking product's extra information.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return array
     */
    private function getBookingProductInfo($product)
    {
        return [
            'booking' => $product->booking_products,
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
