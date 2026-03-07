<?php

namespace Webkul\RestApi\Console\Commands;

use Illuminate\Console\Command;
use Webkul\RestApi\Http\Controllers\V1\Shop\Catalog\NomenclatureController;

class WarmNomenclatureCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nomenclature:warm-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm nomenclature API cache for all channel+locale combinations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Warming nomenclature cache...');

        NomenclatureController::warmCache();

        $this->info('Nomenclature cache warmed successfully.');

        return self::SUCCESS;
    }
}
