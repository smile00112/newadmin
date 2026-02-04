<?php

namespace Webkul\RestApi\Docs\Shop\Models\WebSocket;

/**
 * @OA\Schema(
 *     title="ProductStatusChanged",
 *     description="WebSocket event payload when product status changes to out of stock",
 *     type="object"
 * )
 */
class ProductStatusChanged
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
     *     title="Status",
     *     description="Product status",
     *     example="out_of_stock"
     * )
     *
     * @var string
     */
    private $status;

    /**
     * @OA\Property(
     *     title="Previous Quantity",
     *     description="Previous quantity before change",
     *     format="int64",
     *     example=10
     * )
     *
     * @var int
     */
    private $previous_qty;

    /**
     * @OA\Property(
     *     title="Current Quantity",
     *     description="Current quantity after change",
     *     format="int64",
     *     example=0
     * )
     *
     * @var int
     */
    private $current_qty;

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
