<?php

namespace Webkul\Newsletters\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Models\CustomerNumber;

class CustomerNumberMessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $customerNumber;
    public $mailingListId;

    /**
     * Create a new event instance.
     */
    public function __construct(CustomerNumber $customerNumber)
    {
        $this->customerNumber = $customerNumber;
        $this->mailingListId = $customerNumber->mailing_list_id;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('mailing-list.' . $this->mailingListId)
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'customer-number.message-read';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'customer_number_id' => $this->customerNumber->id,
            'phone_number' => $this->customerNumber->phone_number,
            'name' => $this->customerNumber->name,
            'incoming_message' => $this->customerNumber->incoming_message,
            'mailing_list_id' => $this->mailingListId,
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

