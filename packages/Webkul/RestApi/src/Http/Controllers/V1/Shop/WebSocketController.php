<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Core\ConfigurationResource;

class WebSocketController extends ShopController
{
    /**
     * Is resource authorized.
     */
    public function isAuthorized(): bool
    {
        return false;
    }

    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return CoreConfigRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return ConfigurationResource::class;
    }

    /**
     * Get WebSocket connection information.
     */
    public function info(Request $request): JsonResponse
    {
        $server = $this->getWebSocketServerConfig($request);

        $channels = [
            [
                'name'          => 'notification',
                'type'          => 'public',
                'description'   => 'Main notification channel for all real-time updates',
                'requires_auth' => false,
                'events'        => [
                    [
                        'name'           => 'create-notification',
                        'description'    => 'Fired when a new order is created',
                        'payload_schema' => null,
                    ],
                    [
                        'name'           => 'product-status-changed',
                        'description'    => 'Fired when a product becomes out of stock',
                        'payload_schema' => '#/components/schemas/ProductStatusChanged',
                    ],
                    [
                        'name'           => 'product-price-changed',
                        'description'    => 'Fired when a product price changes',
                        'payload_schema' => '#/components/schemas/ProductPriceChanged',
                    ],
                ],
            ],
        ];

        $privateChannels = [
            [
                'pattern'     => 'order.{orderId}',
                'description' => 'Private channel for specific order updates',
                'example'     => 'order.123',
                'events'      => [
                    [
                        'name'        => 'update-notification',
                        'description' => 'Fired when order status is updated',
                    ],
                ],
            ],
            [
                'pattern'     => 'customer.{customerId}.orders',
                'description' => 'Private channel for all orders of a specific customer',
                'example'     => 'customer.456.orders',
                'events'      => [
                    [
                        'name'        => 'update-notification',
                        'description' => 'Fired when order status is updated',
                    ],
                ],
            ],
        ];

        $authEndpoint = url('/api/v1/broadcasting/auth');

        $exampleCode = "import Echo from 'laravel-echo';\nimport Pusher from 'pusher-js';\n\nwindow.Echo = new Echo({\n"
            . "    broadcaster: 'pusher',\n"
            . "    key: '" . ($server['key'] ?? 'your-app-key') . "',\n"
            . "    wsHost: '" . ($server['host'] ?? 'your-domain.com') . "',\n"
            . "    wsPort: " . ($server['port'] ?? 443) . ",\n"
            . "    wssPort: " . ($server['port'] ?? 443) . ",\n"
            . "    wsPath: '/app',\n"
            . "    forceTLS: true,\n"
            . "    enabledTransports: ['ws', 'wss'],\n"
            . "});\n\n"
            . "Echo.channel('notification')\n"
            . "    .listen('.product-status-changed', (e) => {\n"
            . "        console.log('Product out of stock:', e);\n"
            . "    })\n"
            . "    .listen('.product-price-changed', (e) => {\n"
            . "        console.log('Price changed:', e);\n"
            . "    });";

        return response()->json([
            'server'          => $server,
            'channels'        => $channels,
            'private_channels' => $privateChannels,
            'auth_endpoint'   => [
                'url'            => $authEndpoint,
                'method'         => 'POST',
                'requires_auth'  => true,
            ],
            'example_code' => [
                'javascript' => $exampleCode,
            ],
        ]);
    }

    /**
     * Get list of available WebSocket events.
     */
    public function events(): JsonResponse
    {
        $productStatusPayload = [
            'product_id'   => ['type' => 'integer', 'description' => 'Product ID'],
            'sku'          => ['type' => 'string', 'description' => 'Product SKU'],
            'name'         => ['type' => 'string', 'description' => 'Product name'],
            'status'       => ['type' => 'string', 'description' => 'Product status (e.g. out_of_stock)'],
            'previous_qty' => ['type' => 'integer', 'description' => 'Previous quantity before change'],
            'current_qty'  => ['type' => 'integer', 'description' => 'Current quantity after change'],
            'timestamp'    => ['type' => 'string', 'description' => 'Event timestamp in ISO 8601 format'],
        ];

        $productPricePayload = [
            'product_id'         => ['type' => 'integer', 'description' => 'Product ID'],
            'sku'                => ['type' => 'string', 'description' => 'Product SKU'],
            'name'               => ['type' => 'string', 'description' => 'Product name'],
            'previous_price'     => ['type' => 'number', 'description' => 'Previous price before change'],
            'current_price'      => ['type' => 'number', 'description' => 'Current price after change'],
            'price_change'       => ['type' => 'number', 'description' => 'Absolute price change amount'],
            'price_change_percent' => ['type' => 'number', 'description' => 'Percentage change in price'],
            'timestamp'          => ['type' => 'string', 'description' => 'Event timestamp in ISO 8601 format'],
        ];

        $orderNotificationPayload = [
            'id'          => ['type' => 'integer', 'description' => 'Order ID'],
            'status'      => ['type' => 'string', 'description' => 'Order status'],
            'customer_id' => ['type' => 'integer', 'nullable' => true, 'description' => 'Customer ID'],
            'updated_at'  => ['type' => 'string', 'description' => 'Update timestamp in ISO 8601 format'],
        ];

        $publicEvents = [
            [
                'channel'          => 'notification',
                'event_name'       => 'create-notification',
                'full_event_name'  => '.create-notification',
                'description'      => 'Fired when a new order is created',
                'payload'          => [],
            ],
            [
                'channel'          => 'notification',
                'event_name'       => 'product-status-changed',
                'full_event_name'  => '.product-status-changed',
                'description'      => 'Fired when a product becomes out of stock',
                'payload'          => $productStatusPayload,
            ],
            [
                'channel'          => 'notification',
                'event_name'       => 'product-price-changed',
                'full_event_name'  => '.product-price-changed',
                'description'      => 'Fired when a product price changes',
                'payload'          => $productPricePayload,
            ],
        ];

        $privateEvents = [
            [
                'channel_pattern'  => 'order.{orderId}',
                'channel_example'  => 'order.123',
                'event_name'       => 'update-notification',
                'full_event_name'  => '.update-notification',
                'description'      => 'Fired when order status is updated',
                'requires_auth'    => true,
                'payload'          => $orderNotificationPayload,
            ],
            [
                'channel_pattern'  => 'customer.{customerId}.orders',
                'channel_example'  => 'customer.456.orders',
                'event_name'       => 'update-notification',
                'full_event_name'  => '.update-notification',
                'description'      => 'Fired when order status is updated',
                'requires_auth'    => true,
                'payload'          => $orderNotificationPayload,
            ],
        ];

        return response()->json([
            'public_events'  => $publicEvents,
            'private_events' => $privateEvents,
        ]);
    }

    /**
     * Get WebSocket server configuration from config or env.
     */
    protected function getWebSocketServerConfig(Request $request): array
    {
        $key = Config::get('reverb.apps.0.key')
            ?? env('REVERB_APP_KEY')
            ?? env('PUSHER_APP_KEY')
            ?? 'your-app-key';

        $host = Config::get('reverb.apps.0.host')
            ?? env('REVERB_HOST')
            ?? $this->parseHostFromAppUrl()
            ?? $request->getHost();

        $port = Config::get('reverb.apps.0.port')
            ?? env('REVERB_PORT')
            ?? ($request->secure() ? 443 : 80);

        $scheme = Config::get('reverb.apps.0.scheme')
            ?? env('REVERB_SCHEME')
            ?? ($request->secure() ? 'https' : 'http');

        $path = '/app';

        $wsScheme = $scheme === 'https' ? 'wss' : 'ws';
        $portPart = in_array((int) $port, [80, 443], true) ? '' : ":{$port}";
        $url = "{$wsScheme}://{$host}{$portPart}{$path}";

        return [
            'url'      => $url,
            'protocol' => 'pusher',
            'key'      => $key,
            'host'     => $host,
            'port'     => (int) $port,
            'path'     => $path,
        ];
    }

    /**
     * Parse host from APP_URL env variable.
     */
    protected function parseHostFromAppUrl(): ?string
    {
        $appUrl = env('APP_URL');

        if (empty($appUrl)) {
            return null;
        }

        $parsed = parse_url($appUrl);

        return $parsed['host'] ?? null;
    }
}
