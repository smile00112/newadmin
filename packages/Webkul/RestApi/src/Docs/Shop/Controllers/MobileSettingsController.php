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
     *      description="Returns mobile app configuration settings including app info, filters, custom data, and contact information",
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
     *                  ),
     *                  @OA\Property(
     *                      property="products_banners",
     *                      type="array",
     *                      description="Product banners from catalog.product_category_positions (product-category position mappings with product details)",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(
     *                              property="product",
     *                              type="object",
     *                              description="ProductReviewResource structure (id, title, comment, name, status, product, customer, created_at, updated_at)",
     *                              @OA\Property(property="id", type="integer", example=1, description="ID"),
     *                              @OA\Property(property="title", type="string", example="Great product", nullable=true, description="Title"),
     *                              @OA\Property(property="comment", type="string", example="Nice quality", nullable=true, description="Comment"),
     *                              @OA\Property(property="name", type="string", example="Customer Name", description="Name"),
     *                              @OA\Property(property="status", type="string", example="approved", description="Status"),
     *                              @OA\Property(
     *                                  property="product",
     *                                  type="object",
     *                                  description="Product details (ProductResource)",
     *                                  @OA\Property(property="id", type="integer", example=1),
     *                                  @OA\Property(property="sku", type="string", example="product-sku"),
     *                                  @OA\Property(property="name", type="string", example="Product Name"),
     *                                  @OA\Property(property="images", type="array", @OA\Items(type="object")),
     *                                  @OA\Property(property="price", type="number", example=99.99)
     *                              ),
     *                              @OA\Property(property="customer", type="object", nullable=true, description="Customer resource when customer_id is set"),
     *                              @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-15T10:30:00.000000Z"),
     *                              @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-15T10:30:00.000000Z")
     *                          ),
     *                          @OA\Property(property="category_id", type="integer", example=5, description="Category ID"),
     *                          @OA\Property(property="position_type", type="string", example="top", description="Position type: top, middle, bottom, numeric"),
     *                          @OA\Property(property="position_value", type="integer", nullable=true, example=null, description="Numeric position value when position_type is numeric")
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="contact_telegram",
     *                      type="string",
     *                      description="Telegram contact link",
     *                      example="https://t.me/your_contact",
     *                      nullable=true
     *                  ),
     *                  @OA\Property(
     *                      property="contact_whatsapp",
     *                      type="string",
     *                      description="WhatsApp contact link",
     *                      example="https://wa.me/1234567890",
     *                      nullable=true
     *                  ),
     *                  @OA\Property(
     *                      property="contact_email",
     *                      type="string",
     *                      description="Email address",
     *                      example="support@example.com",
     *                      nullable=true
     *                  ),
     *                  @OA\Property(
     *                      property="contact_max",
     *                      type="string",
     *                      description="Max Messenger contact link",
     *                      example="https://max.me/your_contact",
     *                      nullable=true
     *                  ),
     *                  @OA\Property(
     *                      property="contact_us",
     *                      type="object",
     *                      description="Contact information in structured format",
     *                      @OA\Property(
     *                          property="telegram",
     *                          type="string",
     *                          description="Telegram contact link",
     *                          example="https://t.me/your_contact",
     *                          nullable=true
     *                      ),
     *                      @OA\Property(
     *                          property="whatsapp",
     *                          type="string",
     *                          description="WhatsApp contact link",
     *                          example="https://wa.me/1234567890",
     *                          nullable=true
     *                      ),
     *                      @OA\Property(
     *                          property="email",
     *                          type="string",
     *                          description="Email address",
     *                          example="support@example.com",
     *                          nullable=true
     *                      ),
     *                      @OA\Property(
     *                          property="max",
     *                          type="string",
     *                          description="Max Messenger contact link",
     *                          example="https://max.me/your_contact",
     *                          nullable=true
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="documents",
     *                      type="object",
     *                      description="Document links for mobile app",
     *                      @OA\Property(
     *                          property="user_agreement",
     *                          type="string",
     *                          description="URL to get HTML content of user agreement page",
     *                          example="http://example.com/api/v1/cms/1/html",
     *                          nullable=true
     *                      ),
     *                      @OA\Property(
     *                          property="privacy_policy",
     *                          type="string",
     *                          description="URL to get HTML content of privacy policy page",
     *                          example="http://example.com/api/v1/cms/2/html",
     *                          nullable=true
     *                      )
     *                  )
 *                  ,
 *                  @OA\Property(
 *                      property="push",
 *                      type="object",
 *                      description="Push notifications configuration for the mobile app (client-safe, no secrets)",
 *                      @OA\Property(
 *                          property="provider",
 *                          type="string",
 *                          example="fcm",
 *                          description="Push provider identifier"
 *                      ),
 *                      @OA\Property(
 *                          property="enabled",
 *                          type="boolean",
 *                          example=true,
 *                          description="Whether push notifications are enabled on the server"
 *                      ),
 *                      @OA\Property(
 *                          property="statuses",
 *                          type="array",
 *                          description="Order statuses that should trigger push notifications",
 *                          @OA\Items(type="string", example="processing")
 *                      )
 *                  ),
 *                  @OA\Property(
 *                      property="sockets",
 *                      type="object",
 *                      description="WebSocket connection configuration (Pusher-compatible protocol)",
 *                      @OA\Property(
 *                          property="server",
 *                          type="object",
 *                          description="WebSocket server configuration",
 *                          @OA\Property(
 *                              property="url",
 *                              type="string",
 *                              example="wss://your-domain.com/app",
 *                              description="WebSocket server URL"
 *                          ),
 *                          @OA\Property(
 *                              property="protocol",
 *                              type="string",
 *                              example="pusher",
 *                              description="WebSocket protocol (Pusher-compatible)"
 *                          ),
 *                          @OA\Property(
 *                              property="key",
 *                              type="string",
 *                              example="your-app-key",
 *                              description="WebSocket application key"
 *                          ),
 *                          @OA\Property(
 *                              property="host",
 *                              type="string",
 *                              example="your-domain.com",
 *                              description="WebSocket host"
 *                          ),
 *                          @OA\Property(
 *                              property="port",
 *                              type="integer",
 *                              example=443,
 *                              description="WebSocket port"
 *                          ),
 *                          @OA\Property(
 *                              property="path",
 *                              type="string",
 *                              example="/app",
 *                              description="WebSocket path"
 *                          )
 *                      ),
 *                      @OA\Property(
 *                          property="auth_endpoint",
 *                          type="object",
 *                          description="Endpoint for authorizing private channels (used by Echo/Pusher)",
 *                          @OA\Property(property="url", type="string", example="http://example.com/api/v1/broadcasting/auth"),
 *                          @OA\Property(property="method", type="string", example="POST"),
 *                          @OA\Property(property="requires_auth", type="boolean", example=true)
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


