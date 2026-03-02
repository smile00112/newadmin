<?php

namespace Webkul\RestApi\Docs\Shop\Controllers;


/**
 * @OA\Get(
 *      path="/api/v1/websocket/info",
 *      operationId="getWebSocketInfo",
 *      tags={"WebSocket Notifications"},
 *      summary="Get WebSocket connection information",
 *      description="Returns WebSocket server connection details, available channels and events for real-time notifications.
 *      
 *      ## WebSocket Connection
 *      
 *      The API supports real-time notifications via WebSocket using Laravel Reverb (Pusher-compatible protocol).
 *      
 *      ### Connection Details
 *      - **Protocol**: Pusher-compatible WebSocket
 *      - **Server**: Laravel Reverb
 *      - **Channels**: Public and Private channels available
 *      
 *      ### Channel Types
 *      
 *      #### Public Channels
 *      - **notification**: Public channel for general notifications (no authentication required)
 *      
 *      #### Private Channels (Authentication Required)
 *      - **order.{orderId}**: Private channel for specific order updates (e.g., `order.123`)
 *      - **customer.{customerId}.orders**: Private channel for all orders of a specific customer (e.g., `customer.456.orders`)
 *      
 *      ### Available Events
 *      - **create-notification**: Fired when a new order is created (public channel)
 *      - **update-notification**: Fired when an order status is updated (private channels)
 *      - **product-status-changed**: Fired when a product becomes out of stock (public channel)
 *      - **product-price-changed**: Fired when a product price changes (public channel)
 *      
 *      ### Authentication for Private Channels
 *      
 *      Private channels require authentication. The authorization endpoint is:
 *      - **POST** `/api/v1/customer/broadcasting/auth`
 *      
 *      This endpoint requires authentication token in the Authorization header.
 *      
 *      ### Example Connection (JavaScript)
 *      ```javascript
 *      import Echo from 'laravel-echo';
 *      import Pusher from 'pusher-js';
 *      
 *      window.Echo = new Echo({
 *          broadcaster: 'pusher',
 *          key: process.env.VITE_REVERB_APP_KEY,
 *          wsHost: process.env.VITE_REVERB_HOST,
 *          wsPort: process.env.VITE_REVERB_PORT ?? 80,
 *          wssPort: process.env.VITE_REVERB_PORT ?? 443,
 *          wsPath: '/app',
 *          forceTLS: (process.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
 *          enabledTransports: ['ws', 'wss'],
 *          authEndpoint: '/api/v1/customer/broadcasting/auth',
 *          auth: {
 *              headers: {
 *                  Authorization: 'Bearer ' + yourAuthToken
 *              }
 *          }
 *      });
 *      
 *      // Public channel - no authentication required
 *      Echo.channel('notification')
 *          .listen('.product-status-changed', (e) => {
 *              console.log('Product out of stock:', e);
 *          })
 *          .listen('.product-price-changed', (e) => {
 *              console.log('Price changed:', e);
 *          })
 *          .listen('.create-notification', (e) => {
 *              console.log('New order created:', e);
 *          });
 *      
 *      // Private channel for specific order
 *      Echo.private('order.123')
 *          .listen('.update-notification', (e) => {
 *              console.log('Order updated:', e);
 *          });
 *      
 *      // Private channel for all customer orders
 *      Echo.private('customer.456.orders')
 *          .listen('.update-notification', (e) => {
 *              console.log('Order status changed:', e);
 *          });
 *      ```
 *      ",
 *
 *      @OA\Response(
 *          response=200,
 *          description="WebSocket connection information",
 *
 *          @OA\JsonContent(
 *              @OA\Property(
 *                  property="server",
 *                  type="object",
 *                  description="WebSocket server configuration",
 *                  @OA\Property(
 *                      property="url",
 *                      type="string",
 *                      example="wss://your-domain.com/app",
 *                      description="WebSocket server URL"
 *                  ),
 *                  @OA\Property(
 *                      property="protocol",
 *                      type="string",
 *                      example="pusher",
 *                      description="WebSocket protocol (Pusher-compatible)"
 *                  ),
 *                  @OA\Property(
 *                      property="key",
 *                      type="string",
 *                      example="your-app-key",
 *                      description="Application key (available in VITE_REVERB_APP_KEY)"
 *                  ),
 *                  @OA\Property(
 *                      property="host",
 *                      type="string",
 *                      example="your-domain.com",
 *                      description="WebSocket host"
 *                  ),
 *                  @OA\Property(
 *                      property="port",
 *                      type="integer",
 *                      example=443,
 *                      description="WebSocket port (443 for HTTPS, 80 for HTTP)"
 *                  ),
 *                  @OA\Property(
 *                      property="path",
 *                      type="string",
 *                      example="/app",
 *                      description="WebSocket path"
 *                  )
 *              ),
 *              @OA\Property(
 *                  property="channels",
 *                  type="array",
 *                  description="Available WebSocket channels",
 *                  @OA\Items(
 *                      type="object",
 *                      @OA\Property(
 *                          property="name",
 *                          type="string",
 *                          example="notification"
 *                      ),
 *                      @OA\Property(
 *                          property="type",
 *                          type="string",
 *                          enum={"public", "private"},
 *                          example="public",
 *                          description="Channel type"
 *                      ),
 *                      @OA\Property(
 *                          property="description",
 *                          type="string",
 *                          example="Main notification channel for all real-time updates"
 *                      ),
 *                      @OA\Property(
 *                          property="requires_auth",
 *                          type="boolean",
 *                          example=false,
 *                          description="Whether authentication is required"
 *                      ),
 *                      @OA\Property(
 *                          property="events",
 *                          type="array",
 *                          description="Available events on this channel",
 *                          @OA\Items(
 *                              type="object",
 *                              @OA\Property(
 *                                  property="name",
 *                                  type="string",
 *                                  example="product-status-changed"
 *                              ),
 *                              @OA\Property(
 *                                  property="description",
 *                                  type="string",
 *                                  example="Fired when a product becomes out of stock"
 *                              ),
 *                              @OA\Property(
 *                                  property="payload_schema",
 *                                  type="string",
 *                                  example="#/components/schemas/ProductStatusChanged"
 *                              )
 *                          )
 *                      )
 *                  )
 *              ),
 *              @OA\Property(
 *                  property="private_channels",
 *                  type="array",
 *                  description="Available private WebSocket channels (require authentication)",
 *                  @OA\Items(
 *                      type="object",
 *                      @OA\Property(
 *                          property="pattern",
 *                          type="string",
 *                          example="order.{orderId}",
 *                          description="Channel name pattern"
 *                      ),
 *                      @OA\Property(
 *                          property="description",
 *                          type="string",
 *                          example="Private channel for specific order updates"
 *                      ),
 *                      @OA\Property(
 *                          property="example",
 *                          type="string",
 *                          example="order.123",
 *                          description="Example channel name"
 *                      ),
 *                      @OA\Property(
 *                          property="events",
 *                          type="array",
 *                          description="Available events on this channel",
 *                          @OA\Items(
 *                              type="object",
 *                              @OA\Property(
 *                                  property="name",
 *                                  type="string",
 *                                  example="update-notification"
 *                              ),
 *                              @OA\Property(
 *                                  property="description",
 *                                  type="string",
 *                                  example="Fired when order status is updated"
 *                              )
 *                          )
 *                      )
 *                  )
 *              ),
 *              @OA\Property(
 *                  property="auth_endpoint",
 *                  type="object",
 *                  description="Authorization endpoint for private channels",
 *                  @OA\Property(
 *                      property="url",
 *                      type="string",
 *                      example="/api/v1/customer/broadcasting/auth"
 *                  ),
 *                  @OA\Property(
 *                      property="method",
 *                      type="string",
 *                      example="POST"
 *                  ),
 *                  @OA\Property(
 *                      property="requires_auth",
 *                      type="boolean",
 *                      example=true,
 *                      description="Requires Bearer token in Authorization header"
 *                  )
 *              ),
 *              @OA\Property(
 *                  property="example_code",
 *                  type="object",
 *                  description="Example code snippets for connecting",
 *                  @OA\Property(
 *                      property="javascript",
 *                      type="string",
 *                      example="import Echo from 'laravel-echo';\nimport Pusher from 'pusher-js';\n\nwindow.Echo = new Echo({\n    broadcaster: 'pusher',\n    key: 'your-app-key',\n    wsHost: 'your-domain.com',\n    wsPort: 443,\n    wssPort: 443,\n    wsPath: '/app',\n    forceTLS: true,\n    enabledTransports: ['ws', 'wss'],\n});\n\nEcho.channel('notification')\n    .listen('.product-status-changed', (e) => {\n        console.log('Product out of stock:', e);\n    })\n    .listen('.product-price-changed', (e) => {\n        console.log('Price changed:', e);\n    });"
 *                  )
 *              )
 *          )
 *      )
 * )
 */
