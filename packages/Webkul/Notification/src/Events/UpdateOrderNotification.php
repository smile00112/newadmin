<?php

namespace Webkul\Notification\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateOrderNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(protected $data) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = [];
        
        // Приватный канал для конкретного заказа
        if (isset($this->data['id'])) {
            $channels[] = new PrivateChannel('order.' . $this->data['id']);
        }
        
        // Приватный канал для всех заказов пользователя
        if (isset($this->data['customer_id'])) {
            $channels[] = new PrivateChannel('customer.' . $this->data['customer_id'] . '.orders');
        }
        
        return $channels;
    }

    /**
     * Broadcast with data.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->data;
    }

    /**
     * Separate queue.
     *
     * Command: `php artisan queue:work --queue=broadcastable`
     *
     * @return string
     */
    public function broadcastQueue()
    {
        return 'broadcastable';
    }

    /**
     * Get the channels the event should broadcast as.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'update-notification';
    }
}
