<?php

namespace Webkul\Newsletters\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Models\MailingList;
use Illuminate\Support\Facades\Log;

class ResetBlockedInstances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletters:reset-blocked-instances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset blocked status and message count for instances of active mailing lists';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting reset of blocked instances...');

        // Получаем все активные рассылки
        $activeMailingLists = MailingList::where('active', true)->get();

        if ($activeMailingLists->isEmpty()) {
            $this->info('No active mailing lists found.');
            Log::info('ResetBlockedInstances: No active mailing lists found');
            return Command::SUCCESS;
        }

        $totalReset = 0;

        foreach ($activeMailingLists as $mailingList) {
            // Получаем все инстансы этой рассылки
            $instances = VacapInstance::where('mailing_list_id', $mailingList->id)->get();

            foreach ($instances as $instance) {
                // Сбрасываем блокировку и счетчик сообщений
                $instance->update([
                    'blocked' => false,
                    'sending_message_count' => 0,
                ]);

                $totalReset++;
            }

            $this->info("Reset instances for mailing list #{$mailingList->id}: {$instances->count()} instances");
        }

        $this->info("Successfully reset {$totalReset} instances.");
        Log::info('ResetBlockedInstances: Successfully reset instances', [
            'total_reset' => $totalReset,
            'active_mailing_lists_count' => $activeMailingLists->count(),
        ]);

        return Command::SUCCESS;
    }
}

