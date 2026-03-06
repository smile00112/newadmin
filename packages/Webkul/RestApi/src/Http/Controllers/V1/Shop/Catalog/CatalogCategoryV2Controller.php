<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Catalog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\CatalogV2Resource;
use Webkul\RestApi\Traits\ProvideApiCache;

class CatalogCategoryV2Controller extends CatalogController
{
    use ProvideApiCache;

    /**
     * Cache key prefix for catalog V2.
     */
    public const CACHE_PREFIX = 'api_catalog_v2';

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
        return CatalogV2Resource::class;
    }

    /**
     * Returns a listing of catalog categories (cached with pagination) - lightweight V2 format.
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
                            'videos',
                            'up_sells:id',
                            'cross_sells:id',
                            'drinks:id',
                            'constructor.groups.products:id',
                        ]);
                    },
                ])
                ->where('status', 1)
                ->orderBy('position', 'asc');

            if ($usePagination) {
                $paginator = $query->paginate($limit);

                $data = [
                    'data'  => CatalogV2Resource::collection($paginator->items())->resolve($request),
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
                'data' => CatalogV2Resource::collection($categories)->resolve($request),
            ];

            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });

        return api_stream_json($jsonResponse, 'catalog-v2.json');
    }

    /**
     * Clear all catalog V2 cache entries.
     */
    public static function clearCatalogV2Cache(): void
    {
        try {
            $store = Cache::getStore();
            $storeClass = get_class($store);

            Log::debug('Cache store type: ' . $storeClass, [
                'has_getRedis'   => method_exists($store, 'getRedis'),
                'has_tags'       => method_exists($store, 'tags'),
                'is_RedisStore'  => $store instanceof \Illuminate\Cache\RedisStore,
                'is_FileStore'   => $store instanceof \Illuminate\Cache\FileStore,
            ]);

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

                    if (empty($allKeys)) {
                        $altPattern = self::CACHE_PREFIX . '*';
                        $cursor = 0;
                        do {
                            $result = $redis->scan($cursor, ['match' => $altPattern, 'count' => 100]);
                            $cursor = is_array($result) ? ($result[0] ?? 0) : 0;
                            $keys = is_array($result) ? ($result[1] ?? []) : [];
                            if (! empty($keys)) {
                                $keys = array_filter($keys, fn ($key) => strpos($key, self::CACHE_PREFIX) !== false);
                                $allKeys = array_merge($allKeys, $keys);
                            }
                        } while ($cursor > 0);
                    }

                    $allKeys = array_unique($allKeys);

                    if (! empty($allKeys)) {
                        $chunks = array_chunk($allKeys, 100);
                        foreach ($chunks as $chunk) {
                            $redis->del($chunk);
                        }
                        Log::info('Cleared ' . count($allKeys) . ' catalog V2 cache keys', [
                            'pattern'     => $searchPattern,
                            'keys_count'  => count($allKeys),
                            'store_class' => $storeClass,
                        ]);
                    } else {
                        Log::warning('No catalog V2 cache keys found to clear', [
                            'pattern'      => $searchPattern,
                            'cache_prefix' => $cachePrefix,
                            'base_prefix'  => $basePrefix,
                            'store_class'  => $storeClass,
                        ]);
                    }

                    return;
                } catch (\Exception $e) {
                    Log::warning('Failed to clear Redis cache: ' . $e->getMessage(), [
                        'store_class' => $storeClass,
                        'exception'   => get_class($e),
                    ]);
                }
            }

            if ($store instanceof \Illuminate\Cache\FileStore) {
                try {
                    $channels = \Webkul\Core\Facades\Core::getAllChannels();
                    $locales = \Webkul\Core\Facades\Core::getAllLocales();
                    $clearedCount = 0;
                    $maxPages = 20;
                    $limits = [10, 20, 50, 100];

                    foreach ($channels as $channel) {
                        foreach ($locales as $locale) {
                            for ($page = 1; $page <= $maxPages; $page++) {
                                foreach ($limits as $limit) {
                                    $cacheKey = self::CACHE_PREFIX . ":{$channel->id}:{$locale->code}:page_{$page}:limit_{$limit}:paginated_1";
                                    Cache::forget($cacheKey);
                                    $clearedCount++;
                                    $cacheKey = self::CACHE_PREFIX . ":{$channel->id}:{$locale->code}:page_{$page}:limit_{$limit}:paginated_0";
                                    Cache::forget($cacheKey);
                                    $clearedCount++;
                                }
                            }
                        }
                    }

                    Log::info('Cleared catalog V2 cache entries using Cache::forget()', [
                        'driver'       => 'file',
                        'attempted'    => $clearedCount,
                        'channels'     => $channels->count(),
                        'locales'      => $locales->count(),
                        'store_class'  => $storeClass,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to clear catalog V2 cache for file driver: ' . $e->getMessage(), [
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

                    Log::info('Cleared ' . $deleted . ' catalog V2 cache entries from database', [
                        'table'       => $table,
                        'pattern'     => $pattern,
                        'store_class' => $storeClass,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to clear catalog V2 cache from database: ' . $e->getMessage(), [
                        'store_class' => $storeClass,
                        'exception'   => get_class($e),
                    ]);
                }

                return;
            }

            if (method_exists($store, 'tags')) {
                try {
                    Cache::tags([self::CACHE_PREFIX])->flush();
                    Log::info('Cleared catalog V2 cache using tags', ['store_class' => $storeClass]);
                } catch (\Exception $e) {
                    Log::warning('Failed to clear catalog V2 cache using tags: ' . $e->getMessage(), [
                        'store_class' => $storeClass,
                        'exception'   => get_class($e),
                    ]);
                }

                return;
            }

            Log::warning('Catalog V2 cache cannot be cleared automatically for this cache driver. Please clear cache manually.', [
                'store_class' => $storeClass,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear catalog V2 cache: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);
        }
    }
}
