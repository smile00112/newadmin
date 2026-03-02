<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Catalog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Webkul\Product\Models\Product;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\NomenclatureIngredientResource;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\NomenclatureProductResource;
use Webkul\RestApi\Traits\ProvideApiCache;

class NomenclatureController extends CatalogController
{
    use ProvideApiCache;

    /**
     * Cache key prefix for nomenclature.
     */
    public const CACHE_PREFIX = 'api_nomenclature';

    /**
     * Cache TTL in seconds (10 minutes - nomenclature may change).
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
     * Returns nomenclature with products and ingredients (cached).
     */
    public function index(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $channel = core()->getCurrentChannel();
        $channelCode = $channel->code ?? (string) $channel->id;
        $channelId = $channel->id;
        $locale = core()->getRequestedLocaleCode();

        $cacheKey = self::CACHE_PREFIX . ":{$channelId}:{$locale}";

        $jsonResponse = Cache::remember($cacheKey, $this->cacheTtl, function () use ($channelCode, $channelId, $locale, $request) {
            $products = $this->getProducts($channelCode, $locale);
            $ingredients = $this->getIngredients($channelCode, $locale);

            $data = [
                'products'    => NomenclatureProductResource::collection($products)->resolve($request),
                'ingredients' => NomenclatureIngredientResource::collection($ingredients)->resolve($request),
            ];

            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });

        return api_stream_json($jsonResponse, 'nomenclature.json');
    }

    /**
     * Get products (excluding ingredients).
     */
    protected function getProducts(string $channelCode, string $locale)
    {
        return Product::with(['images', 'attribute_family', 'price_indices', 'inventory_indices'])
            ->whereHas('product_flats', function ($query) use ($channelCode, $locale) {
                $query->where('channel', $channelCode)
                    ->where('locale', $locale)
                    ->where('status', 1)
                    ->where('visible_individually', 1);
            })
            ->whereNotIn('type', ['ingredient'])
            ->whereNull('parent_id')
            ->orderBy('id')
            ->get();
    }

    /**
     * Get ingredients.
     */
    protected function getIngredients(string $channelCode, string $locale)
    {
        return Product::with(['images', 'attribute_family', 'price_indices', 'inventory_indices', 'videos'])
            ->whereHas('product_flats', function ($query) use ($channelCode, $locale) {
                $query->where('channel', $channelCode)
                    ->where('locale', $locale)
                    ->where('status', 1);
            })
            ->where('type', 'ingredient')
            ->orderBy('id')
            ->get();
    }

    /**
     * Clear all nomenclature cache entries.
     */
    public static function clearNomenclatureCache(): void
    {
        try {
            $store = Cache::getStore();
            $storeClass = get_class($store);

            if (method_exists($store, 'getRedis')) {
                try {
                    $redis = $store->getRedis();
                    $cachePrefix = method_exists($store, 'getPrefix')
                        ? $store->getPrefix()
                        : config('cache.prefix', 'laravel_cache');
                    $basePrefix = rtrim($cachePrefix, ':');
                    $searchPattern = $basePrefix . ':' . self::CACHE_PREFIX . '*';

                    $allKeys = [];
                    $cursor = 0;

                    do {
                        $result = $redis->scan($cursor, ['match' => $searchPattern, 'count' => 100]);
                        $cursor = is_array($result) ? ($result[0] ?? 0) : 0;
                        $keys = is_array($result) ? ($result[1] ?? []) : [];

                        if (! empty($keys)) {
                            $allKeys = array_merge($allKeys, $keys);
                        }
                    } while ($cursor > 0);

                    if (empty($allKeys)) {
                        try {
                            $keys = $redis->keys($searchPattern);
                            if (is_array($keys) && ! empty($keys)) {
                                $allKeys = $keys;
                            }
                        } catch (\Exception $e) {
                            Log::debug('KEYS command not available: ' . $e->getMessage());
                        }
                    }

                    $allKeys = array_unique($allKeys);

                    if (! empty($allKeys)) {
                        $chunks = array_chunk($allKeys, 100);
                        foreach ($chunks as $chunk) {
                            $redis->del($chunk);
                        }
                        Log::info('Cleared ' . count($allKeys) . ' nomenclature cache keys', [
                            'pattern'      => $searchPattern,
                            'keys_count'   => count($allKeys),
                            'store_class'  => $storeClass,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to clear Redis nomenclature cache: ' . $e->getMessage(), [
                        'store_class' => $storeClass,
                        'exception'   => get_class($e),
                    ]);
                }

                return;
            }

            if ($store instanceof \Illuminate\Cache\FileStore) {
                try {
                    $channels = \Webkul\Core\Facades\Core::getAllChannels();
                    $locales = \Webkul\Core\Facades\Core::getAllLocales();

                    foreach ($channels as $channel) {
                        foreach ($locales as $locale) {
                            $cacheKey = self::CACHE_PREFIX . ":{$channel->id}:{$locale->code}";
                            Cache::forget($cacheKey);
                        }
                    }

                    Log::info('Cleared nomenclature cache entries using Cache::forget()', [
                        'driver'       => 'file',
                        'store_class'  => $storeClass,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to clear nomenclature cache for file driver: ' . $e->getMessage(), [
                        'store_class' => $storeClass,
                        'exception'   => get_class($e),
                    ]);
                }

                return;
            }

            if ($store instanceof \Illuminate\Cache\DatabaseStore) {
                try {
                    $connection = $store->getConnection();
                    $table = $store->getTable();
                    $cachePrefix = config('cache.prefix', 'laravel_cache');
                    $pattern = $cachePrefix . ':' . self::CACHE_PREFIX . '%';
                    $deleted = $connection->table($table)
                        ->where('key', 'like', $pattern)
                        ->delete();

                    Log::info('Cleared ' . $deleted . ' nomenclature cache entries from database', [
                        'table'       => $table,
                        'pattern'     => $pattern,
                        'store_class' => $storeClass,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to clear nomenclature cache from database: ' . $e->getMessage(), [
                        'store_class' => $storeClass,
                        'exception'   => get_class($e),
                    ]);
                }

                return;
            }

            if (method_exists($store, 'tags')) {
                try {
                    Cache::tags([self::CACHE_PREFIX])->flush();
                    Log::info('Cleared nomenclature cache using tags', ['store_class' => $storeClass]);
                } catch (\Exception $e) {
                    Log::warning('Failed to clear nomenclature cache using tags: ' . $e->getMessage(), [
                        'store_class' => $storeClass,
                        'exception'   => get_class($e),
                    ]);
                }

                return;
            }

            Log::warning('Nomenclature cache cannot be cleared automatically for this cache driver. Please clear cache manually.', [
                'store_class' => $storeClass,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear nomenclature cache: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);
        }
    }
}
