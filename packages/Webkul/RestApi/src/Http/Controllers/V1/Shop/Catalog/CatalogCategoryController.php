<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Catalog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
    public function allResources(Request $request): \Illuminate\Http\JsonResponse
    {
        $channelId = core()->getCurrentChannel()->id;
        $locale = core()->getRequestedLocaleCode();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $usePagination = is_null($request->input('pagination')) || $request->input('pagination');

        $cacheKey = self::CACHE_PREFIX . ":{$channelId}:{$locale}:page_{$page}:limit_{$limit}:paginated_" . ($usePagination ? '1' : '0');

        $response = Cache::remember($cacheKey, $this->cacheTtl, function () use ($request, $usePagination, $limit) {
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
                        ]);
                    },
                ])
                ->where('status', 1)
                ->orderBy('position', 'asc');

            if ($usePagination) {
                $paginator = $query->paginate($limit);

                return [
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
            }

            $categories = $query->get();

            return [
                'data' => CatalogResource::collection($categories)->resolve($request),
            ];
        });

        return response()->json($response);
    }

    /**
     * Clear all catalog cache entries.
     */
    public static function clearCatalogCache(): void
    {
        $prefix = config('cache.prefix', 'laravel_cache') . '_' . self::CACHE_PREFIX;

        // For Redis driver, use pattern-based deletion
        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            $redis = Cache::getStore()->getRedis();
            $keys = $redis->keys($prefix . ':*');

            if (! empty($keys)) {
                $redis->del($keys);
            }

            return;
        }

        // For file/database drivers, we need to track keys or use tags
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags([self::CACHE_PREFIX])->flush();
        }
    }
}

