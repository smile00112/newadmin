<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Catalog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\CategoryResource;
use Webkul\RestApi\Traits\ProvideApiCache;

class CategoryController extends CatalogController
{
    use ProvideApiCache;

    /**
     * Cache TTL in seconds (10 minutes - categories may change).
     */
    protected int $cacheTtl = 600;

    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(protected ProductRepository $productRepository) {}

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
        return CategoryResource::class;
    }

    /**
     * Returns a listing of all categories (cached).
     */
    public function allResources(Request $request): \Illuminate\Http\Response
    {
        $channelId = core()->getCurrentChannel()->id;
        $locale = core()->getRequestedLocaleCode();

        $data = $this->cachedResponse("all:{$channelId}:{$locale}", function () {
            return $this->getRepositoryInstance()
                ->with(['translations'])
                ->where('status', 1)
                ->orderBy('position', 'asc')
                ->get()
                ->toArray();
        });

        return response(['data' => $data]);
    }

    /**
     * Returns a listing of the descendant categories (cached).
     */
    public function descendantCategories(Request $request): \Illuminate\Http\Response
    {
        $parentId = $request->input('parent_id');
        $channelId = core()->getCurrentChannel()->id;
        $locale = core()->getRequestedLocaleCode();

        $cacheKey = "descendants:{$channelId}:{$locale}:" . ($parentId ?? 'root');

        $data = $this->cachedResponse($cacheKey, function () use ($parentId) {
            $results = $this->getRepositoryInstance()->getVisibleCategoryTree($parentId);

            return CategoryResource::collection($results)->resolve();
        });

        return response(['data' => $data]);
    }

    /**
     * Get product maximum price (cached for 5 minutes).
     */
    public function getProductMaxPrice($categoryId = null): \Illuminate\Http\Response
    {
        $cacheKey = 'max_price:' . ($categoryId ?? 'all');

        $maxPrice = Cache::remember(
            $this->buildCacheKey($cacheKey),
            300, // 5 minutes
            function () use ($categoryId) {
                if (core()->getConfigData('catalog.products.search.engine') == 'elastic') {
                    $searchEngine = core()->getConfigData('catalog.products.search.storefront_mode');
                }

                return $this->productRepository
                    ->setSearchEngine($searchEngine ?? 'database')
                    ->getMaxPrice(['category_id' => $categoryId]);
            }
        );

        return response([
            'data' => [
                'max_price' => $maxPrice,
            ],
        ]);
    }

    /**
     * Clear category cache.
     */
    public static function clearCategoryCache(): void
    {
        $prefix = 'api_categorycontroller';
        // Clear known cache keys
        Cache::forget($prefix . ':all');
        Cache::forget($prefix . ':descendants');
    }
}
