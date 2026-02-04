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
        $store = Cache::getStore();
        
        // Get the actual cache prefix from Laravel Cache store
        $cachePrefix = method_exists($store, 'getPrefix') 
            ? $store->getPrefix() 
            : config('cache.prefix', 'laravel_cache');
        
        // Laravel Cache adds prefix with colon separator for Redis
        // Format: {prefix}:{key}
        $prefix = rtrim($cachePrefix, ':') . ':' . self::CACHE_PREFIX;

        // For Redis driver, use pattern-based deletion
        if ($store instanceof \Illuminate\Cache\RedisStore) {
            $redis = $store->getRedis();
            
            // Use SCAN instead of KEYS for better performance in production
            $pattern = $prefix . '*';
            $cursor = 0;
            $keys = [];
            
            do {
                $result = $redis->scan($cursor, ['match' => $pattern, 'count' => 100]);
                $cursor = $result[0];
                if (!empty($result[1])) {
                    $keys = array_merge($keys, $result[1]);
                }
            } while ($cursor > 0);
            
            // Remove duplicates
            $keys = array_unique($keys);
            
            if (!empty($keys)) {
                $redis->del($keys);
            }

            return;
        }

        // For file/database drivers, we need to track keys or use tags
        if (method_exists($store, 'tags')) {
            Cache::tags([self::CACHE_PREFIX])->flush();
        } else {
            // Fallback: try to clear by pattern if possible
            // This is a best-effort approach for drivers that don't support tags
            try {
                // For drivers without tags support, we can't easily clear by pattern
                // So we'll just log a warning
                Log::warning('Catalog cache cannot be cleared automatically for this cache driver. Please clear cache manually.');
            } catch (\Exception $e) {
                Log::warning('Failed to clear catalog cache: ' . $e->getMessage());
            }
        }
    }
}

