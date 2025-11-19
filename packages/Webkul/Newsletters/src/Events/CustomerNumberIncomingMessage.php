<?php

namespace Webkul\Newsletters\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\Newsletters\Models\CustomerNumber;

class CustomerNumberIncomingMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CustomerNumber $customerNumber;

    /**
     * Create a new event instance.
     */
    public function __construct(CustomerNumber $customerNumber)
    {
        $this->customerNumber = $customerNumber;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('customer-numbers'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'customer-number.incoming-message';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'customer_number_id' => $this->customerNumber->id,
            'mailing_list_id' => $this->customerNumber->mailing_list_id,
            'phone_number' => $this->customerNumber->phone_number,
            'name' => $this->customerNumber->name,
            'incoming_message' => (bool) $this->customerNumber->incoming_message,
            'timestamp' => now()->toISOString(),
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







