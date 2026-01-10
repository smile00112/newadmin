<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Models\AccountWarming;
use Webkul\Newsletters\Services\AccountWarmingService;
use Illuminate\Support\Facades\Log;

class ProcessAccountWarming implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected $accountWarmingId;

    public function __construct(int $accountWarmingId)
    {
        $this->accountWarmingId = $accountWarmingId;
        $this->onQueue('account-warming');
    }

    public function handle(AccountWarmingService $warmingService)
    {
        Log::info('ProcessAccountWarming started', [
            'account_warming_id' => $this->accountWarmingId,
        ]);

        $warming = AccountWarming::findOrFail($this->accountWarmingId);

        // Check if warming is still active
        if (!$warming->active) {
            Log::info('Account warming is not active, stopping', [
                'account_warming_id' => $this->accountWarmingId,
            ]);
            $warming->update(['status' => 'paused']);
            return;
        }

        // Update status to running
        $warming->update(['status' => 'running']);

        // Process one warming cycle
        $success = $warmingService->processWarmingCycle($warming);

        if (!$success) {
            Log::warning('Failed to process warming cycle', [
                'account_warming_id' => $this->accountWarmingId,
            ]);
            $warming->update(['status' => 'paused', 'active' => false]);
            return;
        }

        // Calculate delay for next cycle
        $delay = $this->calculateCycleDelay($warming);

        // Dispatch next cycle
        ProcessAccountWarming::dispatch($this->accountWarmingId)
            ->delay(now()->addSeconds($delay))
            ->onQueue('account-warming');

        Log::info('Account warming cycle completed, next cycle scheduled', [
            'account_warming_id' => $this->accountWarmingId,
            'delay_seconds' => $delay,
            'next_cycle_at' => now()->addSeconds($delay)->toDateTimeString(),
        ]);
    }

    /**
     * Calculate delay for next cycle.
     */
    protected function calculateCycleDelay(AccountWarming $warming): int
    {
        $delayFrom = $warming->delay_from;
        $delayTo = $warming->delay_to;

        if ($delayTo && !$delayFrom) {
            return (int) $delayTo;
        }

        if ($delayFrom && $delayTo) {
            $min = min((int) $delayFrom, (int) $delayTo);
            $max = max((int) $delayFrom, (int) $delayTo);
            return rand($min, $max);
        }

        if ($delayFrom && !$delayTo) {
            return (int) $delayFrom;
        }

        return 5; // Default delay
    }
}


