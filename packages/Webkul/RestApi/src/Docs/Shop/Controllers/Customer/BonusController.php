<?php

namespace Webkul\RestApi\Docs\Shop\Controllers\Customer;

class BonusController
{
    /**
     * @OA\Get(
     *      path="/api/v1/customer/bonuses",
     *      operationId="getCustomerBonuses",
     *      tags={"Bonuses"},
     *      summary="Get logged in customer's bonus information",
     *      description="Returns customer's bonus balance, levels, history and settings",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="points_balance",
     *                  type="number",
     *                  format="float",
     *                  description="Available bonus balance (not expired)",
     *                  example=450.50
     *              ),
     *
     *              @OA\Property(
     *                  property="balance",
     *                  type="number",
     *                  format="float",
     *                  description="Total bonus balance",
     *                  example=500.00
     *              ),
     *
     *              @OA\Property(
     *                  property="spent_sum",
     *                  type="number",
     *                  format="float",
     *                  description="Total amount spent by customer",
     *                  example=15000.00
     *              ),
     *
     *              @OA\Property(
     *                  property="orders_count",
     *                  type="integer",
     *                  description="Number of completed orders",
     *                  example=25
     *              ),
     *
     *              @OA\Property(
     *                  property="percent_max",
     *                  type="number",
     *                  format="float",
     *                  description="Maximum percentage of order that can be paid with bonuses",
     *                  example=100.00
     *              ),
     *
     *              @OA\Property(
     *                  property="show_levels_info",
     *                  type="boolean",
     *                  description="Whether to show levels information",
     *                  example=true
     *              ),
     *
     *              @OA\Property(
     *                  property="level",
     *                  type="integer",
     *                  nullable=true,
     *                  description="Current bonus level ID",
     *                  example=2
     *              ),
     *
     *              @OA\Property(
     *                  property="type",
     *                  type="string",
     *                  description="Calculation type for current level",
     *                  enum={"orders_count", "total_spent", "cart_value"},
     *                  example="total_spent"
     *              ),
     *
     *              @OA\Property(
     *                  property="levels_info",
     *                  type="array",
     *                  description="List of all bonus levels",
     *
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="Bronze"),
     *                      @OA\Property(property="cashback_percent", type="integer", example=2),
     *                      @OA\Property(property="description_top", type="string", example="Возвращаем 2% заказа."),
     *                      @OA\Property(property="description_bottom", type="string", example="0-1000 потрачено"),
     *                      @OA\Property(property="is_current", type="boolean", example=false)
     *                  )
     *              ),
     *
     *              @OA\Property(
     *                  property="next_level_info",
     *                  type="object",
     *                  nullable=true,
     *                  description="Information about next level",
     *                  @OA\Property(property="text1", type="string", example="Вы сделали 1000 потрачено"),
     *                  @OA\Property(property="text2", type="string", example="Осталось 500 потрачено до повышения")
     *              ),
     *
     *              @OA\Property(
     *                  property="bonus_history",
     *                  type="array",
     *                  description="Recent bonus transactions",
     *
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="type", type="string", enum={"accrual", "deduction", "return"}, example="accrual"),
     *                      @OA\Property(property="amount", type="number", format="float", example=50.00),
     *                      @OA\Property(property="currency_code", type="string", example="USD"),
     *                      @OA\Property(property="description", type="string", example="Начисление бонусов за заказ #00001"),
     *                      @OA\Property(property="order_id", type="integer", nullable=true, example=1),
     *                      @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-22 10:00:00")
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *      path="/api/v1/customer/bonuces-test",
     *      operationId="getCustomerBonusesTest",
     *      tags={"Bonuses"},
     *      summary="Get test bonus information (for testing)",
     *      description="Returns test bonus data for development/testing purposes",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="points_balance",
     *                  type="number",
     *                  format="float",
     *                  example=450.00
     *              ),
     *
     *              @OA\Property(
     *                  property="spent_sum",
     *                  type="number",
     *                  format="float",
     *                  example=0.00
     *              ),
     *
     *              @OA\Property(
     *                  property="percent_max",
     *                  type="number",
     *                  format="float",
     *                  example=10.00
     *              ),
     *
     *              @OA\Property(
     *                  property="show_levels_info",
     *                  type="boolean",
     *                  example=true
     *              ),
     *
     *              @OA\Property(
     *                  property="level",
     *                  type="integer",
     *                  example=2
     *              ),
     *
     *              @OA\Property(
     *                  property="type",
     *                  type="string",
     *                  example="orderscount"
     *              ),
     *
     *              @OA\Property(
     *                  property="levels_info",
     *                  type="array",
     *                  description="Test levels data",
     *
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="1 грейд"),
     *                      @OA\Property(property="cashback_percent", type="integer", example=2),
     *                      @OA\Property(property="description_top", type="string", example="Возвращаем 2% заказа."),
     *                      @OA\Property(property="description_bottom", type="string", example="0-10 заказов")
     *                  )
     *              ),
     *
     *              @OA\Property(
     *                  property="next_level_info",
     *                  type="object",
     *                  @OA\Property(property="text1", type="string", example="Вы сделалли 12 заказов"),
     *                  @OA\Property(property="text2", type="string", example="Осталось 2 закзаза до  до повышения")
     *              ),
     *
     *              @OA\Property(
     *                  property="bonus_history",
     *                  type="array",
     *                  description="Bonus transaction history",
     *
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="type", type="string", enum={"accrual", "deduction", "return"}, example="accrual"),
     *                      @OA\Property(property="amount", type="number", format="float", example=50.00),
     *                      @OA\Property(property="currency_code", type="string", example="USD"),
     *                      @OA\Property(property="description", type="string", example="Начисление бонусов за заказ #00001"),
     *                      @OA\Property(property="order_id", type="integer", nullable=true, example=1),
     *                      @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-22 10:00:00")
     *                  ),
     *                  example={}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     */
    public function test() {}

    /**
     * @OA\Post(
     *      path="/api/checkout/bonus/apply",
     *      operationId="applyBonusToCart",
     *      tags={"Checkout"},
     *      summary="Apply bonus to cart",
     *      description="Apply bonus amount to the current customer's cart. Requires authenticated customer with active cart.",
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\MediaType(
     *              mediaType="application/json",
     *
     *              @OA\Schema(
     *
     *                  @OA\Property(
     *                      property="amount",
     *                      type="number",
     *                      format="float",
     *                      description="Bonus amount to apply (must be >= 0)",
     *                      example=100.50,
     *                      minimum=0
     *                  ),
     *                  required={"amount"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Bonus applied successfully",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Бонусы успешно применены"
     *              ),
     *              @OA\Property(
     *                  property="cart",
     *                  type="object",
     *                  description="Updated cart object with applied bonus"
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad request - validation error or business logic error",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=false
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Корзина не найдена или пользователь не авторизован"
     *              )
     *          )
     *      )
     * )
     */
    public function applyBonus() {}
}
