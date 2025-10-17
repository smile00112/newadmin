<?php

namespace Webkul\Newsletters\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StartWhatsAppCampaign implements ShouldQueue
{
    use Dispatchable;

    public $campaignId;
    public $message;

    public function __construct($campaignId, $message)
    {
        $this->campaignId = $campaignId;
        $this->message = $message;
    }

    public function handle()
    {
        // Получаем получателей пачками по 1000
        $recipientsChunks = \App\Models\Recipient::where('campaign_id', $this->campaignId)
            ->cursor()
            ->chunk(1000);

        foreach ($recipientsChunks as $chunk) {
            // Создаем задание для пачки получателей
            ProcessRecipientsBatch::dispatch($chunk->pluck('phone')->toArray(), $this->message);
        }
    }
}
