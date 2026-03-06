<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Catalog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\CatalogResource;
use Webkul\RestApi\Traits\ProvideApiCache;

class CatalogCategoryController extends CatalogController
{
    use ProvideApiCache;

    /**
     * Cache key prefix for catalog.
     */
    public const CACHE_PREFIX = 'api_catalog';

    /**
     * Cache TTL in seconds (10 minutes - catalog may change).
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
        return CategoryRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return CatalogResource::class;
    }

    /**
     * Returns a listing of catalog categories (cached with pagination).
     */
    public function allResources(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $channelId = core()->getCurrentChannel()->id;
        $locale = core()->getRequestedLocaleCode();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $usePagination = is_null($request->input('pagination')) || $request->input('pagination');

        $cacheKey = self::CACHE_PREFIX . ":{$channelId}:{$locale}:page_{$page}:limit_{$limit}:paginated_" . ($usePagination ? '1' : '0');

        $jsonResponse = Cache::remember($cacheKey, $this->cacheTtl, function () use ($request, $usePagination, $limit) {

            $query = $this->getRepositoryInstance()
                ->with([
                    'translations',
                    'products' => function ($query) {
                        $query->with([
                            'images',
                            'videos',
                            'half_portion_pair_product',
                            'attribute_family.attribute_groups.custom_attributes.options',
                            'super_attributes',
                            'constructor.groups.products' => function ($query) {
                                $query->with(['images', 'half_portion_pair_product']);
                            },
                            'grouped_products.associated_product',
                            'variants',
                            'downloadable_links',
                            'downloadable_samples',
                            'booking_products',
                            'bundle_options',
                            'up_sells',
                            'cross_sells',
                            'drinks' => function ($query) {
                                $query->with('images')
                                    ->orderByPivot('sort', 'asc');
                            },
                        ]);
                    },
                ])
                ->where('status', 1)
                ->orderBy('position', 'asc');

            if ($usePagination) {
                $paginator = $query->paginate($limit);

                $data = [
                    'data'  => CatalogResource::collection($paginator->items())->resolve($request),
                    'links' => [
                        'first' => $paginator->url(1),
                        'last'  => $paginator->url($paginator->lastPage()),
                        'prev'  => $paginator->previousPageUrl(),
                        'next'  => $paginator->nextPageUrl(),
                    ],
                    'meta'  => [
                        'current_page' => $paginator->currentPage(),
                        'from'         => $paginator->firstItem(),
                        'last_page'    => $paginator->lastPage(),
                        'links'        => $paginator->linkCollection()->toArray(),
                        'path'         => $paginator->path(),
                        'per_page'     => $paginator->perPage(),
                        'to'           => $paginator->lastItem(),
                        'total'        => $paginator->total(),
                    ],
                ];

                return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $categories = $query->get();

            $data = [
                'data' => CatalogResource::collection($categories)->resolve($request),
            ];

            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });

        // Используем helper функцию для быстрого возврата JSON через stream
        return api_stream_json($jsonResponse, 'catalog.json', [
            'Cache-Control' => 'public, max-age=' . $this->cacheTtl,
        ]);
    }

    /**
     * Clear all catalog cache entries.
     */
    public static function clearCatalogCache(): void
    {
        try {
            $store = Cache::getStore();
            $storeClass = get_class($store);

            // Log store type for debugging
            Log::debug('Cache store type: ' . $storeClass, [
                'has_getRedis' => method_exists($store, 'getRedis'),
                'has_tags' => method_exists($store, 'tags'),
                'is_RedisStore' => $store instanceof \Illuminate\Cache\RedisStore,
                'is_FileStore' => $store instanceof \Illuminate\Cache\FileStore,
            ]);

            // For Redis driver, check if store has getRedis() method (more reliable than instanceof)
            if (method_exists($store, 'getRedis')) {
                try {
                    $redis = $store->getRedis();

                // Get the actual cache prefix from Laravel Cache store
                // Laravel RedisStore uses getPrefix() method which returns the full prefix
                $cachePrefix = method_exists($store, 'getPrefix')
                    ? $store->getPrefix()
                    : config('cache.prefix', 'laravel_cache');

                // Laravel Cache format: {prefix}:{key}
                // The prefix from getPrefix() already includes the separator if needed
                // But we need to ensure we have the right format
                $basePrefix = rtrim($cachePrefix, ':');
                $searchPattern = $basePrefix . ':' . self::CACHE_PREFIX . '*';

                // First, try to find keys using SCAN (more efficient)
                $allKeys = [];
                $cursor = 0;

                do {
                    $result = $redis->scan($cursor, ['match' => $searchPattern, 'count' => 100]);
                    $cursor = is_array($result) ? ($result[0] ?? 0) : 0;
                    $keys = is_array($result) ? ($result[1] ?? []) : [];

                    if (!empty($keys)) {
                        $allKeys = array_merge($allKeys, $keys);
                    }
                } while ($cursor > 0);

                // If SCAN didn't find anything, try KEYS as fallback
                if (empty($allKeys)) {
                    try {
                        $keys = $redis->keys($searchPattern);
                        if (is_array($keys) && !empty($keys)) {
                            $allKeys = $keys;
                        }
                    } catch (\Exception $e) {
                        // KEYS might be disabled in production, that's okay
                        Log::debug('KEYS command not available: ' . $e->getMessage());
                    }
                }

                // Also try alternative pattern without prefix (in case prefix is handled differently)
                if (empty($allKeys)) {
                    $altPattern = self::CACHE_PREFIX . '*';
                    $cursor = 0;
                    do {
                        $result = $redis->scan($cursor, ['match' => $altPattern, 'count' => 100]);
                        $cursor = is_array($result) ? ($result[0] ?? 0) : 0;
                        $keys = is_array($result) ? ($result[1] ?? []) : [];
                        if (!empty($keys)) {
                            // Filter only keys that contain our prefix
                            $keys = array_filter($keys, function($key) {
                                return strpos($key, self::CACHE_PREFIX) !== false;
                            });
                            $allKeys = array_merge($allKeys, $keys);
                        }
                    } while ($cursor > 0);
                }

                // Remove duplicates
                $allKeys = array_unique($allKeys);

                if (!empty($allKeys)) {
                    // Delete keys in batches to avoid memory issues
                    $chunks = array_chunk($allKeys, 100);
                    foreach ($chunks as $chunk) {
                        $redis->del($chunk);
                    }
                    Log::info('Cleared ' . count($allKeys) . ' catalog cache keys', [
                        'pattern' => $searchPattern,
                        'keys_count' => count($allKeys),
                        'store_class' => $storeClass
                    ]);
                } else {
                    Log::warning('No catalog cache keys found to clear', [
                        'pattern' => $searchPattern,
                        'cache_prefix' => $cachePrefix,
                        'base_prefix' => $basePrefix,
                        'store_class' => $storeClass
                    ]);
                }

                return;
            } catch (\Exception $e) {
                Log::warning('Failed to clear Redis cache: ' . $e->getMessage(), [
                    'store_class' => $storeClass,
                    'exception' => get_class($e)
                ]);
            }
        }

        // For file driver, use Cache::forget() for all possible combinations
        if ($store instanceof \Illuminate\Cache\FileStore) {
            try {
                $channels = \Webkul\Core\Facades\Core::getAllChannels();
                $locales = \Webkul\Core\Facades\Core::getAllLocales();

                $clearedCount = 0;

                // Reasonable limits for pagination
                $maxPages = 20; // Clear first 20 pages
                $limits = [10, 20, 50, 100]; // Common limit values

                foreach ($channels as $channel) {
                    foreach ($locales as $locale) {
                        // Clear paginated versions
                        for ($page = 1; $page <= $maxPages; $page++) {
                            foreach ($limits as $limit) {
                                // Paginated = 1
                                $cacheKey = self::CACHE_PREFIX . ":{$channel->id}:{$locale->code}:page_{$page}:limit_{$limit}:paginated_1";
                                Cache::forget($cacheKey);
                                $clearedCount++;

                                // Paginated = 0
                                $cacheKey = self::CACHE_PREFIX . ":{$channel->id}:{$locale->code}:page_{$page}:limit_{$limit}:paginated_0";
                                Cache::forget($cacheKey);
                                $clearedCount++;
                            }
                        }
                    }
                }

                Log::info('Cleared catalog cache entries using Cache::forget()', [
                    'driver' => 'file',
                    'attempted' => $clearedCount,
                    'channels' => $channels->count(),
                    'locales' => $locales->count(),
                    'store_class' => $storeClass
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to clear catalog cache for file driver: ' . $e->getMessage(), [
                    'store_class' => $storeClass,
                    'exception' => get_class($e)
                ]);
            }

            return;
        }

        // For database driver, delete entries matching pattern
        if ($store instanceof \Illuminate\Cache\DatabaseStore) {
            try {
                $connection = $store->getConnection();
                $table = $store->getTable();
                $cachePrefix = config('cache.prefix', 'laravel_cache');

                // Delete cache entries where key starts with our prefix
                $pattern = $cachePrefix . ':' . self::CACHE_PREFIX . '%';
                $deleted = $connection->table($table)
                    ->where('key', 'like', $pattern)
                    ->delete();

                Log::info('Cleared ' . $deleted . ' catalog cache entries from database', [
                    'table' => $table,
                    'pattern' => $pattern,
                    'store_class' => $storeClass
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to clear catalog cache from database: ' . $e->getMessage(), [
                    'store_class' => $storeClass,
                    'exception' => get_class($e)
                ]);
            }

            return;
        }

        // For drivers that support tags, try to use tags if available
        if (method_exists($store, 'tags')) {
            try {
                Cache::tags([self::CACHE_PREFIX])->flush();
                Log::info('Cleared catalog cache using tags', ['store_class' => $storeClass]);
            } catch (\Exception $e) {
                Log::warning('Failed to clear catalog cache using tags: ' . $e->getMessage(), [
                    'store_class' => $storeClass,
                    'exception' => get_class($e)
                ]);
            }
            return;
        }

        // Fallback: log warning if no specific clearing mechanism was found
        Log::warning('Catalog cache cannot be cleared automatically for this cache driver. Please clear cache manually.', [
            'store_class' => $storeClass
        ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear catalog cache: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
}

