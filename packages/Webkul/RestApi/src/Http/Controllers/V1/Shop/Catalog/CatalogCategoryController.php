<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Catalog;

use Illuminate\Http\Request;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\CatalogResource;
use Webkul\RestApi\Traits\ProvideApiCache;

class CatalogCategoryController extends CatalogController
{
    use ProvideApiCache;

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
     * Returns a listing of catalog categories (cached).
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
                ->all()
                ->toArray();
        });

        return response(['data' => $data]);
    }
}

