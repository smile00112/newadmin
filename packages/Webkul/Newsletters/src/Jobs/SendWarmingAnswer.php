<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Models\AccountWarming;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Services\GreenAPIService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SendWarmingAnswer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 2;

    protected $accountWarmingId;
    protected $fromAccountId;
    protected $toAccountId;
    protected $answer;

    public function __construct(int $accountWarmingId, int $fromAccountId, int $toAccountId, string $answer)
    {
        $this->accountWarmingId = $accountWarmingId;
        $this->fromAccountId = $fromAccountId;
        $this->toAccountId = $toAccountId;
        $this->answer = $answer;
        $this->onQueue('account-warming');
    }

    public function handle()
    {
        $warming = AccountWarming::findOrFail($this->accountWarmingId);

        // Check if warming is still active
        if (!$warming->active) {
            Log::info('Account warming is not active, skipping answer', [
                'account_warming_id' => $this->accountWarmingId,
            ]);
            return;
        }

        $fromAccount = VacapInstance::findOrFail($this->fromAccountId);
        $toAccount = VacapInstance::findOrFail($this->toAccountId);

        try {
            $greenApiService = new GreenAPIService($fromAccount->link_name, $fromAccount->login, $fromAccount->password);
            
            $toPhone = $toAccount->phone;
            if (empty($toPhone)) {
                Log::error('Account has no phone number', [
                    'account_id' => $toAccount->id,
                ]);
                return;
            }

            $toPhone = preg_replace('/[^0-9]/', '', $toPhone);
            
            $response = $greenApiService->sendMessage($toPhone . '@c.us', $this->answer);

            Log::info('Warming answer message sent successfully', [
                'account_warming_id' => $this->accountWarmingId,
                'from_account_id' => $this->fromAccountId,
                'to_account_id' => $this->toAccountId,
                'to_phone' => $toPhone,
                'response' => $response,
            ]);

            // Update participants
            DB::table('newsletters_account_warming_participants')
                ->where('account_warming_id', $this->accountWarmingId)
                ->where('whatsapp_instance_id', $this->fromAccountId)
                ->increment('messages_sent', 1, [
                    'last_message_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('newsletters_account_warming_participants')
                ->where('account_warming_id', $this->accountWarmingId)
                ->where('whatsapp_instance_id', $this->toAccountId)
                ->increment('messages_received', 1, [
                    'last_message_at' => now(),
                    'updated_at' => now(),
                ]);

        } catch (\Exception $e) {
            Log::error('Failed to send warming answer message', [
                'account_warming_id' => $this->accountWarmingId,
                'from_account_id' => $this->fromAccountId,
                'to_account_id' => $this->toAccountId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

