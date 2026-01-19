<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Core;

use Illuminate\Http\Request;
use Webkul\Core\Repositories\LocaleRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Core\LocaleResource;
use Webkul\RestApi\Traits\ProvideApiCache;

class LocaleController extends CoreController
{
    use ProvideApiCache;

    /**
     * Cache TTL in seconds (1 hour - locales rarely change).
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
        return LocaleRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return LocaleResource::class;
    }

    /**
     * Returns a listing of all locales (cached).
     */
    public function allResources(Request $request): \Illuminate\Http\Response
    {
        $data = $this->cachedResponse('all', function () {
            return $this->getRepositoryInstance()
                ->orderBy('name', 'asc')
                ->get()
                ->toArray();
        });

        return response(['data' => $data]);
    }
}
