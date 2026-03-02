<?php

namespace Webkul\RestApi\Docs\Shop\Models\WebSocket;

/**
 * @OA\Schema(
 *     title="OrderNotification",
 *     description="WebSocket event payload for order notifications (create/update)",
 *     type="object"
 * )
 */
class OrderNotification
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="Order ID",
     *     format="int64",
     *     example=123
     * )
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(
     *     title="Status",
     *     description="Order status (for update-notification event)",
     *     example="processing",
     *     nullable=true
     * )
     *
     * @var string|null
     */
    private $status;
}
