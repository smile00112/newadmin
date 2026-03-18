<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Customer;

class AlfabankSettingsController
{
    /**
     * @OA\Get(
     *      path="/api/v1/customer/alfabank/settings",
     *      operationId="getCustomerAlfabankSettings",
     *      tags={"Customers"},
     *      summary="Get Alfabank payment settings",
     *      description="Returns full Alfabank payment method configuration for the current sales channel as stored in core_config under sales.payment_methods.alfabank.*. Endpoint is protected by customer sanctum token.",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="active", type="string", example="1", description="Whether the payment method is active (\"1\" or \"0\")."),
     *                  @OA\Property(property="title", type="string", example="Альфа-Банк", description="Localized title of the payment method."),
     *                  @OA\Property(property="description", type="string", nullable=true, example="Оплата картой через Альфа-Банк", description="Localized description of the payment method."),
     *                  @OA\Property(property="merchant", type="string", example="1234567890", description="Alfabank merchant identifier."),
     *                  @OA\Property(property="password", type="string", example="secret-password", description="Technical password for Alfabank merchant."),
     *                  @OA\Property(property="token", type="string", example="secret-token", description="Token for Alfabank merchant."),
     *                  @OA\Property(property="test_mode", type="string", example="1", description="Flag indicating test mode (\"1\" or \"0\")."),
     *                  @OA\Property(property="stage_mode", type="string", example="one-stage", description="Stage mode for payments (one-stage or two-stage)."),
     *                  @OA\Property(property="order_status_paid", type="string", example="processing", description="Order status code used when payment is completed."),
     *                  @OA\Property(property="success_url", type="string", nullable=true, example="https://example.com/alfabank/success", description="Custom success redirect URL."),
     *                  @OA\Property(property="fail_url", type="string", nullable=true, example="https://example.com/alfabank/fail", description="Custom fail redirect URL."),
     *                  @OA\Property(property="send_order", type="string", example="0", description="Whether order line items are sent to the bank (\"1\" or \"0\")."),
     *                  @OA\Property(property="tax_system", type="string", example="0", description="Tax system code."),
     *                  @OA\Property(property="tax_type", type="string", example="0", description="Default tax type code."),
     *                  @OA\Property(property="version_ffd", type="string", example="v1_05", description="FFD version (fiscal data format)."),
     *                  @OA\Property(property="payment_method_type", type="string", example="4", description="Payment method type code for receipts."),
     *                  @OA\Property(property="payment_object_type", type="string", example="1", description="Payment object type code for products."),
     *                  @OA\Property(property="payment_object_type_delivery", type="string", example="1", description="Payment object type code for delivery."),
     *                  @OA\Property(property="saved_cards_payment_enable", type="string", example="0", description="Flag indicating whether saved cards payment is enabled (\"1\" or \"0\")."),
     *                  @OA\Property(property="min_order_total", type="string", nullable=true, example="0", description="Minimum order total for which the method is available."),
     *                  @OA\Property(property="max_order_total", type="string", nullable=true, example="10000", description="Maximum order total for which the method is available."),
     *                  @OA\Property(property="callback_type", type="string", example="STATIC", description="Callback URL type (STATIC or DYNAMIC).")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function show() {}
}

