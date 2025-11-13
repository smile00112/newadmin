<?php

namespace Webkul\Newsletters\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhatsAppMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $mailingListId;
    public int $customerId;
    public int|null $instanceId;
    public string $messageId;
    public string $sentAt;

    public function __construct(
        int $mailingListId,
        int $customerId,
        int|null $instanceId,
        string $messageId
    ) {
        $this->mailingListId = $mailingListId;
        $this->customerId = $customerId;
        $this->instanceId = $instanceId;
        $this->messageId = $messageId;
        $this->sentAt = now()->toISOString();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('mailing-list.' . $this->mailingListId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'mailing_list_id' => $this->mailingListId,
            'customer_id' => $this->customerId,
            'instance_id' => $this->instanceId,
            'message_id' => $this->messageId,
            'sent_at' => $this->sentAt,
        ];
    }

    public function shouldQueue(): bool
    {
        return true;
    }

    public function broadcastQueue(): string
    {
        return 'broadcastable';
    }
}

