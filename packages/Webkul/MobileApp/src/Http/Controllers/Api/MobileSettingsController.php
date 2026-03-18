<?php

namespace Webkul\MobileApp\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Core\Facades\Core;
use Webkul\Core\Models\CoreConfig;
use Webkul\MobileApp\Repositories\MobileAppSettingRepository;
use Webkul\Payment\Payment;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\RestApi\Support\WebSocketConfig;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\ProductResource;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\ProductReviewResource;
use Webkul\Sales\Models\Order;
use Webkul\Shipping\Shipping;

class MobileSettingsController extends Controller
{
    /**
     * Cache TTL in seconds (10 minutes).
     */
    protected const CACHE_TTL = 600;

    /**
     * Cache key prefix.
     */
    protected const CACHE_PREFIX = 'mobile_settings_response';

    /**
     * Config code for product category positions (product banners).
     */
    private const PRODUCT_CATEGORY_POSITIONS_CONFIG = 'catalog.product_category_positions';

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected MobileAppSettingRepository $settingRepository,
        protected AttributeRepository $attributeRepository,
        protected Payment $payment,
        protected Shipping $shipping,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Get all mobile app settings.
     */
    public function index(): JsonResponse
    {
        $channelCode = request('channel', core()->getDefaultChannelCode());

        $data = Cache::remember(
            $this->buildCacheKey($channelCode),
            self::CACHE_TTL,
            fn () => $this->buildSettingsData($channelCode)
        );

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Build settings data for caching.
     */
    protected function buildSettingsData(string $channelCode): array
    {
        $settings = $this->settingRepository->getAllSettings($channelCode);

        // Add core config values
        $settings['app_name'] = $settings['app_name']
            ?? core()->getConfigData('mobile_app.general.app_info.app_name');
        $settings['app_version'] = $settings['app_version']
            ?? core()->getConfigData('mobile_app.general.app_info.app_version');
        $settings['min_app_version'] = $settings['min_app_version']
            ?? core()->getConfigData('mobile_app.general.app_info.min_app_version');
        $settings['force_update'] = (bool) ($settings['force_update']
            ?? core()->getConfigData('mobile_app.general.app_info.force_update'));
        $settings['maintenance_mode'] = (bool) ($settings['maintenance_mode']
            ?? core()->getConfigData('mobile_app.general.app_info.maintenance_mode'));
        $settings['custom_data'] = $settings['custom_data']
            ?? core()->getConfigData('mobile_app.general.custom.custom_data');

        // Expand home_filters with attribute options
        if (!empty($settings['home_filters'])) {
            $settings['home_filters'] = $this->expandHomeFilters($settings['home_filters']);
        }

        // Add shipping methods
        $settings['shipping_methods'] = $this->shipping->getShippingMethods();

        // Add payment methods
        $settings['payment_methods'] = $this->payment->getPaymentMethods();

        // Add order labels
        $labelsList = core()->getConfigData('sales.order_settings.order_labels.labels_list', $channelCode);
        if ($labelsList) {
            $settings['order_labels'] = array_filter(
                array_map('trim', explode("\n", $labelsList)),
                fn($label) => !empty($label)
            );
        } else {
            $settings['order_labels'] = [];
        }

        // Add order statuses
        $settings['order_statuses'] = $this->getOrderStatuses();

        // Add cart cross-sell products
        $settings['cart_cross_sell_products'] = $this->getCartCrossSellProducts();

        // Add product banners from catalog.product_category_positions
        $settings['products_banners'] = $this->getProductsBanners();

        // Add contact us information in structured format
        $settings['contact_us'] = [
            'telegram' => $settings['contact_telegram'] ?? '',
            'whatsapp' => $settings['contact_whatsapp'] ?? '',
            'email'    => $settings['contact_email'] ?? '',
            'max'      => $settings['contact_max'] ?? '',
        ];

        // Add document links
        $userAgreementId = $settings['user_agreement'] ?? null;
        $privacyPolicyId = $settings['privacy_policy'] ?? null;

        $settings['documents'] = [
            'user_agreement' => $userAgreementId
                ? url('/api/v1/cms/' . $userAgreementId . '/html')
                : '',
            'privacy_policy' => $privacyPolicyId
                ? url('/api/v1/cms/' . $privacyPolicyId . '/html')
                : '',
        ];

        $pushEnabled = (bool) core()->getConfigData('mobile_app.push_notifications.settings.enabled');
        $pushStatuses = core()->getConfigData('mobile_app.push_notifications.settings.statuses');

        if (is_string($pushStatuses)) {
            $pushStatuses = array_filter(array_map('trim', explode(',', $pushStatuses)));
        }

        if (! is_array($pushStatuses)) {
            $pushStatuses = [];
        }

        $settings['push'] = [
            'provider' => 'fcm',
            'enabled'  => $pushEnabled,
            'statuses' => array_values(Arr::flatten($pushStatuses)),
        ];

        $settings['sockets'] = [
            'server'        => WebSocketConfig::server(request()),
            'auth_endpoint' => [
                'url'           => url('/api/v1/broadcasting/auth'),
                'method'        => 'POST',
                'requires_auth' => true,
            ],
        ];

        return $settings;
    }

    /**
     * Build cache key for channel.
     */
    protected function buildCacheKey(string $channelCode): string
    {
        return self::CACHE_PREFIX . ':' . $channelCode;
    }

    /**
     * Clear cache for a specific channel or all channels.
     */
    public static function clearCache(?string $channelCode = null): void
    {
        if ($channelCode) {
            Cache::forget(self::CACHE_PREFIX . ':' . $channelCode);
        } else {
            // Clear for default channel
            Cache::forget(self::CACHE_PREFIX . ':' . core()->getDefaultChannelCode());

            // Clear for current channel if different
            $currentChannel = core()->getCurrentChannelCode();
            if ($currentChannel !== core()->getDefaultChannelCode()) {
                Cache::forget(self::CACHE_PREFIX . ':' . $currentChannel);
            }
        }
    }

    /**
     * Warm mobile-settings cache for all channels.
     */
    public static function warmCache(): void
    {
        $channels = Core::getAllChannels();

        foreach ($channels as $channel) {
            $channelCode = $channel->code ?? (string) $channel->id;
            if (empty($channelCode)) {
                continue;
            }

            try {
                $controller = app(self::class);
                $controller->warmChannel($channelCode);
            } catch (\Throwable $e) {
                Log::warning('Failed to warm mobile-settings cache', [
                    'channel' => $channelCode,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Build and cache mobile-settings data for a specific channel.
     */
    public function warmChannel(string $channelCode): void
    {
        $originalChannel = core()->getCurrentChannel();
        $channel = Core::getAllChannels()->first(fn ($c) => ($c->code ?? (string) $c->id) === $channelCode);

        if ($channel) {
            core()->setCurrentChannel($channel);
        }

        try {
            $data = $this->buildSettingsData($channelCode);
            Cache::put($this->buildCacheKey($channelCode), $data, self::CACHE_TTL);
        } finally {
            if ($originalChannel) {
                core()->setCurrentChannel($originalChannel);
            }
        }
    }

    /**
     * Expand home filters with attribute details and options.
     */
    protected function expandHomeFilters(array $attributeCodes): array
    {
        $filters = [];

        foreach ($attributeCodes as $code) {
            $attribute = $this->attributeRepository->findOneByField('code', $code);

            if (!$attribute) {
                continue;
            }

            $filter = [
                'code'       => $attribute->code,
                'name'       => $attribute->admin_name ?? $attribute->code,
                'type'       => $attribute->type,
                'options'    => [],
            ];

            // Add options if attribute has them
            if ($attribute->options && $attribute->options->count() > 0) {
                $filter['options'] = $attribute->options->map(function ($option) {
                    return [
                        'id'    => $option->id,
                        'code'  => $option->admin_name ?? $option->id,
                        'label' => $option->label ?? $option->admin_name,
                    ];
                })->toArray();
            }

            $filters[] = $filter;
        }

        return $filters;
    }

    /**
     * Get all available order statuses from database.
     * Now reads from order_statuses table, so any custom statuses will be included automatically.
     */
    protected function getOrderStatuses(): array
    {
        try {
            // Get all statuses from DB ordered by sort_order
            $dbStatuses = \Webkul\Sales\Models\OrderStatus::orderBy('sort_order')->get();

            $result = [];
            foreach ($dbStatuses as $status) {
                // Try to get translation first, fallback to DB name if translation doesn't exist
                $label = trans('shop::app.customers.account.orders.status.options.' . str_replace('_', '-', $status->code));
                
                // If translation key still contains the key itself, use the DB name instead
                if (strpos($label, 'shop::app.customers.account.orders.status.options.') !== false) {
                    $label = $status->name;
                }

                $result[] = [
                    'code'  => $status->code,
                    'label' => $label,
                    'icon'  => $status->icon,   // New: icon from DB
                    'color' => $status->color,  // New: color from DB
                ];
            }

            return $result;
        } catch (\Exception $e) {
            // Fallback if table doesn't exist (e.g., during migration)
            return [
                ['code' => 'pending', 'label' => 'Новый', 'icon' => 'hourglass-top', 'color' => '#f59e0b'],
                ['code' => 'pending_payment', 'label' => 'Ожидание оплаты', 'icon' => 'credit-card', 'color' => '#f59e0b'],
                ['code' => 'processing', 'label' => 'Обработка', 'icon' => 'arrow-repeat', 'color' => '#3b82f6'],
                ['code' => 'preparing', 'label' => 'Готовим', 'icon' => 'fire', 'color' => '#6366f1'],
                ['code' => 'ready', 'label' => 'Готов', 'icon' => 'check2-circle', 'color' => '#10b981'],
                ['code' => 'completed', 'label' => 'Выполнен', 'icon' => 'check-circle-fill', 'color' => '#22c55e'],
                ['code' => 'canceled', 'label' => 'Отменён', 'icon' => 'x-circle', 'color' => '#ef4444'],
            ];
        }
    }

    /**
     * Get cart cross-sell products from configuration.
     */
    protected function getCartCrossSellProducts(): array
    {
        // Проверяем, включен ли отдельный список
        $useSeparateList = core()->getConfigData('catalog.products.cart_view_page.separate_cross_sell_list');

        if ($useSeparateList) {
            // Используем отдельный список из конфигурации
            $productIds = core()->getConfigData('catalog.products.cart_view_page.cart_cross_sell_products');

            if (is_string($productIds)) {
                $productIds = explode(',', $productIds);
            }

            if (empty($productIds) || ! is_array($productIds)) {
                return [];
            }

            $products = $this->productRepository
                ->whereIn('id', $productIds)
                ->take(core()->getConfigData('catalog.products.cart_view_page.no_of_cross_sells_products'))
                ->get();

            return ProductResource::collection($products)->resolve();
        }

        // Если отдельный список не включен, возвращаем пустой массив
        // так как кросс-сейлы зависят от товаров в корзине пользователя
        return [];
    }

    /**
     * Get product banners from catalog.product_category_positions config.
     *
     * @return array<int, array{product: array, category_id: int, position_type: string, position_value: int|null}>
     */
    protected function getProductsBanners(): array
    {
        $config = CoreConfig::where('code', self::PRODUCT_CATEGORY_POSITIONS_CONFIG)
            ->whereNull('channel_code')
            ->whereNull('locale_code')
            ->first();

        if (! $config || empty($config->value)) {
            return [];
        }

        $mappings = json_decode($config->value, true);

        if (! is_array($mappings)) {
            return [];
        }

        $productIds = array_unique(array_filter(array_column($mappings, 'product_id')));

        if (empty($productIds)) {
            return [];
        }

        $products = $this->productRepository
            ->whereIn('id', $productIds)
            ->with('images')
            ->get()
            ->keyBy('id');

        $result = [];

        foreach ($mappings as $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            $categoryId = (int) ($row['category_id'] ?? 0);

            if ($productId <= 0 || $categoryId <= 0) {
                continue;
            }

            $product = $products->get($productId);

            if (! $product) {
                continue;
            }

            $result[] = [
                'product'        => ProductReviewResource::make($product)->resolve(),
                'category_id'    => $categoryId,
                'position_type'  => $row['position_type'] ?? 'middle',
                'position_value' => isset($row['position_value']) ? (int) $row['position_value'] : null,
            ];
        }

        return $result;
    }
}

