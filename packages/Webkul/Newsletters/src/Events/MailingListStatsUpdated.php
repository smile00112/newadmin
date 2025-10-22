<?php

namespace Webkul\Newsletters\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MailingListStatsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mailingListId;
    public $stats;

    /**
     * Create a new event instance.
     */
    public function __construct(int $mailingListId, array $stats)
    {
        $this->mailingListId = $mailingListId;
        $this->stats = $stats;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('mailing-lists-stats')
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'stats-updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'mailing_list_id' => $this->mailingListId,
            'stats' => $this->stats,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Determine if this event should be queued.
     */
    public function shouldQueue(): bool
    {
        return true;
    }

    /**
     * Get the queue the event should be dispatched on.
     */
    public function broadcastQueue(): string
    {
        return 'broadcastable';
    }
}
