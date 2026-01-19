<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Core;

use Illuminate\Http\Request;
use Webkul\Core\Repositories\CountryStateRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Core\CountryStateResource;
use Webkul\RestApi\Traits\ProvideApiCache;

class CountryStateController extends CoreController
{
    use ProvideApiCache;

    /**
     * Cache TTL in seconds (24 hours - states rarely change).
     */
    protected int $cacheTtl = 86400;

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
        return CountryStateRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return CountryStateResource::class;
    }

    /**
     * Returns a listing of all country states (cached).
     */
    public function allResources(Request $request): \Illuminate\Http\Response
    {
        // Include country_code filter in cache key if provided
        $countryCode = $request->input('country_code');
        $cacheKey = $countryCode ? "all:{$countryCode}" : 'all';

        $data = $this->cachedResponse($cacheKey, function () use ($countryCode) {
            $query = $this->getRepositoryInstance()->orderBy('default_name', 'asc');

            if ($countryCode) {
                $query = $query->where('country_code', $countryCode);
            }

            return $query->get()->toArray();
        });

        return response(['data' => $data]);
    }
}
