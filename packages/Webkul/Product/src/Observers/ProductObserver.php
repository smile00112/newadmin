<?php

namespace Webkul\Product\Observers;

use Illuminate\Support\Facades\Storage;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\CatalogCategoryController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\NomenclatureController;

class ProductObserver
{
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
        $this->clearCatalogCache();
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
        }
    }
}
