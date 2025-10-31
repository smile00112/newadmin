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

    protected int $instanceId;
    protected CustomerNumber $customer;
    protected string $message;

    public function __construct(int $instanceId, CustomerNumber $customer, string $message)
    {
        $this->customer = $customer;
        $this->instanceId = $instanceId;
        //$this->phoneNumber = $phoneNumber;
        $this->message = $message;
        $this->onQueue('whatsapp-send');
    }

    public function handle(WhatsAppMailingService $whatsappService)
    {
        Log::info("SendWhatsAppMessage handle", [
            'instance_id' => $this->instanceId,
            'customer' => $this->customer,
            'message' => $this->message
        ]);

        try {
            $instance = VacapInstance::findOrFail($this->instanceId);
            $message_id = $whatsappService->sendMessage($instance, $this->customer->phone_number, $this->message);
        }
        catch (\Exception $e) {
            Log::error("!Failed to send WhatsApp message!", [
                'instance_id' => $this->instanceId,
                'customer' => $this->customer,
                'error' => $e->getMessage()
            ]);
            throw new \Exception("Failed to send WhatsApp message");
        }


        if ($message_id) {
            Log::info("WhatsApp message sent successfully", [
                'instance_id' => $this->instanceId,
                'phone' => $this->customer->phone_number,
                'message_id' => $message_id
            ]);

            //привязываем инстанс к сообщению
            //присваиваем сообщению номер из greenapi
            $this->customer->update([
                'greenapi_chat_id' => $message_id,
                'whatsapp_instance_id' => $instance->id
            ]);

            //заносим в стоп лист
            \Webkul\Newsletters\Models\StopList::create(['phone_number' => $this->customer->phone_number]);
        } else {
            Log::error("Failed to send WhatsApp message", [
                'instance_id' => $this->instanceId,
                'phone' => $this->customer->phone_number
            ]);
            throw new \Exception("Failed to send WhatsApp message");
        }
    }
}
