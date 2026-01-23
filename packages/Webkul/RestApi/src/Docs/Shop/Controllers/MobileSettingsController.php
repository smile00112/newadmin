<?php

namespace Webkul\RestApi\Docs\Shop\Controllers;

class MobileSettingsController
{
    /**
     * @OA\Get(
     *      path="/api/v1/mobile-settings",
     *      operationId="getMobileAppSettings",
     *      tags={"Mobile App"},
     *      summary="Get mobile app settings",
     *      description="Returns mobile app configuration settings including app info, filters, and custom data",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\Parameter(
     *          name="channel",
     *          description="Channel code (optional, defaults to default channel)",
     *          required=false,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string",
     *              example="default"
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
     *                  @OA\Property(
     *                      property="app_name",
     *                      type="string",
     *                      description="Application name",
     *                      example="My Store App"
     *                  ),
     *                  @OA\Property(
     *                      property="app_version",
     *                      type="string",
     *                      description="Current app version",
     *                      example="1.0.0"
     *                  ),
     *                  @OA\Property(
     *                      property="min_app_version",
     *                      type="string",
     *                      description="Minimum required app version",
     *                      example="1.0.0"
     *                  ),
     *                  @OA\Property(
     *                      property="force_update",
     *                      type="boolean",
     *                      description="Whether to force app update",
     *                      example=false
     *                  ),
     *                  @OA\Property(
     *                      property="maintenance_mode",
     *                      type="boolean",
     *                      description="Whether app is in maintenance mode",
     *                      example=false
     *                  ),
     *                  @OA\Property(
     *                      property="custom_data",
     *                      type="string",
     *                      description="Custom JSON data",
     *                      example="{}"
     *                  ),
     *                  @OA\Property(
     *                      property="home_filters",
     *                      type="array",
     *                      description="Filters for home screen with attribute options",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(
     *                              property="code",
     *                              type="string",
     *                              description="Attribute code",
     *                              example="color"
     *                          ),
     *                          @OA\Property(
     *                              property="name",
     *                              type="string",
     *                              description="Attribute name",
     *                              example="Color"
     *                          ),
     *                          @OA\Property(
     *                              property="type",
     *                              type="string",
     *                              description="Attribute type",
     *                              example="select"
     *                          ),
     *                          @OA\Property(
     *                              property="options",
     *                              type="array",
     *                              description="Available options for this attribute",
     *                              @OA\Items(
     *                                  type="object",
     *                                  @OA\Property(property="id", type="integer", example=1),
     *                                  @OA\Property(property="code", type="string", example="red"),
     *                                  @OA\Property(property="label", type="string", example="Red")
     *                              )
     *                          )
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="featured_categories",
     *                      type="array",
     *                      description="Featured category IDs",
     *                      @OA\Items(type="integer", example=1)
     *                  ),
     *                  @OA\Property(
     *                      property="featured_products",
     *                      type="array",
     *                      description="Featured product IDs",
     *                      @OA\Items(type="integer", example=1)
     *                  ),
     *                  @OA\Property(
     *                      property="shipping_methods",
     *                      type="array",
     *                      description="Available shipping methods",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="code", type="string", example="flatrate"),
     *                          @OA\Property(property="method", type="string", example="flatrate_flatrate"),
     *                          @OA\Property(property="method_title", type="string", example="Flat Rate"),
     *                          @OA\Property(property="description", type="string", example="Flat rate shipping")
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="payment_methods",
     *                      type="array",
     *                      description="Available payment methods",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="method", type="string", example="cashondelivery"),
     *                          @OA\Property(property="method_title", type="string", example="Cash On Delivery"),
     *                          @OA\Property(property="description", type="string", example="Pay when you receive your order"),
     *                          @OA\Property(property="sort", type="integer", example=1),
     *                          @OA\Property(property="image", type="string", nullable=true, example=null)
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="order_labels",
     *                      type="array",
     *                      description="Order labels list",
     *                      @OA\Items(type="string", example="VIP")
     *                  ),
     *                  @OA\Property(
     *                      property="order_statuses",
     *                      type="array",
     *                      description="Available order statuses",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="code", type="string", example="pending", description="Status code"),
     *                          @OA\Property(property="label", type="string", example="Pending", description="Status label")
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="cart_cross_sell_products",
     *                      type="array",
     *                      description="Cart cross-sell products from configuration (only if separate cross-sell list is enabled)",
     *                      @OA\Items(
     *                          type="object",
     *                          description="Product resource with full product details",
     *                          @OA\Property(property="id", type="integer", example=1, description="Product ID"),
     *                          @OA\Property(property="sku", type="string", example="product-sku", description="Product SKU"),
     *                          @OA\Property(property="name", type="string", example="Product Name", description="Product name"),
     *                          @OA\Property(property="price", type="number", format="float", example=99.99, description="Product price"),
     *                          @OA\Property(property="formatted_price", type="string", example="$99.99", description="Formatted price"),
     *                          @OA\Property(property="images", type="array", description="Product images", @OA\Items(type="string")),
     *                          @OA\Property(property="in_stock", type="boolean", example=true, description="Product availability"),
     *                          @OA\Property(property="is_saleable", type="boolean", example=true, description="Whether product can be sold"),
     *                          @OA\Property(property="url_key", type="string", example="product-url-key", description="Product URL key")
     *                      )
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
}


