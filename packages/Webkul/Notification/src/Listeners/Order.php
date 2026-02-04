<?php

namespace Webkul\Notification\Listeners;

use Webkul\Notification\Events\CreateOrderNotification;
use Webkul\Notification\Events\UpdateOrderNotification;
use Webkul\Notification\Repositories\NotificationRepository;

class Order
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected NotificationRepository $notificationRepository) {}

    /**
     * Create a new resource.
     *
     * @return void
     */
    public function createOrder($order)
    {
        $this->notificationRepository->create(['type' => 'order', 'order_id' => $order->id]);

        event(new CreateOrderNotification);
    }

    /**
     * Fire an Event when the order status is updated.
     *
     * @return void
     */
    public function updateOrder($order)
    {
        // Получаем customer_id из заказа
        $customerId = null;
        if ($order->customer_id && $order->customer_type) {
            $customerId = $order->customer_id;
        }
        
        event(new UpdateOrderNotification([
            'id'          => $order->id,
            'status'      => $order->status,
            'customer_id' => $customerId,
            'updated_at'  => $order->updated_at->toIso8601String(),
        ]));
    }
}
