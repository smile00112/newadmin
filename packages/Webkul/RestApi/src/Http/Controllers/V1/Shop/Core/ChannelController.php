<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Core;

use Illuminate\Http\Request;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\RestApi\Http\Resources\V1\Shop\Core\ChannelResource;
use Webkul\RestApi\Traits\ProvideApiCache;

class ChannelController extends CoreController
{
    use ProvideApiCache;

    /**
     * Cache TTL in seconds (10 minutes - channels may change occasionally).
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
        return ChannelRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return ChannelResource::class;
    }

    /**
     * Returns a listing of all channels (cached).
     */
    public function allResources(Request $request): \Illuminate\Http\Response
    {
        $data = $this->cachedResponse('all', function () {
            return $this->getRepositoryInstance()
                ->with(['locales', 'currencies', 'inventory_sources'])
                ->orderBy('id', 'asc')
                ->get()
                ->toArray();
        });

        return response(['data' => $data]);
    }
}
