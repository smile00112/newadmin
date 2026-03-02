<?php

use Illuminate\Support\Facades\Broadcast;
use Webkul\Sales\Models\Order;
use Webkul\Customer\Models\Customer;

/**
 * Broadcast channel authorization routes.
 */

// Канал для конкретного заказа пользователя
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    
    if (!$order) {
        return false;
    }
    
    // Проверяем, что заказ принадлежит пользователю
    // Order использует morphTo для customer, поэтому проверяем через customer_id и customer_type
    if ($order->customer_type === Customer::class && $order->customer_id) {
        return (int) $order->customer_id === (int) $user->id;
    }
    
    return false;
});

// Канал для всех заказов конкретного пользователя
Broadcast::channel('customer.{customerId}.orders', function ($user, $customerId) {
    return (int) $user->id === (int) $customerId;
});
