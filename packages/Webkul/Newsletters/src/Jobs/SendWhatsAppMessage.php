<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Admin\Services\FcmNotificationService;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Models\CustomerNumber;
use Webkul\Newsletters\Models\MailingList;
use Webkul\Newsletters\Services\WhatsAppMailingService;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    public function __construct(
        protected int $instanceId,
        protected int $customerId,
        protected string $message
    )
    {
        //...property promotion)
        $this->onQueue('whatsapp-send');
    }

    public function handle(WhatsAppMailingService $whatsappService)
    {
        $customer = CustomerNumber::with('mailingList')->findOrFail($this->customerId);

        //временный лог
        Log::info("SendWhatsAppMessage handle", [
            'instance_id' => $this->instanceId,
            'customer' => $customer,
            'message' => $this->message
        ]);

        if(!$customer){
            Log::info("SendWhatsAppMessage handle ERROR", [
                'instance_id' => $this->instanceId,
                'customer' => $customer,
                'message' => $this->message
            ]);
            return;
        }

        try {
            $customer->update([
                'sending' => true
            ]);

            $instance = VacapInstance::where('active', true)
                ->where('blocked', false)
                ->findOrFail($this->instanceId);
            $mailingList = $customer->mailingList;
            
            // Проверяем лимит перед отправкой
            if ($mailingList && $mailingList->max_messages_per_instance) {
                if ($instance->sending_message_count >= $mailingList->max_messages_per_instance) {
                    Log::warning('Instance blocked, cannot send message', [
                        'instance_id' => $instance->id,
                        'sending_message_count' => $instance->sending_message_count,
                        'max_messages_per_instance' => $mailingList->max_messages_per_instance,
                    ]);
                    $instance->update(['blocked' => true]);
                    $customer->update([
                        'send_error' => true
                    ]);
                    return;
                }
            }

            $message_id = null;

            // Проверяем наличие медиа файлов в message_links
            if ($mailingList && $mailingList->message_links && !empty($mailingList->message_links)) {
                $media = $mailingList->message_links[0];

                // Получаем полный URL для файла
                $fileUrl = $media['url'];

                // Если URL относительный, делаем его абсолютным
                if (!preg_match('/^https?:\/\//', $fileUrl)) {
                    $fileUrl = url($fileUrl);
                }

                // Получаем имя файла из original_name или извлекаем из path
                $fileName = $media['original_name'] ?? basename($media['path'] ?? 'file');

                // Отправляем медиа файл с текстом сообщения как подписью
                $mediaMessageId = $whatsappService->sendFileByUrl(
                    $instance,
                    $customer->phone_number,
                    $fileUrl,
                    $fileName,
                    $this->message // Используем текст сообщения как подпись к медиа
                );

                if ($mediaMessageId) {
                    $message_id = $mediaMessageId;
                    Log::info("WhatsApp media sent successfully", [
                        'time' => date("H:i:s"),
                        'instance_id' => $this->instanceId,
                        'phone' => $customer->phone_number,
                        'media_url' => $fileUrl,
                        'message_id' => $message_id
                    ]);
                } else {
                    // Если медиа не отправилось, отправляем только текст
                    Log::warning("Failed to send media, falling back to text message", [
                        'instance_id' => $this->instanceId,
                        'phone' => $customer->phone_number,
                        'media_url' => $fileUrl
                    ]);
                    $message_id = $whatsappService->sendMessage($instance, $customer->phone_number, $this->message);
                }
            } else {
                // Если нет медиа, отправляем только текстовое сообщение
                $message_id = $whatsappService->sendMessage($instance, $customer->phone_number, $this->message);
            }

        }
        catch (\Exception $e) {
            $customer->update([
                'send_error' => true
            ]);

            Log::error("!Failed to send WhatsApp message!", [
                'instance_id' => $this->instanceId,
                'customer' => $customer,
                'error' => $e->getMessage()
            ]);
            //throw new \Exception("Failed to send WhatsApp message");
        }

        if ($message_id) {
            try {
                Log::info("WhatsApp message sent successfully 2", [
                    'time' => date("H:i:s"),
                    'instance_id' => $this->instanceId,
                    'phone' => $customer->phone_number,
                    'message_id' => $message_id,
                    'aaaa' => $customer->mailingList,
                    'bbb' => $customer->mailingList->id
                ]);
            }
            catch (\Exception $e) {
                Log::error("!!!!!", [
                    'instance_id' => $e->getMessage()
                ]);
            }


            //привязываем инстанс к сообщению
            //присваиваем сообщению номер из greenapi
            $customer->update([
                'greenapi_chat_id' => $message_id,
                'whatsapp_instance_id' => $instance->id
            ]);

            // Увеличиваем счетчик отправленных сообщений для инстанса
            $instance->increment('sending_message_count');
            
            // Проверяем лимит и блокируем инстанс при превышении
            if ($mailingList && $mailingList->max_messages_per_instance) {
                $instance->refresh(); // Обновляем данные инстанса после increment
                if ($instance->sending_message_count >= $mailingList->max_messages_per_instance) {
                    $instance->update(['blocked' => true]);
                    Log::warning('Instance blocked due to message limit exceeded', [
                        'instance_id' => $instance->id,
                        'sending_message_count' => $instance->sending_message_count,
                        'max_messages_per_instance' => $mailingList->max_messages_per_instance,
                        'mailing_list_id' => $mailingList->id,
                    ]);
                }
            }

            //заносим в стоп лист
            \Webkul\Newsletters\Models\StopList::create(['phone_number' => $customer->phone_number]);

            }
        else
        {
            Log::error("Failed to send WhatsApp message", [
                'instance_id' => $this->instanceId,
                'phone' => $customer->phone_number
            ]);

            // new \Exception("Failed to send WhatsApp message");
        }


        //смотрим конец ли рассылки и делаем уведомления
        $mailingList = $customer->mailingList;
        $remaining_customer_numbers_count = 0;
        if ($mailingList) {
            try {
                $remaining_customer_numbers_count = CustomerNumber::where('mailing_list_id', $mailingList->id)
                    ->where('sending', false)
                    ->where('send_error', false)
                    ->count();
            }
            catch (\Exception $e) {

                Log::error("ERROR remaining_customer_numbers_count", [
                    'total' => $remaining_customer_numbers_count,
                    'error' => $e->getMessage()
                ]);

            }

            $remainingCount = $mailingList->customerNumbers()
                ->where('sending', false)
                ->where('send_error', false)
                ->count();

            Log::info("Last message sent for CHECK", [
                'tatal' => $mailingList->customerNumbers()
                    ->count(),
                'remaining' => $mailingList->customerNumbers()
                    ->where('sending', false)
                    ->where('send_error', false)
                    ->count()
            ]);

            if ($remainingCount === 0) {
                // Это последнее сообщение!
                Log::info("Last message sent for mailing list", [
                    'mailing_list_id' => $mailingList->id,
                    'customer_id' => $customer->id
                ]);

                // Обновляем статус рассылки
                $mailingList->update([
                    'status' => 'completed',
                ]);

                // Broadcast completion event
                broadcast(new \Webkul\Newsletters\Events\MailingListCompleted($mailingList));

                // FCM уведомление
                try {
                    $fcm = app(FcmNotificationService::class);
                    if ($fcm) {
                        $fcm->sendToAllAdmins(
                            'Рассылка завершена',
                            'Рассылка #' . $mailingList->id . ' завершена',
                            ['type' => 'mailing.completed', 'mailing_list_id' => (string) $mailingList->id]
                        );
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send FCM for last message', ['error' => $e->getMessage()]);
                }
            }
        }

    }
}
