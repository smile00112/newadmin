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
     *      description="Returns only required Alfabank payment settings for the customer, taken from core_config under sales.payment_methods.alfabank.*.",
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
     *                  @OA\Property(
     *                      property="ALFA_PAY_BASE_URL",
     *                      type="string",
     *                      example="https://pay.alfabank.ru/payment/rest/",
     *                      description="Base URL AlfaPay API (override from `alfa_pay_base_url`, fallback by test_mode)."
     *                  ),
     *                  @OA\Property(property="GATEWAY_USERNAME", type="string", example="1234567890", description="Alfabank merchant identifier."),
     *                  @OA\Property(property="GATEWAY_PASSWORD", type="string", example="secret-password", description="Alfabank technical password."),
     *                  @OA\Property(property="GATEWAY_CLIENT_ID", type="string", example="secret-token", description="Alfabank token / client id."),
     *                  @OA\Property(property="GATEWAY_RETURN_URL", type="string", nullable=true, example="sdk://done", description="Return URL after successful payment."),
     *                  @OA\Property(property="GATEWAY_FAIL_URL", type="string", nullable=true, example="sdk://done", description="Return URL after failed payment.")
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

