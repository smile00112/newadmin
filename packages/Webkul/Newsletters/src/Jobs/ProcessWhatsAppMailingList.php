<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Services\WhatsAppMailingService;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppMailingList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;

    protected $mailingListId;

    public function __construct(int $mailingListId)
    {
        $this->mailingListId = $mailingListId;
        $this->onQueue('whatsapp-mailing');
    }

    public function handle(WhatsAppMailingService $whatsappService)
    {
        Log::info('ProcessWhatsAppMailingList INFO', [
            'mailing_list_id' => $this->mailingListId,
            'all_lists' => MailingList::all(),
        ]);

        $mailingList = MailingList::with(['whatsappInstances', 'customerNumbers'])
            ->findOrFail($this->mailingListId);

        if (!$mailingList->active || $mailingList->whatsappInstances->isEmpty()) {
            Log::warning("Mailing list is inactive or has no WhatsApp instances", [
                'mailing_list_id' => $this->mailingListId
            ]);
            return;
        }

        // Check if we should start mailing based on time constraints
        if (!$this->shouldStartMailing($mailingList)) {
            // Reschedule job if time constraints not met
            $delay = $this->calculateDelayForMailing($mailingList);
            if ($delay > 0) {
                Log::info("Mailing list rescheduled due to time constraints", [
                    'mailing_list_id' => $this->mailingListId,
                    'delay_seconds' => $delay,
                    'rescheduled_at' => now()->addSeconds($delay)->toDateTimeString(),
                ]);

                ProcessWhatsAppMailingList::dispatch($this->mailingListId)
                    ->delay(now()->addSeconds($delay));
                return;
            }
        }

        // Process customers in batches
        $delay = $mailingList->message_delay ?? 5;
        $batchIndex = 0;

        $mailingList->customerNumbers()
           // ->whereNull('unsubscribed_at')
            ->chunk(100, function ($customers) use ($mailingList, &$batchIndex, $delay) {
                ProcessWhatsAppBatch::dispatch($mailingList->id, $customers->pluck('id')->toArray())
                    ->delay(now()->addSeconds($batchIndex * $delay))
                    ->onQueue('whatsapp-batch');

                $mailingList->update([
                    'status' => 'pending'
                ]);

                $batchIndex++;
            });
    }

    /**
     * Check if mailing should start based on time constraints.
     */
    protected function shouldStartMailing($mailingList): bool
    {
        $now = now();

        // Check start_at
        if ($mailingList->start_at) {
            $startAt = \Carbon\Carbon::parse($mailingList->start_at);
            if ($startAt->isFuture()) {
                return false;
            }
        }

        // Check mailing_hours_from and mailing_hours_to
        if ($mailingList->mailing_hours_from) {
            $currentTime = $now->format('H:i');
            $fromTime = $mailingList->mailing_hours_from;
            $toTime = $mailingList->mailing_hours_to;

            if ($currentTime < $fromTime) {
                return false;
            }

            if ($toTime && $currentTime > $toTime) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate delay in seconds if mailing should be postponed.
     */
    protected function calculateDelayForMailing($mailingList): int
    {
        $now = now();
        $delay = 0;

        // Check start_at
        if ($mailingList->start_at) {
            $startAt = \Carbon\Carbon::parse($mailingList->start_at);
            if ($startAt->isFuture()) {
                $secondsUntilStart = $now->diffInSeconds($startAt, false);
                if ($secondsUntilStart > 0) {
                    $delay = max($delay, $secondsUntilStart);
                }
            }
        }

        // Check mailing_hours_from
        if ($mailingList->mailing_hours_from) {
            $currentTime = $now->format('H:i');
            $fromTime = $mailingList->mailing_hours_from;
            $toTime = $mailingList->mailing_hours_to;

            if ($currentTime < $fromTime) {
                $hoursFromToday = $now->copy()->setTimeFromTimeString($mailingList->mailing_hours_from);
                $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
                if ($secondsUntilFrom > 0) {
                    $delay = max($delay, $secondsUntilFrom);
                }
            } elseif ($toTime && $currentTime > $toTime) {
                // If current time is after mailing_hours_to, schedule for tomorrow
                $hoursFromTomorrow = $now->copy()->addDay()->setTimeFromTimeString($mailingList->mailing_hours_from);
                $secondsUntilFrom = $now->diffInSeconds($hoursFromTomorrow, false);
                if ($secondsUntilFrom > 0) {
                    $delay = max($delay, $secondsUntilFrom);
                }
            }
        }

        return (int) $delay;
    }
}
