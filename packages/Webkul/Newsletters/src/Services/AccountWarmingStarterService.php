<?php

namespace Webkul\Newsletters\Services;

use Webkul\Newsletters\Models\AccountWarming;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Jobs\ProcessAccountWarming;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AccountWarmingStarterService
{
    /**
     * Start account warming processing.
     *
     * @param AccountWarming $warming
     * @return array ['success' => bool, 'message' => string, 'status_code' => int]
     */
    public function start(AccountWarming $warming): array
    {
        // Validate accounts
        $accountsCheck = $this->validateAccounts($warming);
        if (!$accountsCheck['success']) {
            return $accountsCheck;
        }

        // Validate phrases
        $phrasesCheck = $this->validatePhrases($warming);
        if (!$phrasesCheck['success']) {
            return $phrasesCheck;
        }

        // Reset participants state
        DB::table('newsletters_account_warming_participants')
            ->where('account_warming_id', $warming->id)
            ->delete();

        // Create participants for all selected accounts
        $this->createParticipants($warming);

        // Activate account warming
        $warming->update([
            'active' => true,
            'status' => 'pending'
        ]);

        // Dispatch job
        $this->dispatchWarmingJob($warming);

        Log::info('Account warming started successfully', [
            'account_warming_id' => $warming->id,
            'user_id' => auth()->id(),
        ]);

        return [
            'success' => true,
            'message' => trans('newsletters::app.admin.account-warmings.warming-started'),
            'status_code' => 200
        ];
    }

    /**
     * Validate accounts for the warming.
     */
    protected function validateAccounts(AccountWarming $warming): array
    {
        $accountIds = $warming->selected_account_ids ?? [];
        
        if (count($accountIds) < 2) {
            return [
                'success' => false,
                'message' => trans('newsletters::app.admin.account-warmings.min-accounts-required'),
                'status_code' => 400
            ];
        }

        // Check if accounts exist and are active
        $activeAccounts = VacapInstance::whereIn('id', $accountIds)
            ->where('active', true)
            ->where('blocked', false)
            ->count();

        if ($activeAccounts < 2) {
            return [
                'success' => false,
                'message' => trans('newsletters::app.admin.account-warmings.insufficient-active-accounts'),
                'status_code' => 400
            ];
        }

        return ['success' => true];
    }

    /**
     * Validate phrases for the warming.
     */
    protected function validatePhrases(AccountWarming $warming): array
    {
        $phrases = $warming->phrases ?? [];
        
        if (empty($phrases) || !is_array($phrases)) {
            return [
                'success' => false,
                'message' => trans('newsletters::app.admin.account-warmings.no-phrases'),
                'status_code' => 400
            ];
        }

        // Validate that each phrase has question and answer
        foreach ($phrases as $phrase) {
            if (!isset($phrase['question']) || !isset($phrase['answer']) || 
                empty(trim($phrase['question'])) || empty(trim($phrase['answer']))) {
                return [
                    'success' => false,
                    'message' => trans('newsletters::app.admin.account-warmings.invalid-phrases'),
                    'status_code' => 400
                ];
            }
        }

        return ['success' => true];
    }

    /**
     * Create participants for all selected accounts.
     */
    protected function createParticipants(AccountWarming $warming): void
    {
        $accountIds = $warming->selected_account_ids ?? [];
        
        foreach ($accountIds as $accountId) {
            DB::table('newsletters_account_warming_participants')->insert([
                'account_warming_id' => $warming->id,
                'whatsapp_instance_id' => $accountId,
                'messages_sent' => 0,
                'messages_received' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Dispatch the warming job.
     */
    protected function dispatchWarmingJob(AccountWarming $warming): void
    {
        $delay = $this->calculateWarmingDelay($warming);

        if ($delay > 0) {
            ProcessAccountWarming::dispatch($warming->id)
                ->delay(now()->addSeconds($delay))
                ->onQueue('account-warming');

            Log::info('Account warming scheduled with delay', [
                'account_warming_id' => $warming->id,
                'delay_seconds' => $delay,
                'scheduled_at' => now()->addSeconds($delay)->toDateTimeString(),
            ]);
        } else {
            ProcessAccountWarming::dispatch($warming->id)
                ->onQueue('account-warming');

            Log::info('Starting account warming without delay', [
                'account_warming_id' => $warming->id,
            ]);
        }
    }

    /**
     * Calculate delay based on warming parameters.
     */
    protected function calculateWarmingDelay(AccountWarming $warming): int
    {
        if ($warming->start_at && $warming->start_at->isFuture()) {
            return now()->diffInSeconds($warming->start_at);
        }

        return 0;
    }
}


