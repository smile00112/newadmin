<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Models\CustomerNumber;
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
        $customer = CustomerNumber::findOrFail($this->customerId);

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

            $instance = VacapInstance::findOrFail($this->instanceId);
            $message_id = $whatsappService->sendMessage($instance, $customer->phone_number, $this->message);

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
            throw new \Exception("Failed to send WhatsApp message");
        }


        if ($message_id) {
            Log::info("WhatsApp message sent successfully", [
                'instance_id' => $this->instanceId,
                'phone' => $customer->phone_number,
                'message_id' => $message_id
            ]);

            //привязываем инстанс к сообщению
            //присваиваем сообщению номер из greenapi
            $customer->update([
                'greenapi_chat_id' => $message_id,
                'whatsapp_instance_id' => $instance->id
            ]);

            //заносим в стоп лист
            \Webkul\Newsletters\Models\StopList::create(['phone_number' => $customer->phone_number]);

        } else {
            Log::error("Failed to send WhatsApp message", [
                'instance_id' => $this->instanceId,
                'phone' => $customer->phone_number
            ]);
            throw new \Exception("Failed to send WhatsApp message");
        }
    }
}
