<?php

namespace Webkul\RestApi\Docs\Shop\Models\Catalog;

/**
 * @OA\Schema(
 *     title="NomenclatureProduct",
 *     description="Product in nomenclature response",
 * )
 */
class NomenclatureProduct
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="Product ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(
     *     title="SKU",
     *     description="Product SKU",
     *     example="product-1"
     * )
     *
     * @var string
     */
    public $sku;

    /**
     * @OA\Property(
     *     title="Type",
     *     description="Product type",
     *     enum={"simple", "configurable", "virtual", "grouped", "downloadable", "bundle", "booking", "constructor", "ingredient"}
     * )
     *
     * @var string
     */
    public $type;

    /**
     * @OA\Property(
     *     title="Name",
     *     description="Product name",
     *     example="Men T-Shirt"
     * )
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *     title="Price",
     *     description="Product price",
     *     example=12.20
     * )
     *
     * @var float
     */
    public $price;

    /**
     * @OA\Property(
     *     title="Formatted Price",
     *     description="Product's formatted price",
     *     example="$12.20"
     * )
     *
     * @var string
     */
    public $formatted_price;

    /**
     * @OA\Property(
     *     title="Base Image",
     *     description="Product's base image",
     *     type="object",
     *     example={
     *          "small_image_url": "http://localhost/public/vendor/webkul/ui/assets/images/product/small-product-placeholder.webp",
     *          "medium_image_url": "http://localhost/public/vendor/webkul/ui/assets/images/product/meduim-product-placeholder.webp",
     *          "large_image_url": "http://localhost/public/vendor/webkul/ui/assets/images/product/large-product-placeholder.webp",
     *          "original_image_url": "http://localhost/public/vendor/webkul/ui/assets/images/product/original-product-placeholder.webp",
     *     }
     * )
     *
     * @var object
     */
    public $base_image;

    /**
     * @OA\Property(
     *     title="In Stock",
     *     description="Whether product is in stock",
     *     example=true
     * )
     *
     * @var bool
     */
    public $in_stock;
}
