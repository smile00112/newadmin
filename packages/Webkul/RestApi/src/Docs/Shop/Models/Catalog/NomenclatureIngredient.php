<?php

namespace Webkul\RestApi\Docs\Shop\Models\Catalog;

/**
 * @OA\Schema(
 *     title="NomenclatureIngredient",
 *     description="Ingredient in nomenclature response",
 * )
 */
class NomenclatureIngredient
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="Ingredient ID",
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
     *     description="Ingredient SKU",
     *     example="ingredient-1"
     * )
     *
     * @var string
     */
    public $sku;

    /**
     * @OA\Property(
     *     title="Name",
     *     description="Ingredient name",
     *     example="Tomato"
     * )
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *     title="Price",
     *     description="Ingredient price",
     *     example=12.20
     * )
     *
     * @var float
     */
    public $price;

    /**
     * @OA\Property(
     *     title="Formatted Price",
     *     description="Ingredient's formatted price",
     *     example="$12.20"
     * )
     *
     * @var string
     */
    public $formatted_price;

    /**
     * @OA\Property(
     *     title="Base Image",
     *     description="Ingredient's base image",
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
     *     description="Whether ingredient is in stock",
     *     example=true
     * )
     *
     * @var bool
     */
    public $in_stock;

    /**
     * @OA\Property(
     *     title="Videos",
     *     description="Ingredient's videos",
     *     type="array",
     *
     *     @OA\Items(ref="#/components/schemas/ProductVideo")
     * )
     *
     * @var array
     */
    public $videos;

    /**
     * @OA\Property(
     *     title="Nutrition",
     *     description="Nutrition information (calories, proteins, fats, carbs)",
     *     type="object",
     *     nullable=true,
     *     example={
     *          "calories": 25.5,
     *          "proteins": 1.2,
     *          "fats": 0.3,
     *          "carbs": 5.0
     *     }
     * )
     *
     * @var object|null
     */
    public $nutrition;
}
