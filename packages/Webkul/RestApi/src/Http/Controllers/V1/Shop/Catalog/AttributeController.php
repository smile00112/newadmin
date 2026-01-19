<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Catalog;

use Illuminate\Http\Request;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\AttributeResource;
use Webkul\RestApi\Traits\ProvideApiCache;

class AttributeController extends CatalogController
{
    use ProvideApiCache;

    /**
     * Cache TTL in seconds (1 hour - attributes rarely change).
     */
    protected int $cacheTtl = 3600;

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
        return AttributeRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return AttributeResource::class;
    }

    /**
     * Returns a listing of all attributes (cached).
     */
    public function allResources(Request $request): \Illuminate\Http\Response
    {
        $data = $this->cachedResponse('all', function () {
            return $this->getRepositoryInstance()
                ->with(['options', 'translations'])
                ->orderBy('position', 'asc')
                ->all()
                ->toArray();
        });

        return response(['data' => $data]);
    }
}
