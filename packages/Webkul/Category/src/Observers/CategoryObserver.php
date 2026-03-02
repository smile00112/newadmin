<?php

namespace Webkul\Category\Observers;

use Illuminate\Support\Facades\Storage;
use Webkul\Category\Models\Category;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\CatalogCategoryController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\NomenclatureController;

class CategoryObserver
{
    /**
     * Handle the Category "deleted" event.
     *
     * @param  \Webkul\Category\Contracts\Category  $category
     * @return void
     */
    public function deleted($category)
    {
        Storage::deleteDirectory('category/'.$category->id);

        $this->clearCatalogCache();
    }

    /**
     * Handle the Category "saved" event.
     *
     * @param  \Webkul\Category\Contracts\Category  $category
     * @return void
     */
    public function saved($category)
    {
        foreach ($category->children as $child) {
            $child->touch();
        }

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
