<?php

namespace Webkul\MobileApp\Console\Commands;

use Illuminate\Console\Command;
use Webkul\MobileApp\Http\Controllers\Api\MobileSettingsController;

class WarmMobileSettingsCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mobile-settings:warm-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm mobile-settings API cache for all channels';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Warming mobile-settings cache...');

        MobileSettingsController::warmCache();

        $this->info('Mobile-settings cache warmed successfully.');

        return self::SUCCESS;
    }
}
