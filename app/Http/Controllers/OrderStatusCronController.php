<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;

class OrderStatusCronController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository
    ) {
    }

    public function pendingToPreparing(): JsonResponse
    {
        $updatedCount = 0;

        Order::query()
            ->where('status', Order::STATUS_PENDING)
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$updatedCount): void {
                foreach ($orders as $order) {
                    $this->orderRepository->updateOrderStatus($order, Order::STATUS_PREPARING);
                    $updatedCount++;
                }
            });

        return response()->json([
            'success'       => true,
            'updated_count' => $updatedCount,
            'from_status'   => Order::STATUS_PENDING,
            'to_status'     => Order::STATUS_PREPARING,
        ]);
    }
}
