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
        $mailingList = MailingList::with(['whatsappInstances', 'customerNumbers'])
            ->findOrFail($this->mailingListId);

        if (!$mailingList->active || $mailingList->whatsappInstances->isEmpty()) {
            Log::warning("Mailing list is inactive or has no WhatsApp instances", [
                'mailing_list_id' => $this->mailingListId
            ]);
            return;
        }

        // Process customers in batches of 1000
        $mailingList->customerNumbers()
            ->whereNull('unsubscribed_at')
            ->chunk(1000, function ($customers) use ($mailingList, $whatsappService) {
                ProcessWhatsAppBatch::dispatch($mailingList->id, $customers->pluck('id')->toArray())
                    ->onQueue('whatsapp-batch');
            });
    }
}