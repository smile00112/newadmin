<?php

namespace Webkul\Newsletters\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Models\VacapInstance;
use Webkul\Newsletters\Services\WhatsAppMailingService;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    protected $instanceId;
    protected $phoneNumber;
    protected $message;

    public function __construct(int $instanceId, string $phoneNumber, string $message)
    {
        $this->instanceId = $instanceId;
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
        $this->onQueue('whatsapp-send');
    }

    public function handle(WhatsAppMailingService $whatsappService)
    {
        $instance = VacapInstance::findOrFail($this->instanceId);
        
        $success = $whatsappService->sendMessage($instance, $this->phoneNumber, $this->message);
        
        if ($success) {
            Log::info("WhatsApp message sent successfully", [
                'instance_id' => $this->instanceId,
                'phone' => $this->phoneNumber
            ]);
        } else {
            Log::error("Failed to send WhatsApp message", [
                'instance_id' => $this->instanceId,
                'phone' => $this->phoneNumber
            ]);
            throw new \Exception("Failed to send WhatsApp message");
        }
    }
}