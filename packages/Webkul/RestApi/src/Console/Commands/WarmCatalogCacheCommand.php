<?php

namespace Webkul\RestApi\Console\Commands;

use Illuminate\Console\Command;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\CatalogCategoryController;

class WarmCatalogCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:warm-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm catalog API cache for all channel+locale combinations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Warming catalog cache...');

        CatalogCategoryController::warmCache();

        $this->info('Catalog cache warmed successfully.');

        return self::SUCCESS;
    }
}
