<?php

namespace Webkul\Newsletters\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Models\MailingList;

class MailingListCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $mailingListId;
    public ?string $completedAt;
    public ?int $sentCount;

    public function __construct(MailingList $mailingList)
    {
        $this->mailingListId = $mailingList->id;
        $this->completedAt = now()->toISOString();
        $this->sentCount = $mailingList->sent_count ?? null;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('mailing-list.' . $this->mailingListId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'mailing-list.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'mailing_list_id' => $this->mailingListId,
            'completed_at' => $this->completedAt,
            'sent_count' => $this->sentCount,
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





