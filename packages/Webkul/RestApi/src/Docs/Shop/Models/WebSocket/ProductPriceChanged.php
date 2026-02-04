<?php

namespace Webkul\RestApi\Docs\Shop\Models\WebSocket;

/**
 * @OA\Schema(
 *     title="ProductPriceChanged",
 *     description="WebSocket event payload when product price changes",
 *     type="object"
 * )
 */
class ProductPriceChanged
{
    /**
     * @OA\Property(
     *     title="Product ID",
     *     description="Product ID",
     *     format="int64",
     *     example=123
     * )
     *
     * @var int
     */
    private $product_id;

    /**
     * @OA\Property(
     *     title="SKU",
     *     description="Product SKU",
     *     example="PRODUCT-SKU-123"
     * )
     *
     * @var string
     */
    private $sku;

    /**
     * @OA\Property(
     *     title="Name",
     *     description="Product name",
     *     example="Product Name"
     * )
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     title="Previous Price",
     *     description="Previous price before change",
     *     format="float",
     *     example=1000.00
     * )
     *
     * @var float
     */
    private $previous_price;

    /**
     * @OA\Property(
     *     title="Current Price",
     *     description="Current price after change",
     *     format="float",
     *     example=1200.00
     * )
     *
     * @var float
     */
    private $current_price;

    /**
     * @OA\Property(
     *     title="Price Change",
     *     description="Absolute price change amount",
     *     format="float",
     *     example=200.00
     * )
     *
     * @var float
     */
    private $price_change;

    /**
     * @OA\Property(
     *     title="Price Change Percent",
     *     description="Percentage change in price",
     *     format="float",
     *     example=20.00
     * )
     *
     * @var float
     */
    private $price_change_percent;

    /**
     * @OA\Property(
     *     title="Timestamp",
     *     description="Event timestamp in ISO 8601 format",
     *     example="2025-01-15T10:30:00Z"
     * )
     *
     * @var string
     */
    private $timestamp;
}
