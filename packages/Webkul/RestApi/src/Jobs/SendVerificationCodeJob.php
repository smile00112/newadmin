<?php

namespace Webkul\RestApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendVerificationCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $backoff = 5;

    public function __construct(
        protected string $channel,
        protected string $identifier,
        protected string $code,
        protected ?string $channelCode = null
    ) {
        $this->onQueue('auth');
    }

    public function handle(): void
    {
        $sent = match ($this->channel) {
            'sms'      => app(\Webkul\RestApi\Services\Auth\SmsService::class)
                            ->sendVerificationCode($this->identifier, $this->code, $this->channelCode),
            'whatsapp' => app(\Webkul\RestApi\Services\Auth\WhatsAppService::class)
                            ->sendVerificationCode($this->identifier, $this->code, $this->channelCode),
            'telegram' => app(\Webkul\RestApi\Services\Auth\TelegramService::class)
                            ->sendVerificationCode($this->identifier, $this->code, $this->channelCode),
            default    => false,
        };

        if (! $sent) {
            Log::error("SendVerificationCodeJob failed for {$this->channel}:{$this->identifier}");
        }
    }
}
