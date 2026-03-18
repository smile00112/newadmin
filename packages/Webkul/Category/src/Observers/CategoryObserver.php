<?php

namespace Webkul\Category\Observers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Webkul\Category\Models\Category;
use Webkul\Core\Facades\Core;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\CatalogCategoryController;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\NomenclatureController;
use Webkul\RestApi\Jobs\WarmCatalogCacheJob;
use Webkul\RestApi\Jobs\WarmNomenclatureCacheJob;

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

            // Synchronously warm default channel+locale so first request is fast
            $this->warmCatalogDefault();

            WarmCatalogCacheJob::dispatch();
        }

        if (class_exists(NomenclatureController::class)) {
            NomenclatureController::clearNomenclatureCache();

            // Synchronously warm default channel+locale so first request is fast
            $this->warmNomenclatureDefault();

            WarmNomenclatureCacheJob::dispatch();
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
}
