<?php

namespace Webkul\Reporting\Jobs;

use Webkul\Reporting\Models\AnalyticsOrderTimestamp;
use Webkul\Reporting\Services\EventTracker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webkul\Sales\Models\Order;

class TrackOrderCreatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        protected int $orderId
    ) {
        $this->onQueue('default');
    }

    public function handle(EventTracker $tracker): void
    {
        $order = Order::with('items')->find($this->orderId);

        if (! $order) {
            return;
        }

        AnalyticsOrderTimestamp::updateOrCreate(
            ['order_id' => $order->id],
            [
                'channel'     => $order->channel_name ?? $order->channel ?? null,
                'location_id' => $order->location_id ?? null,
                'order_type'  => $order->order_type ?? 'dine_in',
                'created_at'  => $order->created_at,
            ]
        );

        $tracker->track('order_created', [
            'order_id'    => $order->id,
            'channel'     => $order->channel_name ?? $order->channel ?? null,
            'customer_id' => $order->customer_id,
            'total'       => $order->base_grand_total,
            'items_count' => $order->items->count(),
        ], ['customer_id' => $order->customer_id, 'channel' => $order->channel_name ?? $order->channel]);
    }

    public function failed(\Throwable $e): void
    {
        Log::warning('Analytics: TrackOrderCreatedJob failed', [
            'order_id' => $this->orderId,
            'message'  => $e->getMessage(),
        ]);
    }
}
