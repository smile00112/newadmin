<?php

namespace Webkul\RestApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\CatalogCategoryV2Controller;

class WarmCatalogV2CacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job: warm catalog-v2 cache for all channel+locale combinations.
     */
    public function handle(): void
    {
        CatalogCategoryV2Controller::warmCache();
    }
}
