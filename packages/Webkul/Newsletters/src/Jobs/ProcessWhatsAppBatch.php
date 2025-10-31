<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Services\WhatsAppMailingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ProcessWhatsAppBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected $mailingListId;
    protected $customerIds;

    public function __construct(int $mailingListId, array $customerIds)
    {
        $this->mailingListId = $mailingListId;
        $this->customerIds = $customerIds;
        $this->onQueue('whatsapp-batch');
    }

    public function handle(WhatsAppMailingService $whatsappService)
    {
        $mailingList = MailingList::with('whatsappInstances')->findOrFail($this->mailingListId);
        $customers = CustomerNumber::whereIn('id', $this->customerIds)->get();

        if ($customers->isEmpty()) {
            return;
        }

        // Check if mailing hours allow sending
        if (!$this->isWithinMailingHours($mailingList)) {
            Log::info("Batch processing postponed due to mailing hours", [
                'mailing_list_id' => $this->mailingListId,
                'current_time' => now()->format('H:i'),
                'mailing_hours_from' => $mailingList->mailing_hours_from,
                'mailing_hours_to' => $mailingList->mailing_hours_to,
            ]);

            // Reschedule batch for next day if outside mailing hours
            if ($mailingList->mailing_hours_from) {
                $delay = $this->calculateDelayUntilNextMailingHour($mailingList);
                ProcessWhatsAppBatch::dispatch($this->mailingListId, $this->customerIds)
                    ->delay(now()->addSeconds($delay))
                    ->onQueue('whatsapp-batch');
            }
            return;
        }

        $instance = $whatsappService->getRandomInstance($mailingList);
        if (!$instance) {
            Log::error("No WhatsApp instance available for mailing list", [
                'mailing_list_id' => $this->mailingListId
            ]);
            return;
        }

        $messageDelay = $mailingList->message_delay ?? 5; // Delay between messages in seconds
        $messageIndex = 0;

        foreach ($customers as $customer) {
            // Check if we're still within mailing hours before sending each message
            if ($messageIndex > 0 && !$this->isWithinMailingHours($mailingList, $messageIndex * $messageDelay)) {
                // If sending this message would exceed mailing hours, schedule remaining for tomorrow
                $remainingCustomers = $customers->slice($messageIndex)->pluck('id')->toArray();
                if (!empty($remainingCustomers)) {
                    $delay = $this->calculateDelayUntilNextMailingHour($mailingList);
                    ProcessWhatsAppBatch::dispatch($this->mailingListId, $remainingCustomers)
                        ->delay(now()->addSeconds($delay))
                        ->onQueue('whatsapp-batch');

                    Log::info("Remaining messages scheduled for next mailing hour", [
                        'mailing_list_id' => $this->mailingListId,
                        'remaining_count' => count($remainingCustomers),
                        'scheduled_at' => now()->addSeconds($delay)->toDateTimeString(),
                    ]);
                }
                break;
            }

            // Check rate limit before each message
            if (!$whatsappService->checkRateLimit()) {
                // If rate limit exceeded, delay the remaining messages
                ProcessWhatsAppBatch::dispatch($this->mailingListId, [$customer->id])
                    ->delay(now()->addSecond())
                    ->onQueue('whatsapp-batch');
                continue;
            }

            // Send individual message with delay based on message_delay
            $randomWhatsappInstance = $whatsappService->makeRandomMessage($mailingList->message_text);
            SendWhatsAppMessage::dispatch($instance->id, $customer->id, $randomWhatsappInstance)
                ->delay(now()->addSeconds($messageIndex * $messageDelay))
                ->onQueue('whatsapp-send');

            $messageIndex++;
        }
    }

    /**
     * Check if current time is within mailing hours.
     */
    protected function isWithinMailingHours($mailingList, int $secondsFromNow = 0): bool
    {
        if (!$mailingList->mailing_hours_from) {
            return true; // No time restriction
        }

        $checkTime = now()->addSeconds($secondsFromNow);
        $currentTime = $checkTime->format('H:i');
        $fromTime = $mailingList->mailing_hours_from;
        $toTime = $mailingList->mailing_hours_to;

        if ($currentTime < $fromTime || ($toTime && $currentTime > $toTime)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate delay until next mailing hour starts.
     */
    protected function calculateDelayUntilNextMailingHour($mailingList): int
    {
        if (!$mailingList->mailing_hours_from) {
            return 0;
        }

        $now = now();
        $hoursFromTomorrow = $now->copy()->addDay()->setTimeFromTimeString($mailingList->mailing_hours_from);
        return (int) $now->diffInSeconds($hoursFromTomorrow, false);
    }
}
