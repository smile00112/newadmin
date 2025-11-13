<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Events\MailingListStatsUpdated;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Services\WhatsAppMailingService;
use Webkul\Newsletters\Events\WhatsAppMessageSent;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageWithEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    public function __construct(
        protected int $instanceId,
        protected int $customerId,
        protected string $message,
        protected int $mailingListId
    )
    {
        $this->onQueue('whatsapp-send');
    }

    public function handle(WhatsAppMailingService $whatsappService)
    {
        $customer = CustomerNumber::with('mailingList')->findOrFail($this->customerId);

        Log::info("SendWhatsAppMessageWithEvent handle", [
            'instance_id' => $this->instanceId,
            'customer_id' => $this->customerId,
            'mailing_list_id' => $this->mailingListId,
        ]);

        if (!$customer) {
            Log::error("Customer not found", [
                'customer_id' => $this->customerId,
            ]);
            return;
        }

        try {

            $instance = VacapInstance::findOrFail($this->instanceId);
            $mailingList = $customer->mailingList ?? MailingList::findOrFail($this->mailingListId);

            $message_id = null;

            // Проверяем наличие медиа файлов
            if ($mailingList && $mailingList->message_links && !empty($mailingList->message_links)) {
                $media = $mailingList->message_links[0];
                $fileUrl = $media['url'];

                if (!preg_match('/^https?:\/\//', $fileUrl)) {
                    $fileUrl = url($fileUrl);
                }

                $fileName = $media['original_name'] ?? basename($media['path'] ?? 'file');

                $mediaMessageId = $whatsappService->sendFileByUrl(
                    $instance,
                    $customer->phone_number,
                    $fileUrl,
                    $fileName,
                    $this->message
                );

                if ($mediaMessageId) {
                    $message_id = $mediaMessageId;
                } else {
                    $message_id = $whatsappService->sendMessage($instance, $customer->phone_number, $this->message);
                }
            } else {
                $message_id = $whatsappService->sendMessage($instance, $customer->phone_number, $this->message);
            }

            if ($message_id) {
                // Обновляем данные клиента
                $customer->update([
                    'greenapi_chat_id' => $message_id,
                    'whatsapp_instance_id' => $instance->id,
                    'sending' => true,
                    'delivered' => true,
                ]);

                // Заносим в стоп-лист
                \Webkul\Newsletters\Models\StopList::firstOrCreate([
                    'phone_number' => $customer->phone_number
                ]);


                // Вызываем событие для фронта
//                broadcast(new WhatsAppMessageSent(
//                    $this->mailingListId,
//                    $this->customerId,
//                    $this->instanceId,
//                    $message_id
//                ));

                Log::info("WhatsApp message sent and event broadcasted", [
                    'customer_id' => $this->customerId,
                    'instance_id' => $this->instanceId,
                    'message_id' => $message_id,
                ]);
            } else {
                $customer->update([
                    'sending' => true,
                    'send_error' => true,
                ]);

                Log::error("Failed to send WhatsApp message", [
                    'customer_id' => $this->customerId,
                    'instance_id' => $this->instanceId,
                ]);
            }

            //событие для обновления данных на фронте
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


        } catch (\Exception $e) {
            $customer->update([
                'sending' => false,
                'send_error' => true,
            ]);

            Log::error("Exception in SendWhatsAppMessageWithEvent", [
                'customer_id' => $this->customerId,
                'instance_id' => $this->instanceId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

