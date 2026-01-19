<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Catalog;

use Illuminate\Http\Request;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Catalog\AttributeFamilyResource;
use Webkul\RestApi\Traits\ProvideApiCache;

class AttributeFamilyController extends CatalogController
{
    use ProvideApiCache;

    /**
     * Cache TTL in seconds (1 hour - attribute families rarely change).
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
        return AttributeFamilyRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return AttributeFamilyResource::class;
    }

    /**
     * Returns a listing of all attribute families (cached).
     */
    public function allResources(Request $request): \Illuminate\Http\Response
    {
        $data = $this->cachedResponse('all', function () {
            return $this->getRepositoryInstance()
                ->with(['attributeGroups.customAttributes'])
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();
        });

        return response(['data' => $data]);
    }
}
