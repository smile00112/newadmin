<?php

namespace Webkul\RestApi\Docs\Admin\Models\Catalog;

/**
 * @OA\Schema(
 *     title="Product",
 *     description="Product model",
 * )
 */
class Product
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(
     *     title="SKU",
     *     description="Product SKU",
     *     example="men-t-shirt"
     * )
     *
     * @var string
     */
    private $sku;

    /**
     * @OA\Property(
     *     title="Type",
     *     description="Product type",
     *     enum={"simple", "configurable", "virtual", "grouped", "downloadable", "bundle"}
     * )
     *
     * @var string
     */
    private $type;

    /**
     * @OA\Property(
     *     title="Created at",
     *     description="Created at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $created_at;

    /**
     * @OA\Property(
     *     title="Updated at",
     *     description="Updated at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @OA\Property(
     *     title="Parent ID",
     *     description="Parent ID, Use in case of child product",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $parent_id;

    /**
     * @OA\Property(
     *     title="Attribute Family ID",
     *     description="Attribute family ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $attribute_family_id;

    /**
     * @OA\Property(
     *     title="Additional",
     *     description="Additional",
     *     example=null
     * )
     *
     * @var string
     */
    private $additional;

    /**
     * @OA\Property(
     *     title="Is Half Portion",
     *     description="Ingredient is half portion (ingredient type only)",
     *     example=false,
     *     type="boolean"
     * )
     *
     * @var bool
     */
    private $is_half_portion;

    /**
     * @OA\Property(
     *     title="Half Portion Pair Product ID",
     *     description="Linked ingredient product ID for half portion pair (ingredient type only)",
     *     example=null,
     *     type="integer",
     *     nullable=true
     * )
     *
     * @var int|null
     */
    private $half_portion_pair_product_id;

    /**
     * @OA\Property(
     *     title="Attribute Values",
     *     description="Product's attribute values",
     *     type="object",
     *     ref="#/components/schemas/AttributeFamily"
     * )
     *
     * @var object
     */
    private $attribute_family;

    /**
     * @OA\Property(
     *     title="Attribute Values",
     *     description="Product's attribute values",
     *     type="array",
     *
     *     @OA\Items(ref="#/components/schemas/ProductAttributeValue")
     * )
     *
     * @var array
     */
    private $attribute_values;

    /**
     * @OA\Property(
     *     title="Customer Group Prices",
     *     description="Customer group price discount",
     *     type="array",
     *
     *     @OA\Items(ref="#/components/schemas/ProductCustomerGroupPrice")
     * )
     *
     * @var array
     */
    private $customer_group_prices;

    /**
     * @OA\Property(
     *     title="Images",
     *     description="Product's images",
     *     type="array",
     *
     *     @OA\Items(ref="#/components/schemas/ProductImage")
     * )
     *
     * @var array
     */
    private $images;

    /**
     * @OA\Property(
     *     title="Videos",
     *     description="Product's videos",
     *     type="array",
     *
     *     @OA\Items(ref="#/components/schemas/ProductVideo")
     * )
     *
     * @var array
     */
    private $videos;

    /**
     * @OA\Property(
     *     title="Category Image",
     *     description="Product's category image for displaying in category listings",
     *     type="object",
     *     nullable=true,
     *     example={
     *          "path": "product/1/category_abc123.webp",
     *          "url": "http://localhost/storage/product/1/category_abc123.webp",
     *          "original_image_url": "http://localhost/storage/product/1/category_abc123.webp",
     *          "small_image_url": "http://localhost/cache/small/product/1/category_abc123.webp",
     *          "medium_image_url": "http://localhost/cache/medium/product/1/category_abc123.webp",
     *          "large_image_url": "http://localhost/cache/large/product/1/category_abc123.webp",
     *     },
     *
     *     @OA\Property(property="path", type="string", description="Image path"),
     *     @OA\Property(property="url", type="string", description="Image URL"),
     *     @OA\Property(property="original_image_url", type="string", description="Original image URL"),
     *     @OA\Property(property="small_image_url", type="string", description="Small image URL"),
     *     @OA\Property(property="medium_image_url", type="string", description="Medium image URL"),
     *     @OA\Property(property="large_image_url", type="string", description="Large image URL")
     * )
     *
     * @var object|null
     */
    private $category_image;
}
