<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Customer;

class CheckoutController
{
    /**
     * @OA\Post(
     *      path="/api/v1/customer/checkout/save-address",
     *      operationId="saveCheckoutAddress",
     *      tags={"Checkout"},
     *      summary="Save addresses at the checkout",
     *      description="Save addresses at the checkout",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\RequestBody(
     *
     *          @OA\MediaType(
     *              mediaType="application/json",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(
     *                      property="billing",
     *                      type="array",
     *                      description="",
     *                      example={
     *                          "id": 14,
     *                          "address": {"70 Winchester Rd"},
     *                          "save_as_address": false,
     *                          "use_for_shipping": false,
     *                          "first_name": "John",
     *                          "last_name": "Doe",
     *                          "email": "john@example.com",
     *                          "company_name": "Lovefood",
     *                          "city": "Marrero",
     *                          "state": "LA",
     *                          "country": "US",
     *                          "postcode": 70072,
     *                          "phone": 9871234560
     *                      },
     *
     *                      @OA\Items(
     *
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="address", type="array", @OA\Items(
     *                              @OA\Property(type="string")
     *                          )),
     *                          @OA\Property(property="save_as_address", type="boolean"),
     *                          @OA\Property(property="use_for_shipping", type="boolean"),
     *                          @OA\Property(property="first_name", type="string"),
     *                          @OA\Property(property="last_name", type="string"),
     *                          @OA\Property(property="email", type="string"),
     *                          @OA\Property(property="company_name", type="string"),
     *                          @OA\Property(property="city", type="string"),
     *                          @OA\Property(property="state", type="string"),
     *                          @OA\Property(property="country", type="string"),
     *                          @OA\Property(property="postcode", type="integer"),
     *                          @OA\Property(property="phone", type="integer")
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="shipping",
     *                      type="array",
     *                      description="",
     *                      example={
     *                          "id": null,
     *                          "address": {"819  Farnum Road"},
     *                          "save_as_address": false,
     *                          "first_name": "John",
     *                          "last_name": "Doe",
     *                          "email": "john@example.com",
     *                          "company_name": "Lovefood",
     *                          "city": "Mansfield",
     *                          "state": "OH",
     *                          "country": "US",
     *                          "postcode": 44907,
     *                          "phone": 987654321
     *                      },
     *
     *                      @OA\Items(
     *
     *                          @OA\Property(property="id", type="integer"),
     *                          @OA\Property(property="address", type="array", @OA\Items(
     *                              @OA\Property(type="string")
     *                          )),
     *                          @OA\Property(property="save_as_address", type="boolean"),
     *                          @OA\Property(property="first_name", type="string"),
     *                          @OA\Property(property="last_name", type="string"),
     *                          @OA\Property(property="email", type="string"),
     *                          @OA\Property(property="company_name", type="string"),
     *                          @OA\Property(property="city", type="string"),
     *                          @OA\Property(property="state", type="string"),
     *                          @OA\Property(property="country", type="string"),
     *                          @OA\Property(property="postcode", type="integer"),
     *                          @OA\Property(property="phone", type="integer")
     *                      )
     *                  ),
     *                  required={"billing"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Address saved successfully."
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(
     *
     *                      @OA\Property(
     *                          property="cart",
     *                          type="object",
     *                          ref="#/components/schemas/Cart"
     *                      ),
     *                      @OA\Property(
     *                          property="rates",
     *                          type="array",
     *
     *                          @OA\Items(
     *
     *                              @OA\Property(
     *                                  property="carrier_title",
     *                                  type="string",
     *                                  example="Flat Rate"
     *                              ),
     *                              @OA\Property(
     *                                  property="rates",
     *                                  type="object",
     *                                  ref="#/components/schemas/CartShippingRate"
     *                              )
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Quantity cannot be lesser than one."
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Something went wrong!"
     *      )
     * )
     */
    public function saveAddress() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/checkout/save-shipping",
     *      operationId="saveCheckoutShipping",
     *      tags={"Checkout"},
     *      summary="Save shipping method at the checkout",
     *      description="Save shipping method at the checkout",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\RequestBody(
     *
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(
     *                      property="shipping_method",
     *                      type="string",
     *                      example="flatrate_flatrate",
     *                  ),
     *                  required={"shipping_method"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Shipping method saved successfully."
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(
     *
     *                      @OA\Property(
     *                          property="cart",
     *                          type="object",
     *                          ref="#/components/schemas/Cart"
     *                      ),
     *                      @OA\Property(
     *                          property="methods",
     *                          type="object",
     *                          ref="#/components/schemas/CartPayment"
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Something went wrong!"
     *      )
     * )
     */
    public function saveShipping() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/checkout/save-payment",
     *      operationId="saveCheckoutPayment",
     *      tags={"Checkout"},
     *      summary="Save payment method at the checkout",
     *      description="Save payment method at the checkout. Payment can be a string (method code) or an object with method and optional saved_card_id for Alfabank. When method is alfabank and saved_card_id is provided, the saved card (from GET /api/v1/customer/saved-cards) will be used for payment. bindingId and clientId are then passed to the bank when the order is paid.",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\RequestBody(
     *
     *          @OA\MediaType(
     *              mediaType="application/json",
     *
     *              @OA\Schema(
     *                  oneOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="payment", type="string", example="cashondelivery", description="Payment method code (short form)")
     *                      ),
     *                      @OA\Schema(
     *                          @OA\Property(
     *                              property="payment",
     *                              type="object",
     *                              description="Payment object with method and optional saved card for Alfabank",
     *                              @OA\Property(property="method", type="string", example="alfabank", description="Payment method code"),
     *                              @OA\Property(property="saved_card_id", type="integer", example=1, nullable=true, description="ID of saved card from GET /api/v1/customer/saved-cards. Only for method=alfabank.")
     *                          )
     *                      )
     *                  },
     *                  required={"payment"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Payment method saved successfully."
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(
     *
     *                      @OA\Property(
     *                          property="cart",
     *                          type="object",
     *                          ref="#/components/schemas/Cart"
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Something went wrong!"
     *      )
     * )
     */
    public function savePayment() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/checkout/check-minimum-order",
     *      operationId="checkMinimumOrderAmount",
     *      tags={"Checkout"},
     *      summary="Check minimun order at the checkout",
     *      description="Check minimun order at the checkout",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Minimum order amount is $120."
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(
     *
     *                      @OA\Property(
     *                          property="cart",
     *                          type="object",
     *                          ref="#/components/schemas/Cart"
     *                      ),
     *                      @OA\Property(
     *                          property="status",
     *                          type="boolean",
     *                          example="true"
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Something went wrong!"
     *      )
     * )
     */
    public function checkMinimumOrder() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/checkout/save-order",
     *      operationId="saveCheckoutOrder",
     *      tags={"Checkout"},
     *      summary="Create order at the checkout",
     *      description="Create order at the checkout",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="order_labels",
     *                  type="array",
     *                  description="Array of order labels to attach to the order. Labels must exist in the configured list.",
     *                  @OA\Items(
     *                      type="string",
     *                      example="заказ с собой"
     *                  ),
     *                  example={"заказ с собой", "приготовить сдачу", "позвонить при прибытии заказа"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Order saved successfully."
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  description="Response data",
     *
     *                  @OA\Property(
     *                      property="order",
     *                      type="object",
     *                      ref="#/components/schemas/Order",
     *                      description="Created order"
     *                  ),
     *                  @OA\Property(
     *                      property="payment_url",
     *                      type="string",
     *                      nullable=true,
     *                      description="URL for redirect payment (Alfabank etc.). Present only when payment method requires redirect. Client should redirect user to this URL to complete payment.",
     *                      example="https://example.com/alfabank/payment/start?order_id=123"
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Something went wrong!"
     *      )
     * )
     */
    public function saveOrder() {}

    /**
     * @OA\Post(
     *      path="/api/v1/admin/sales/orders/bind-table",
     *      operationId="bindOrderTable",
     *      tags={"Checkout"},
     *      summary="Привязать номер стола к заказу",
     *      description="Bind table number to the order. Admin API - requires admin authentication.",
     *      security={ {"sanctum_admin": {} }},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"order_id", "table_number"},
     *              @OA\Property(
     *                  property="order_id",
     *                  type="integer",
     *                  format="int64",
     *                  description="Order ID",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="table_number",
     *                  type="integer",
     *                  format="int32",
     *                  minimum=1,
     *                  description="Table number to bind to the order",
     *                  example=5
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  ref="#/components/schemas/Order"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Table number bound to order successfully."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Order not found"
     *      ),
     *
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     */
    public function bindTable() {}

    /**
     * @OA\Delete(
     *      path="/api/v1/admin/sales/orders/bind-table",
     *      operationId="unbindOrderTable",
     *      tags={"Checkout"},
     *      summary="Отвязать номер стола от заказа",
     *      description="Unbind table number from the order. Admin API - requires admin authentication.",
     *      security={ {"sanctum_admin": {} }},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"order_id"},
     *              @OA\Property(
     *                  property="order_id",
     *                  type="integer",
     *                  format="int64",
     *                  description="Order ID",
     *                  example=1
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  ref="#/components/schemas/Order"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Table number unbound from order successfully."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Order not found"
     *      )
     * )
     */
    public function unbindTable() {}
}
