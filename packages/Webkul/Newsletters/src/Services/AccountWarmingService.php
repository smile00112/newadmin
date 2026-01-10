<?php

namespace Webkul\Newsletters\Services;

use Webkul\Newsletters\Models\AccountWarming;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Models\AccountWarmingParticipant;
use Webkul\Newsletters\Services\GreenAPIService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AccountWarmingService
{
    /**
     * Process one warming cycle - select two random accounts and exchange messages.
     *
     * @param AccountWarming $warming
     * @return bool
     */
    public function processWarmingCycle(AccountWarming $warming): bool
    {
        // Get active accounts
        $accountIds = $warming->selected_account_ids ?? [];
        $accounts = VacapInstance::whereIn('id', $accountIds)
            ->where('active', true)
            ->where('blocked', false)
            ->get();

        if ($accounts->count() < 2) {
            Log::warning('Not enough active accounts for warming', [
                'account_warming_id' => $warming->id,
                'active_accounts' => $accounts->count(),
            ]);
            return false;
        }

        // Select two random accounts with priority to less active ones
        $selectedAccounts = $this->selectTwoAccounts($warming, $accounts);

        if (count($selectedAccounts) < 2) {
            Log::warning('Failed to select two accounts for warming', [
                'account_warming_id' => $warming->id,
            ]);
            return false;
        }

        $account1 = $selectedAccounts[0];
        $account2 = $selectedAccounts[1];

        // Select random phrase pair
        $phrases = $warming->phrases ?? [];
        if (empty($phrases)) {
            Log::warning('No phrases available for warming', [
                'account_warming_id' => $warming->id,
            ]);
            return false;
        }

        $randomPhrase = $phrases[array_rand($phrases)];
        $question = $randomPhrase['question'] ?? '';
        $answer = $randomPhrase['answer'] ?? '';

        if (empty($question) || empty($answer)) {
            Log::warning('Invalid phrase pair for warming', [
                'account_warming_id' => $warming->id,
            ]);
            return false;
        }

        // Send question from account1 to account2
        $message1Sent = $this->sendMessage($account1, $account2, $question, $warming);

        if ($message1Sent) {
            // Update participants for first message
            $this->updateParticipants($warming, $account1->id, $account2->id, true);

            // Calculate delay before sending answer
            $delay = $this->calculateMessageDelay($warming);

            Log::info('Warming cycle first message sent, scheduling answer', [
                'account_warming_id' => $warming->id,
                'account1_id' => $account1->id,
                'account2_id' => $account2->id,
                'delay_seconds' => $delay,
            ]);

            // Schedule answer message (in real implementation, this would be a separate job)
            // For now, we'll dispatch it with delay
            \Webkul\Newsletters\Jobs\SendWarmingAnswer::dispatch(
                $warming->id,
                $account2->id,
                $account1->id,
                $answer
            )->delay(now()->addSeconds($delay))->onQueue('account-warming');
        }

        // Return delay for job scheduling
        return true;
    }

    /**
     * Select two accounts with priority to less active ones.
     */
    protected function selectTwoAccounts(AccountWarming $warming, $accounts): array
    {
        if ($accounts->count() < 2) {
            return [];
        }

        // Get participants with message counts
        $participants = AccountWarmingParticipant::where('account_warming_id', $warming->id)
            ->whereIn('whatsapp_instance_id', $accounts->pluck('id'))
            ->get()
            ->keyBy('whatsapp_instance_id');

        // Sort accounts by total messages (sent + received)
        $accountsWithCounts = $accounts->map(function ($account) use ($participants) {
            $participant = $participants->get($account->id);
            $totalMessages = ($participant ? $participant->messages_sent + $participant->messages_received : 0);
            return [
                'account' => $account,
                'total_messages' => $totalMessages,
            ];
        })->sortBy('total_messages')->values();

        // Select two accounts - prefer those with fewer messages
        $selected = [];
        
        // First, try to select from accounts with 0 messages
        $zeroMessageAccounts = $accountsWithCounts->filter(function ($item) {
            return $item['total_messages'] == 0;
        });

        if ($zeroMessageAccounts->count() >= 2) {
            $selected = $zeroMessageAccounts->random(2)->pluck('account')->toArray();
        } elseif ($zeroMessageAccounts->count() == 1) {
            $selected[] = $zeroMessageAccounts->first()['account'];
            // Select second from remaining accounts
            $remaining = $accountsWithCounts->filter(function ($item) use ($selected) {
                return $item['account']->id != $selected[0]->id;
            });
            if ($remaining->count() > 0) {
                $selected[] = $remaining->random()['account'];
            }
        } else {
            // All accounts have messages, select randomly but prefer those with fewer
            $selected = $accountsWithCounts->take(10)->random(min(2, $accountsWithCounts->count()))->pluck('account')->toArray();
        }

        return $selected;
    }

    /**
     * Send message from one account to another.
     */
    protected function sendMessage(VacapInstance $fromAccount, VacapInstance $toAccount, string $message, AccountWarming $warming): ?string
    {
        try {
            $greenApiService = new GreenAPIService($fromAccount->link_name, $fromAccount->login, $fromAccount->password);
            
            // Get phone number from toAccount
            $toPhone = $toAccount->phone;
            if (empty($toPhone)) {
                Log::error('Account has no phone number', [
                    'account_id' => $toAccount->id,
                ]);
                return null;
            }

            // Format phone number (remove + if present, ensure it's in correct format)
            $toPhone = preg_replace('/[^0-9]/', '', $toPhone);
            
            $response = $greenApiService->sendMessage($toPhone . '@c.us', $message);

            Log::info('Warming message sent successfully', [
                'account_warming_id' => $warming->id,
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'to_phone' => $toPhone,
                'response' => $response,
            ]);

            return $response['idMessage'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to send warming message', [
                'account_warming_id' => $warming->id,
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calculate delay between messages.
     */
    protected function calculateMessageDelay(AccountWarming $warming): int
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

    /**
     * Update participant statistics.
     */
    protected function updateParticipants(AccountWarming $warming, int $senderId, int $receiverId, bool $messageSent): void
    {
        if ($messageSent) {
            // Update sender
            DB::table('newsletters_account_warming_participants')
                ->where('account_warming_id', $warming->id)
                ->where('whatsapp_instance_id', $senderId)
                ->increment('messages_sent', 1, [
                    'last_message_at' => now(),
                    'updated_at' => now(),
                ]);

            // Update receiver
            DB::table('newsletters_account_warming_participants')
                ->where('account_warming_id', $warming->id)
                ->where('whatsapp_instance_id', $receiverId)
                ->increment('messages_received', 1, [
                    'last_message_at' => now(),
                    'updated_at' => now(),
                ]);
        }
    }
}

