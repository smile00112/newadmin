<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Bonus\Services\BonusService;

class ExpireBonuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire bonuses that have reached their expiration date';

    /**
     * Execute the console command.
     */
    public function handle(BonusService $bonusService): int
    {
        $this->info('Starting bonus expiration process...');

        $expiredCount = $bonusService->expireBonuses();

        $this->info("Expired {$expiredCount} bonus records.");

        return Command::SUCCESS;
    }
}
