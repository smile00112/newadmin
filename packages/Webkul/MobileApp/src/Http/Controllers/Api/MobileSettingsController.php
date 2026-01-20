<?php

namespace Webkul\MobileApp\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\MobileApp\Repositories\MobileAppSettingRepository;
use Webkul\Payment\Payment;
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
     * Create a new controller instance.
     */
    public function __construct(
        protected MobileAppSettingRepository $settingRepository,
        protected AttributeRepository $attributeRepository,
        protected Payment $payment,
        protected Shipping $shipping
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
}

