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

        $instance = $whatsappService->getRandomInstance($mailingList);
        if (!$instance) {
            Log::error("No WhatsApp instance available for mailing list", [
                'mailing_list_id' => $this->mailingListId
            ]);
            return;
        }

        foreach ($customers as $customer) {
            // Check rate limit before each message
            if (!$whatsappService->checkRateLimit()) {
                // If rate limit exceeded, delay the remaining messages
                ProcessWhatsAppBatch::dispatch($this->mailingListId, [$customer->id])
                    ->delay(now()->addSecond())
                    ->onQueue('whatsapp-batch');
                continue;
            }

            // Send individual message
            SendWhatsAppMessage::dispatch($instance->id, $customer->phone_number, $mailingList->message_text)
                ->onQueue('whatsapp-send');
        }
    }
}