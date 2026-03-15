<?php

namespace Webkul\RestApi\Console\Commands;

use Illuminate\Console\Command;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\CatalogCategoryV2Controller;

class WarmCatalogV2CacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog-v2:warm-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm catalog-v2 API cache for all channel+locale combinations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Warming catalog-v2 cache...');

        CatalogCategoryV2Controller::warmCache();

        $this->info('Catalog-v2 cache warmed successfully.');

        return self::SUCCESS;
    }
}
