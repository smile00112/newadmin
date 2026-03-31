<?php

namespace Webkul\Product\Observers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Facades\Core;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\CatalogCategoryController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\CatalogCategoryV2Controller;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\NomenclatureController;
use Webkul\RestApi\Jobs\WarmCatalogCacheJob;
use Webkul\RestApi\Jobs\WarmCatalogV2CacheJob;
use Webkul\RestApi\Jobs\WarmNomenclatureCacheJob;

class ProductObserver
{
    /**
     * Debounce flag to clear catalog cache only once per request.
     */
    protected static bool $cacheScheduled = false;

    /**
     * Handle the Product "deleted" event.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return void
     */
    public function deleted($product)
    {
        Storage::deleteDirectory('product/'.$product->id);

        $this->scheduleCacheClear();
    }

    /**
     * Handle the Product "saved" event.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return void
     */
    public function saved($product)
    {
        $this->scheduleCacheClear();
    }

    /**
     * Schedule cache clear once per request (after response is sent).
     */
    protected function scheduleCacheClear(): void
    {
        if (! self::$cacheScheduled) {
            self::$cacheScheduled = true;

            app()->terminating(function () {
                $this->clearCatalogCache();
                self::$cacheScheduled = false;
            });
        }
    }

    /**
     * Clear catalog API cache.
     */
    protected function clearCatalogCache(): void
    {
        if (class_exists(CatalogCategoryController::class)) {
            CatalogCategoryController::clearCatalogCache();

            // Synchronously warm default channel+locale so first request is fast
            $this->warmCatalogDefault();

            // Only dispatch warm cache if queue is async; skip on sync to avoid blocking
            if (config('queue.default') !== 'sync') {
                WarmCatalogCacheJob::dispatch();
            }
        }

        if (class_exists(NomenclatureController::class)) {
            NomenclatureController::clearNomenclatureCache();

            // Synchronously warm default channel+locale so first request is fast
            $this->warmNomenclatureDefault();

            // Only dispatch warm cache if queue is async; skip on sync to avoid blocking
            if (config('queue.default') !== 'sync') {
                WarmNomenclatureCacheJob::dispatch();
            }
        }

        if (class_exists(CatalogCategoryV2Controller::class)) {
            CatalogCategoryV2Controller::clearCatalogV2Cache();

            // Synchronously warm default channel+locale so first request is fast
            $this->warmCatalogV2Default();

            // Only dispatch warm cache if queue is async; skip on sync to avoid blocking
            if (config('queue.default') !== 'sync') {
                WarmCatalogV2CacheJob::dispatch();
            }
        }
    }

    /**
     * Warm catalog cache for default channel+locale only (sync, so first request is fast).
     */
    protected function warmCatalogDefault(): void
    {
        try {
            $channel = Core::getDefaultChannel();
            if ($channel && $channel->default_locale) {
                $localeCode = is_object($channel->default_locale) ? $channel->default_locale->code : $channel->default_locale;
                if (! empty($localeCode)) {
                    CatalogCategoryController::warmCacheForChannelAndLocale($channel, $localeCode);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to warm catalog cache for default channel', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Warm nomenclature cache for default channel+locale only (sync, so first request is fast).
     */
    protected function warmNomenclatureDefault(): void
    {
        try {
            $channel = Core::getDefaultChannel();
            if ($channel && $channel->default_locale) {
                $localeCode = is_object($channel->default_locale) ? $channel->default_locale->code : $channel->default_locale;
                if (! empty($localeCode)) {
                    NomenclatureController::warmCacheForChannelAndLocale($channel, $localeCode);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to warm nomenclature cache for default channel', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Warm catalog-v2 cache for default channel+locale only (sync, so first request is fast).
     */
    protected function warmCatalogV2Default(): void
    {
        try {
            $channel = Core::getDefaultChannel();
            if ($channel && $channel->default_locale) {
                $localeCode = is_object($channel->default_locale) ? $channel->default_locale->code : $channel->default_locale;
                if (! empty($localeCode)) {
                    CatalogCategoryV2Controller::warmCacheForChannelAndLocale($channel, $localeCode);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to warm catalog-v2 cache for default channel', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