class WebSocketController
{
    /**
     * @OA\Get(
     *      path="/api/v1/websocket/events",
 *      operationId="getWebSocketEvents",
 *      tags={"WebSocket Notifications"},
 *      summary="Get list of available WebSocket events",
 *      description="Returns detailed information about all available WebSocket events with their payload schemas",
 *
     *      @OA\Response(
     *          response=200,
     *          description="List of WebSocket events",
     *
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="public_events",
     *                  type="array",
     *                  description="Events available on public channels",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(
     *                          property="channel",
     *                          type="string",
     *                          example="notification"
     *                      ),
     *                      @OA\Property(
     *                          property="event_name",
     *                          type="string",
     *                          example="product-status-changed"
     *                      ),
     *                      @OA\Property(
     *                          property="full_event_name",
     *                          type="string",
     *                          example=".product-status-changed",
     *                          description="Full event name to use in Echo.listen()"
     *                      ),
     *                      @OA\Property(
     *                          property="description",
     *                          type="string",
     *                          example="Fired when a product becomes out of stock"
     *                      ),
     *                      @OA\Property(
     *                          property="payload",
     *                          oneOf={
     *                              @OA\Schema(ref="#/components/schemas/ProductStatusChanged"),
     *                              @OA\Schema(ref="#/components/schemas/ProductPriceChanged"),
     *                              @OA\Schema(ref="#/components/schemas/OrderNotification")
     *                          }
     *                      )
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="private_events",
     *                  type="array",
     *                  description="Events available on private channels",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(
     *                          property="channel_pattern",
     *                          type="string",
     *                          example="order.{orderId}"
     *                      ),
     *                      @OA\Property(
     *                          property="channel_example",
     *                          type="string",
     *                          example="order.123"
     *                      ),
     *                      @OA\Property(
     *                          property="event_name",
     *                          type="string",
     *                          example="update-notification"
     *                      ),
     *                      @OA\Property(
     *                          property="full_event_name",
     *                          type="string",
     *                          example=".update-notification",
     *                          description="Full event name to use in Echo.listen()"
     *                      ),
     *                      @OA\Property(
     *                          property="description",
     *                          type="string",
     *                          example="Fired when order status is updated"
     *                      ),
     *                      @OA\Property(
     *                          property="requires_auth",
     *                          type="boolean",
     *                          example=true
     *                      ),
     *                      @OA\Property(
     *                          property="payload",
     *                          ref="#/components/schemas/OrderNotification"
     *                      )
     *                  )
     *              )
     *          )
     *      )
 * )
 */
    public function events() {}

