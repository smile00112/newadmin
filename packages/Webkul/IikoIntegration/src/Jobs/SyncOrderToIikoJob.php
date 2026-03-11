<?php

namespace Webkul\IikoIntegration\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Services\IikoOrderService;
use Webkul\Sales\Models\Order;

class SyncOrderToIikoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        protected int $orderId
    ) {
        $this->onQueue('iiko');
    }

    public function handle(IikoOrderService $orderService): void
    {
        $order = Order::find($this->orderId);

        if (! $order) {
            Log::warning('iiko: Order not found for sync', ['order_id' => $this->orderId]);

            return;
        }

        $orderService->syncOrderToIiko($order);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('iiko: SyncOrderToIikoJob permanently failed', [
            'order_id' => $this->orderId,
            'message'  => $e->getMessage(),
        ]);
    }
}
