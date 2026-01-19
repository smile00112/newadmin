<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Core;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Webkul\Core\Repositories\CountryRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Core\CountryResource;
use Webkul\RestApi\Traits\ProvideApiCache;

class CountryController extends CoreController
{
    use ProvideApiCache;

    /**
     * Cache TTL in seconds (24 hours - countries rarely change).
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
        return CountryRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return CountryResource::class;
    }

    /**
     * Returns a listing of all countries (cached).
     */
    public function allResources(Request $request): \Illuminate\Http\Response
    {
        $data = $this->cachedResponse('all', function () {
            return $this->getRepositoryInstance()
                ->orderBy('name', 'asc')
                ->all()
                ->toArray();
        });

        return response(['data' => $data]);
    }

    /**
     * Get country state group listing (cached).
     */
    public function getCountryStateGroups(): \Illuminate\Http\Response
    {
        $data = $this->cachedResponse('state_groups', function () {
            return core()->groupedStatesByCountries();
        });

        return response(['data' => $data]);
    }
}
