<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Events\MailingListStatsUpdated;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Services\MailingChannelFactory;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;
use Illuminate\Support\Facades\Log;

class ProcessMailingBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected int $mailingListId;
    protected array $customerIds;
    protected int $batchIndex;
    protected ?int $companyId;

    public function __construct(int $mailingListId, array $customerIds, int $batchIndex = 0, ?int $companyId = null)
    {
        $this->mailingListId = $mailingListId;
        $this->customerIds = $customerIds;
        $this->batchIndex = $batchIndex;
        $this->companyId = $companyId;
        $this->onQueue('mailing-batch');
    }

    public function handle()
    {
        Log::info('ProcessMailingBatch started', [
            'mailing_list_id' => $this->mailingListId,
            'customer_ids_count' => count($this->customerIds),
            'batch_index' => $this->batchIndex,
        ]);

        $mailingList = MailingList::findOrFail($this->mailingListId);
        $channelType = $mailingList->channel_type ?? 'whatsapp';

        // For WhatsApp, use the existing specialized job
        if ($channelType === 'whatsapp') {
            ProcessWhatsAppBatchByInstances::dispatch(
                $this->mailingListId,
                $this->customerIds,
                $this->batchIndex,
                $this->companyId
            )->onQueue('whatsapp-batch-instances');
            return;
        }

        // Create channel handler
        $channel = MailingChannelFactory::create($channelType);

        // Verify company access
        if ($this->companyId !== null && $mailingList->company_id !== $this->companyId) {
            Log::warning("Mailing list does not belong to company", [
                'mailing_list_id' => $this->mailingListId,
                'expected_company_id' => $this->companyId,
                'actual_company_id' => $mailingList->company_id,
            ]);
            return;
        }

        // Check account balance
        if ($mailingList->company_id) {
            $accountRepository = app(CompanyAccountRepository::class);
            $account = $accountRepository->getOrCreateForCompany($mailingList->company_id);
            if ($account->balance <= 0) {
                Log::warning("Account balance is insufficient, stopping mailing", [
                    'mailing_list_id' => $this->mailingListId,
                    'company_id' => $mailingList->company_id,
                    'balance' => $account->balance,
                ]);
                $mailingList->update(['active' => false, 'status' => 'paused']);
                return;
            }
        }

        // Get active instances for the channel
        $instances = $channel->getActiveInstances($mailingList);
        if (!$mailingList->active || $instances->isEmpty()) {
            Log::warning("Mailing list is inactive or has no active instances", [
                'mailing_list_id' => $this->mailingListId,
                'channel_type' => $channelType,
            ]);
            return;
        }

        // Check mailing hours
        if (!$this->isWithinMailingHours($mailingList)) {
            Log::info("Batch processing postponed due to mailing hours", [
                'mailing_list_id' => $this->mailingListId,
                'current_time' => now()->format('H:i'),
            ]);

            if ($mailingList->mailing_hours_from) {
                $delay = $this->calculateDelayUntilNextMailingHour($mailingList);
                self::dispatch($this->mailingListId, $this->customerIds, $this->batchIndex, $this->companyId)
                    ->delay(now()->addSeconds($delay))
                    ->onQueue('mailing-batch');
            }
            return;
        }

        // Get customers to process
        $customersQuery = CustomerNumber::whereIn('id', $this->customerIds)
            ->where('sending', false)
            ->where('send_error', false);

        if ($this->companyId !== null) {
            $customersQuery->where('company_id', $this->companyId);
        }

        $customers = $customersQuery->get();

        if ($customers->isEmpty()) {
            Log::info("No customers to process in batch", [
                'mailing_list_id' => $this->mailingListId,
                'batch_index' => $this->batchIndex,
            ]);
            return;
        }

        $instancesCount = $instances->count();
        $batchSize = min($instancesCount, $customers->count());
        $batchCustomers = $customers->take($batchSize);
        $remainingCustomers = $customers->skip($batchSize);

        Log::info('Processing batch', [
            'mailing_list_id' => $this->mailingListId,
            'channel_type' => $channelType,
            'batch_size' => $batchSize,
            'instances_count' => $instancesCount,
            'batch_index' => $this->batchIndex,
            'remaining_customers' => $remainingCustomers->count(),
        ]);

        $instanceIndex = 0;

        foreach ($batchCustomers as $customer) {
            // Validate recipient for this channel
            if (!$channel->validateRecipient($customer)) {
                Log::warning('Invalid recipient for channel', [
                    'channel_type' => $channelType,
                    'customer_id' => $customer->id,
                ]);
                $customer->update(['sending' => true, 'send_error' => true]);
                $this->broadcastStats($mailingList);
                continue;
            }

            // Select instance round-robin
            $instance = $instances->values()->get($instanceIndex % $instancesCount);
            $instanceIndex++;

            // Generate random message
            $randomMessage = $this->makeRandomMessage($mailingList->message_text);

            // Dispatch the send job
            SendChannelMessage::dispatch(
                $channelType,
                $instance->id,
                $customer->id,
                $randomMessage,
                $this->mailingListId
            )->onQueue('mailing-send');
        }

        // Schedule next batch if there are remaining customers
        if ($remainingCustomers->isNotEmpty()) {
            $messageDelay = $this->calculateMessageDelay($mailingList);
            $nextBatchIndex = $this->batchIndex + 1;
            $remainingCustomerIds = $remainingCustomers->pluck('id')->toArray();

            Log::info('Scheduling next batch', [
                'mailing_list_id' => $this->mailingListId,
                'next_batch_index' => $nextBatchIndex,
                'remaining_customers_count' => count($remainingCustomerIds),
                'delay_seconds' => $messageDelay,
            ]);

            self::dispatch($this->mailingListId, $remainingCustomerIds, $nextBatchIndex, $this->companyId)
                ->delay(now()->addSeconds($messageDelay))
                ->onQueue('mailing-batch');
        } else {
            $mailingList->update(['status' => 'completed']);
            Log::info('All batches processed', [
                'mailing_list_id' => $this->mailingListId,
                'last_batch_index' => $this->batchIndex,
            ]);
        }
    }

    protected function broadcastStats(MailingList $mailingList): void
    {
        $mailingListStats = $mailingList->with('customerNumbers')->withCount([
            'customerNumbers as numbers_delivered' => function ($query) {
                $query->where('sending', true)->orWhere('send_error', true);
            },
            'customerNumbers as numbers_viewed' => function ($query) {
                $query->where('viewed', true);
            },
            'customerNumbers as incoming_messages_count' => function ($query) {
                $query->where('incoming_message', true);
            }
        ])->find($mailingList->id);

        $stats = [
            'sent_count' => (int) $mailingListStats->numbers_delivered,
            'incoming_count' => (int) $mailingListStats->incoming_messages_count,
            'viewed_count' => (int) $mailingListStats->numbers_viewed,
            'total_count' => (int) $mailingListStats->customerNumbers->count()
        ];

        broadcast(new MailingListStatsUpdated($mailingList->id, $stats));
    }

    protected function makeRandomMessage(string $text): string
    {
        return preg_replace_callback('/\{([^}]+)\}/', function ($matches) {
            $options = explode('|', $matches[1]);
            return $options[array_rand($options)];
        }, $text);
    }

    protected function isWithinMailingHours($mailingList): bool
    {
        if (!$mailingList->mailing_hours_from) {
            return true;
        }

        $timezone = config('app.timezone', 'UTC');
        $checkTime = now()->setTimezone($timezone);

        $fromTime = $mailingList->mailing_hours_from;
        $toTime = $mailingList->mailing_hours_to;

        $fromMinutes = $this->timeToMinutes($fromTime);
        $currentMinutes = $checkTime->hour * 60 + $checkTime->minute;

        if (!$toTime) {
            return $currentMinutes >= $fromMinutes;
        }

        $toMinutes = $this->timeToMinutes($toTime);
        $spansMidnight = $toMinutes < $fromMinutes;

        if ($spansMidnight) {
            return $currentMinutes >= $fromMinutes || $currentMinutes <= $toMinutes;
        } else {
            return $currentMinutes >= $fromMinutes && $currentMinutes <= $toMinutes;
        }
    }

    protected function calculateDelayUntilNextMailingHour($mailingList): int
    {
        if (!$mailingList->mailing_hours_from) {
            return 0;
        }

        $timezone = config('app.timezone', 'UTC');
        $now = now()->setTimezone($timezone);
        $fromTime = $mailingList->mailing_hours_from;

        $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
        $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);

        if ($secondsUntilFrom > 0) {
            return $secondsUntilFrom;
        }

        $hoursFromTomorrow = $now->copy()->addDay()->setTimeFromTimeString($fromTime);
        return $now->diffInSeconds($hoursFromTomorrow, false);
    }

    protected function calculateMessageDelay($mailingList): int
    {
        $delayFrom = $mailingList->message_delay_from;
        $delayTo = $mailingList->message_delay_to;

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

        return 5;
    }

    protected function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return (int) $hours * 60 + (int) $minutes;
    }
}



