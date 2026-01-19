<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Core;

use Illuminate\Support\Facades\Cache;
use Webkul\RestApi\Http\Resources\V1\Shop\Core\ThemeResource;
use Webkul\RestApi\Traits\ProvideApiCache;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class ThemeController extends CoreController
{
    use ProvideApiCache;

    /**
     * Using const variable for status.
     */
    public const STATUS = 1;

    /**
     * Cache TTL in seconds (10 minutes).
     */
    protected int $cacheTtl = 600;

    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return ThemeCustomizationRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return ThemeResource::class;
    }

    /**
     * Get Theme Customizations listing (cached).
     */
    public function getThemeCustomizations(): \Illuminate\Http\Response
    {
        $channelId = core()->getCurrentChannel()->id;

        $data = $this->cachedResponse("customizations:{$channelId}", function () use ($channelId) {
            return $this->getRepositoryInstance()
                ->orderBy('sort_order')
                ->findWhere([
                    'status'     => self::STATUS,
                    'channel_id' => $channelId,
                ])
                ->toArray();
        });

        return response([
            'data' => $data,
        ]);
    }

    /**
     * Clear theme customizations cache for a channel.
     */
    public static function clearCustomizationsCache(?int $channelId = null): void
    {
        if ($channelId) {
            Cache::forget('api_themecontroller:customizations:' . $channelId);
        }
    }
}
