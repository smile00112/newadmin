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

        $this->clearCatalogCache();
    }

    /**
     * Handle the Product "saved" event.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return void
     */
    public function saved($product)
    {
        if (! self::$cacheScheduled) {
            self::$cacheScheduled = true;
            $observer = $this;
            app()->terminating(function () use ($observer) {
                $observer->clearCatalogCache();
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
            WarmNomenclatureCacheJob::dispatch();
        }
    }
}
