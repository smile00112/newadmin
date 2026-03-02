<?php

namespace Webkul\RestApi\Docs\Shop\Models\Customer;

/**
 * @OA\Schema(
 *     title="SavedCard",
 *     description="Saved card model (Alfabank)",
 * )
 */
class SavedCard
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="Saved card ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(
     *     title="Binding ID",
     *     description="Bank binding identifier for payment",
     *     example="8ac7a4a8-1234-5678-90ab-cdef12345678"
     * )
     *
     * @var string
     */
    private $binding_id;

    /**
     * @OA\Property(
     *     title="Card Mask",
     *     description="Masked card number",
     *     example="4276 **** **** 1234"
     * )
     *
     * @var string
     */
    private $card_mask;

    /**
     * @OA\Property(
     *     title="Card Type",
     *     description="Payment system (VISA, MasterCard, etc.)",
     *     example="VISA"
     * )
     *
     * @var string
     */
    private $card_type;

    /**
     * @OA\Property(
     *     title="Is Active",
     *     description="Whether the card is active",
     *     example=true
     * )
     *
     * @var bool
     */
    private $is_active;

    /**
     * @OA\Property(
     *     title="Created at",
     *     description="Created at",
     *     example="2026-02-19 12:00:00",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var string
     */
    private $created_at;

    /**
     * @OA\Property(
     *     title="Updated at",
     *     description="Updated at",
     *     example="2026-02-19 12:00:00",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var string
     */
    private $updated_at;
}