    /**
     * @OA\Post(
     *      path="/api/v1/customer/broadcasting/auth",
     *      operationId="authorizeBroadcastingChannel",
     *      tags={"WebSocket Notifications"},
     *      summary="Authorize private WebSocket channel",
     *      description="Authorizes access to private WebSocket channels. This endpoint is called automatically by Laravel Echo when subscribing to private channels.
 *      
 *      ## Private Channels
 *      
 *      Private channels require authentication and authorization. When a client attempts to subscribe to a private channel, Laravel Echo automatically sends a POST request to this endpoint.
 *      
 *      ### Available Private Channels
 *      
 *      1. **order.{orderId}** - Private channel for specific order updates
 *         - Pattern: `order.{orderId}` (e.g., `order.123`)
 *         - Authorization: User must be the owner of the order
 *         - Events: `update-notification`
 *      
 *      2. **customer.{customerId}.orders** - Private channel for all customer orders
 *         - Pattern: `customer.{customerId}.orders` (e.g., `customer.456.orders`)
 *         - Authorization: User ID must match the customerId
 *         - Events: `update-notification`
 *      
 *      ### Authorization Logic
 *      
 *      - **order.{orderId}**: Checks if the authenticated user is the owner of the order
 *      - **customer.{customerId}.orders**: Checks if the authenticated user ID matches the customerId
 *      
 *      ### Usage
 *      
 *      This endpoint is called automatically by Laravel Echo. You don't need to call it manually.
 *      
 *      ```javascript
 *      // Laravel Echo automatically handles authorization
 *      Echo.private('order.123')
 *          .listen('.update-notification', (e) => {
 *              console.log('Order updated:', e);
 *          });
 *      ```
 *      ",
     *      security={ {"sanctum": {} }},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="Channel authorization request",
     *
     *          @OA\MediaType(
     *              mediaType="application/json",
     *
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="socket_id",
     *                      type="string",
     *                      example="123.456",
     *                      description="Socket ID from Pusher/Echo"
     *                  ),
     *                  @OA\Property(
     *                      property="channel_name",
     *                      type="string",
     *                      example="private-order.123",
     *                      description="Channel name (prefixed with 'private-' for private channels)"
     *                  ),
     *                  required={"socket_id", "channel_name"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Channel authorized successfully",
     *
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="auth",
     *                  type="string",
     *                  example="your-app-key:signature",
     *                  description="Authorization signature"
     *              ),
     *              @OA\Property(
     *                  property="channel_data",
     *                  type="object",
     *                  description="Channel data (for presence channels)",
     *                  nullable=true
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=403,
     *          description="Channel authorization denied",
     *
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Access denied to channel"
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Unauthenticated"
     *              )
     *          )
     *      )
     * )
     */
    public function authorizeChannel() {}
}
