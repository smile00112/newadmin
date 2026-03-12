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
     *     enum={"simple", "configurable", "virtual", "grouped", "downloadable", "bundle", "booking", "constructor", "ingredient", "configurable_constructor"}
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

    /**
     * @OA\Property(
     *     title="Short Description",
     *     description="Product short description without HTML",
     *     example="What is Lorem Ipsum?"
     * )
     *
     * @var string|null
     */
    public $short_description;

    /**
     * @OA\Property(
     *     title="Description",
     *     description="Product description without HTML",
     *     example="Lorem Ipsum is simply dummy text..."
     * )
     *
     * @var string|null
     */
    public $description;

    /**
     * @OA\Property(
     *     title="Images",
     *     description="Product images",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/ProductImage")
     * )
     *
     * @var array
     */
    public $images;

    /**
     * @OA\Property(
     *     title="Videos",
     *     description="Product videos",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/ProductVideo")
     * )
     *
     * @var array
     */
    public $videos;

    /**
     * @OA\Property(
     *     title="Category Image",
     *     description="Product category image",
     *     type="object",
     *     nullable=true
     * )
     *
     * @var object|null
     */
    public $category_image;

    /**
     * @OA\Property(
     *     title="Show as Big in Category",
     *     description="Whether to show product as big item in category listing",
     *     example=false
     * )
     *
     * @var bool
     */
    public $show_as_big_in_category;

    /**
     * @OA\Property(
     *     title="Is Half Portion",
     *     description="Whether product is half portion",
     *     example=false
     * )
     *
     * @var bool
     */
    public $is_half_portion;

    /**
     * @OA\Property(
     *     title="Half Portion Pair Product ID",
     *     description="ID of paired half/full portion product",
     *     format="int64",
     *     nullable=true
     * )
     *
     * @var int|null
     */
    public $half_portion_pair_product_id;

    /**
     * @OA\Property(
     *     title="Show Quantity Changer",
     *     description="Whether to show quantity selector",
     *     example=true
     * )
     *
     * @var bool
     */
    public $show_quantity_changer;

    /**
     * @OA\Property(
     *     title="Attributes",
     *     description="Product attributes with options",
     *     type="array",
     *     @OA\Items(type="object")
     * )
     *
     * @var array
     */
    public $attributes;

    /**
     * @OA\Property(
     *     title="Nutrition",
     *     description="Nutrition information",
     *     type="object",
     *     nullable=true,
     *     example={"calories": 25.5, "proteins": 1.2, "fats": 0.3, "carbs": 5.0}
     * )
     *
     * @var object|null
     */
    public $nutrition;

    /**
     * @OA\Property(
     *     title="Up Sells",
     *     description="Array of product IDs for up-sell products",
     *     type="array",
     *     @OA\Items(type="integer", format="int64"),
     *     example={1, 2, 3}
     * )
     *
     * @var array
     */
    public $up_sells;

    /**
     * @OA\Property(
     *     title="Cross Sells",
     *     description="Array of product IDs for cross-sell products",
     *     type="array",
     *     @OA\Items(type="integer", format="int64"),
     *     example={4, 5}
     * )
     *
     * @var array
     */
    public $cross_sells;

    /**
     * @OA\Property(
     *     title="Drinks",
     *     description="Array of drink objects with product ID and default flag",
     *     type="array",
     *     @OA\Items(
     *         type="object",
     *         @OA\Property(
     *             property="id",
     *             type="integer",
     *             format="int64",
     *             description="Drink product ID"
     *         ),
     *         @OA\Property(
     *             property="default",
     *             type="boolean",
     *             description="Whether this drink is selected by default"
     *         )
     *     ),
     *     example={
     *         {"id": 27, "default": true},
     *         {"id": 28, "default": false}
     *     }
     * )
     *
     * @var array
     */
    public $drinks;

    /**
     * @OA\Property(
     *     title="Constructor Options",
     *     description="Constructor product options; each group has products array with objects {id, default}",
     *     type="array",
     *     @OA\Items(type="object")
     * )
     *
     * @var array
     */
    public $constructor_options;
}
