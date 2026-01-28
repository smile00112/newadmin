<?php

namespace App\Console\Commands;

use App\Services\BonusService;
use Illuminate\Console\Command;
use Webkul\Customer\Models\CustomerProxy;

class RecalculateCustomerLevels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:recalculate-levels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate bonus levels for all customers';

    /**
     * Execute the console command.
     */
    public function handle(BonusService $bonusService): int
    {
        $this->info('Starting customer levels recalculation...');

        if (! core()->getConfigData('bonus_system.general.enabled')) {
            $this->error('Bonus system is disabled. Please enable it in admin configuration.');

            return Command::FAILURE;
        }

        $customers = CustomerProxy::all();
        $total = $customers->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($customers as $customer) {
            $bonusService->updateCustomerLevel($customer);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Recalculated levels for {$total} customers.");

        return Command::SUCCESS;
    }
}
