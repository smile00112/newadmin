<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Customer;

class OrderController
{
    /**
     * @OA\Get(
     *      path="/api/v1/customer/orders",
     *      operationId="getCustomerOrders",
     *      tags={"Orders"},
     *      summary="Get logged in customer's orders",
     *      description="Returns order list, if you want to retrieve all orders at once pass pagination=0 otherwise ignore this parameter",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="Order id",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="sort",
     *          description="Sort column",
     *          example="id",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="order",
     *          description="Sort order",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              enum={"desc", "asc"}
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="limit",
     *          description="Limit",
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
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
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(ref="#/components/schemas/Order")
     *              ),
     *
     *              @OA\Property(
     *                  property="meta",
     *                  ref="#/components/schemas/Pagination"
     *              )
     *          )
     *      )
     * )
     */
    public function list() {}

    /**
     * @OA\Get(
     *      path="/api/v1/customer/orders/{id}",
     *      operationId="getCustomerOrderDetail",
     *      tags={"Orders"},
     *      summary="Get customer's order by id",
     *      description="Returns customer's order by id",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="Order id",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="integer"
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
     *                  property="data",
     *                  type="object",
     *                  ref="#/components/schemas/Order"
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
    public function get() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/orders/{id}/cancel",
     *      operationId="cancelCustomerOrder",
     *      tags={"Orders"},
     *      summary="Cancel customer's order by id",
     *      description="Cancel customer's order by id",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="Order id",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="integer"
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
     *                  example="Order canceled successfully."
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
    public function cancel() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/orders/{id}/refund",
     *      operationId="refundCustomerOrder",
     *      tags={"Orders"},
     *      summary="Refund customer's paid order via Alfabank",
     *      description="Sends refund request to Alfabank for customer's order. If `amount` is omitted, full refund is requested (`amount=0` in gateway minimal currency units).",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="Order id",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="amount",
     *                  type="number",
     *                  format="float",
     *                  nullable=true,
     *                  description="Refund amount in major currency units. If omitted, full refund is requested.",
     *                  example=12.5
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Refund request accepted by gateway",
     *
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Refund request has been sent successfully."
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="order_id", type="integer", example=101),
     *                  @OA\Property(property="gateway_order_id", type="string", example="01491d0b-c848-7dd6-a20d-e96900a7d8c0"),
     *                  @OA\Property(property="requested_amount", type="number", format="float", nullable=true, example=12.5),
     *                  @OA\Property(property="gateway_amount", type="integer", example=1250),
     *                  @OA\Property(property="gateway_response", type="object")
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Order not found",
     *
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Order not found."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=422,
     *          description="Validation or business rule error",
     *
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Alfabank refund error: Gateway rejected refund"
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Failed to process refund request."
     *              )
     *          )
     *      )
     * )
     */
    public function refund() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/orders/{id}/confirm-payment",
     *      operationId="confirmCustomerOrderPayment",
     *      tags={"Orders"},
     *      summary="Confirm payment for customer's order via Alfabank gateway",
     *      description="Mobile app sends payment confirmation request. `id` is backend order.id, `gateway_order_id` is Alfa mdOrder. Backend verifies payment in gateway and does not trust client status.",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="Order id (backend order.id)",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"gateway_order_id"},
     *              @OA\Property(
     *                  property="gateway_order_id",
     *                  type="string",
     *                  description="Alfabank gateway order id (mdOrder)",
     *                  example="00afdd1b-a6ab-75cf-b5f1-fe720257a540"
     *              ),
     *              example={"gateway_order_id":"00afdd1b-a6ab-75cf-b5f1-fe720257a540"}
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Payment confirmed and invoice created",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="order",
     *                      type="object",
     *                      ref="#/components/schemas/Order"
     *                  ),
     *                  @OA\Property(
     *                      property="invoice_id",
     *                      type="integer",
     *                      nullable=true,
     *                      description="Created invoice id (if any)",
     *                      example=123
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Payment has been confirmed."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=422,
     *          description="Payment not confirmed by gateway or invalid order state",
     *
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Payment was not confirmed by the gateway."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Order not found",
     *
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Order not found."
     *              )
     *          )
     *      )
     * )
     */
    public function confirmPayment() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/orders/{id}/rate",
     *      operationId="rateCustomerOrder",
     *      tags={"Orders"},
     *      summary="Rate customer's order",
     *      description="Rate customer's order (true = Нравится, false = Не нравится)",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="Order id",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"rating"},
     *              @OA\Property(
     *                  property="rating",
     *                  type="boolean",
     *                  description="Order rating (true = Нравится, false = Не нравится)",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="rating_comment",
     *                  type="string",
     *                  description="Optional comment for the rating",
     *                  example="Отличное обслуживание, все быстро и качественно!",
     *                  nullable=true
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
     *                  property="data",
     *                  type="object",
     *                  ref="#/components/schemas/Order"
     *              ),
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Order rated successfully."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Order not found",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Order not found."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="The given data was invalid."
     *              ),
     *
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="rating",
     *                      type="array",
     *                      @OA\Items(
     *                          type="string",
     *                          example="The rating field is required."
     *                      )
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function rate() {}

    /**
     * @OA\Get(
     *      path="/api/v1/customer/orders/reorder/{id}",
     *      operationId="ReOrder",
     *      tags={"Orders"},
     *      summary="Customer Reorder",
     *      description="This endpoint adds order items to the customer's cart for reordering. After successfully adding items to the cart, the customer should proceed with the checkout process via the checkout APIs.",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="Order id",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="integer"
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
     *                  property="data",
     *                  type="object",
     *                  ref="#/components/schemas/Cart"
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Something went wrong!",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *                  example="404 Page Not Found"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Oops! The page you're looking for is on vacation. It seems we couldn't find what you were searching for."
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=405,
     *          description="Method not allowed",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Order can not be reordered"
     *              )
     *          )
     *      ),
     * )
     */
    public function reorder() {}

    /**
     * @OA\Get(
     *      path="/api/v1/customer/active-orders",
     *      operationId="getCustomerActiveOrders",
     *      tags={"Orders"},
     *      summary="Get logged in customer's active orders",
     *      description="Returns list of active orders filtered by statuses configured in order settings (/admin/configuration/sales/order_settings). If you want to retrieve all orders at once pass pagination=0 otherwise ignore this parameter",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="sort",
     *          description="Sort column",
     *          example="id",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="order",
     *          description="Sort order",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              enum={"desc", "asc"}
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="limit",
     *          description="Limit",
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="pagination",
     *          description="Enable pagination (0 to disable, 1 or omit to enable)",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer",
     *              enum={0, 1}
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
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(ref="#/components/schemas/Order")
     *              ),
     *
     *              @OA\Property(
     *                  property="meta",
     *                  ref="#/components/schemas/Pagination"
     *              )
     *          )
     *      )
     * )
     */
    public function activeOrders() {}

    /**
     * @OA\Get(
     *      path="/api/v1/customer/completed-orders",
     *      operationId="getCustomerCompletedOrders",
     *      tags={"Orders"},
     *      summary="Get logged in customer's completed orders",
     *      description="Returns list of completed orders filtered by statuses configured in order settings (/admin/configuration/sales/order_settings). If you want to retrieve all orders at once pass pagination=0 otherwise ignore this parameter",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="sort",
     *          description="Sort column",
     *          example="id",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="order",
     *          description="Sort order",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              enum={"desc", "asc"}
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="limit",
     *          description="Limit",
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="pagination",
     *          description="Enable pagination (0 to disable, 1 or omit to enable)",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer",
     *              enum={0, 1}
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
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(ref="#/components/schemas/Order")
     *              ),
     *
     *              @OA\Property(
     *                  property="meta",
     *                  ref="#/components/schemas/Pagination"
     *              )
     *          )
     *      )
     * )
     */
    public function completedOrders() {}

    /**
     * @OA\Get(
     *      path="/api/v1/customer/cancelled-orders",
     *      operationId="getCustomerCancelledOrders",
     *      tags={"Orders"},
     *      summary="Get logged in customer's cancelled orders",
     *      description="Returns list of cancelled orders filtered by statuses configured in order settings (/admin/configuration/sales/order_settings). If you want to retrieve all orders at once pass pagination=0 otherwise ignore this parameter",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="sort",
     *          description="Sort column",
     *          example="id",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="order",
     *          description="Sort order",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              enum={"desc", "asc"}
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="limit",
     *          description="Limit",
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Parameter(
     *          name="pagination",
     *          description="Enable pagination (0 to disable, 1 or omit to enable)",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer",
     *              enum={0, 1}
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
     *                  property="data",
     *                  type="array",
     *
     *                  @OA\Items(ref="#/components/schemas/Order")
     *              ),
     *
     *              @OA\Property(
     *                  property="meta",
     *                  ref="#/components/schemas/Pagination"
     *              )
     *          )
     *      )
     * )
     */
    public function cancelledOrders() {}
}
