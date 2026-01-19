<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\RestApi\Http\Controllers\V1\Shop\ShopController;
use Webkul\RestApi\Http\Resources\V1\Shop\Core\ConfigurationResource;
use Webkul\RestApi\Traits\ProvideApiCache;

class CoreController extends ShopController
{
    use ProvideApiCache;

    /**
     * Cache TTL in seconds (10 minutes).
     */
    protected int $cacheTtl = 600;

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
     * Get core configs (cached).
     *
     * Caches config values based on the requested config keys, channel, and locale.
     */
    public function getCoreConfigs(Request $request): \Illuminate\Http\Response
    {
        $this->validate($request, [
            '_config'   => 'required|array',
            '_config.*' => 'required|string',
        ]);

        $configKeys = $request->input('_config');
        $channelCode = core()->getRequestedChannelCode();
        $localeCode = core()->getRequestedLocaleCode();

        // Create a unique cache key based on requested configs
        sort($configKeys); // Ensure consistent order for cache key
        $cacheKey = 'configs:' . md5(implode('|', $configKeys) . ":{$channelCode}:{$localeCode}");

        $configValues = $this->cachedResponse($cacheKey, function () use ($configKeys) {
            $values = [];

            foreach ($configKeys as $config) {
                $values[$config] = core()->getConfigData($config);
            }

            return $values;
        });

        return response(['data' => $configValues]);
    }

    /**
     * Clear core config cache.
     */
    public static function clearConfigCache(): void
    {
        // Note: For full cache invalidation, consider using cache tags
        // or implementing a more sophisticated cache key management
    }
}
