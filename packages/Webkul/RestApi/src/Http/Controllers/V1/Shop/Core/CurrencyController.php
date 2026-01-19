<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Core;

use Illuminate\Http\Request;
use Webkul\Core\Repositories\CurrencyRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Core\CurrencyResource;
use Webkul\RestApi\Traits\ProvideApiCache;

class CurrencyController extends CoreController
{
    use ProvideApiCache;

    /**
     * Cache TTL in seconds (1 hour - currencies rarely change).
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
        return CurrencyRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return CurrencyResource::class;
    }

    /**
     * Returns a listing of all currencies (cached).
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
}
