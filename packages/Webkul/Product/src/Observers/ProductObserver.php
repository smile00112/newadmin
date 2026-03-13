<?php

namespace Webkul\Product\Observers;

use Illuminate\Support\Facades\Storage;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\CatalogCategoryController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\NomenclatureController;
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
        }

        if (class_exists(NomenclatureController::class)) {
            NomenclatureController::clearNomenclatureCache();

            // Only dispatch warm cache if queue is async; skip on sync to avoid blocking
            if (config('queue.default') !== 'sync') {
                WarmNomenclatureCacheJob::dispatch();
            }
        }
    }
}
