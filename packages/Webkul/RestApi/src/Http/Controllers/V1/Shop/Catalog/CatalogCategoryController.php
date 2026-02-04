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
                            'attribute_family.attribute_groups.custom_attributes.options',
                            'super_attributes',
                            'constructor.groups.products' => function ($query) {
                                $query->with('images');
                            },
                            'grouped_products.associated_product',
                            'variants',
                            'downloadable_links',
                            'downloadable_samples',
                            'booking_products',
                            'bundle_options',
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
        return api_stream_json($jsonResponse, 'catalog.json');
    }

    /**
     * Clear all catalog cache entries.
     */
    public static function clearCatalogCache(): void
    {
        try {
            $store = Cache::getStore();
            
            // For Redis driver, use pattern-based deletion
            if ($store instanceof \Illuminate\Cache\RedisStore) {
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
                        'keys_count' => count($allKeys)
                    ]);
                } else {
                    Log::warning('No catalog cache keys found to clear', [
                        'pattern' => $searchPattern,
                        'cache_prefix' => $cachePrefix,
                        'base_prefix' => $basePrefix
                    ]);
                }

                return;
            }

            // For file/database drivers, try to use tags if available
            if (method_exists($store, 'tags')) {
                try {
                    Cache::tags([self::CACHE_PREFIX])->flush();
                    Log::info('Cleared catalog cache using tags');
                } catch (\Exception $e) {
                    Log::warning('Failed to clear catalog cache using tags: ' . $e->getMessage());
                }
            } else {
                // Fallback: log warning
                Log::warning('Catalog cache cannot be cleared automatically for this cache driver. Please clear cache manually.');
            }
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

