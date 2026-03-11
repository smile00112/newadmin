<?php

namespace Webkul\Reporting\Listeners;

use Webkul\Reporting\Models\AnalyticsOrderTimestamp;
use Webkul\Reporting\Services\EventTracker;
use Illuminate\Support\Facades\Log;

class AnalyticsOrderListener
{
    public function __construct(
        protected EventTracker $tracker
    ) {}

    public function afterCreated($order): void
    {
        try {
            AnalyticsOrderTimestamp::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'channel'     => $order->channel_name ?? $order->channel ?? null,
                    'location_id' => $order->location_id ?? null,
                    'order_type'  => $order->order_type ?? 'dine_in',
                    'created_at'  => $order->created_at,
                ]
            );

            $this->tracker->track('order_created', [
                'order_id'    => $order->id,
                'channel'     => $order->channel_name ?? $order->channel ?? null,
                'customer_id' => $order->customer_id,
                'total'       => $order->base_grand_total,
                'items_count' => $order->items->count(),
            ], ['customer_id' => $order->customer_id, 'channel' => $order->channel_name ?? $order->channel]);
        } catch (\Throwable $e) {
            Log::warning('Analytics: afterCreated failed', ['order' => $order->id, 'error' => $e->getMessage()]);
        }
    }

    public function afterStatusUpdated($order): void
    {
        try {
            $ts = AnalyticsOrderTimestamp::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'channel'     => $order->channel_name ?? $order->channel ?? null,
                    'location_id' => $order->location_id ?? null,
                    'order_type'  => $order->order_type ?? 'dine_in',
                    'created_at'  => $order->created_at,
                ]
            );

            $now = now();
            $statusColumn = match ($order->status) {
                'processing'  => 'accepted_at',
                'preparing'   => 'preparing_at',
                'ready'       => 'ready_at',
                'completed'   => 'completed_at',
                'canceled', 'cancelled' => 'cancelled_at',
                default       => null,
            };

            if ($statusColumn && !$ts->{$statusColumn}) {
                $ts->{$statusColumn} = $now;

                $ts->total_seconds = $now->diffInSeconds($ts->created_at);

                if (in_array($order->status, ['ready', 'completed'])) {
                    $slaSeconds = config('analytics.sla_seconds', 420);
                    $ts->sla_seconds = $slaSeconds;
                    $ts->within_sla  = $ts->total_seconds <= $slaSeconds;
                }

                $ts->save();
            }

            $this->tracker->track('order_status_changed', [
                'order_id'   => $order->id,
                'status'     => $order->status,
                'channel'    => $order->channel_name ?? $order->channel ?? null,
                'total'      => $order->base_grand_total,
            ], ['customer_id' => $order->customer_id, 'channel' => $order->channel_name ?? $order->channel]);
        } catch (\Throwable $e) {
            Log::warning('Analytics: afterStatusUpdated failed', ['order' => $order->id, 'error' => $e->getMessage()]);
        }
    }

    public function afterCanceled($order): void
    {
        try {
            $ts = AnalyticsOrderTimestamp::where('order_id', $order->id)->first();

            if ($ts && !$ts->cancelled_at) {
                $ts->cancelled_at  = now();
                $ts->total_seconds = now()->diffInSeconds($ts->created_at);
                $ts->save();
            }

            $this->tracker->track('order_cancelled', [
                'order_id' => $order->id,
                'channel'  => $order->channel_name ?? $order->channel ?? null,
                'total'    => $order->base_grand_total,
                'reason'   => $order->cancel_reason ?? null,
            ], ['customer_id' => $order->customer_id, 'channel' => $order->channel_name ?? $order->channel]);
        } catch (\Throwable $e) {
            Log::warning('Analytics: afterCanceled failed', ['order' => $order->id, 'error' => $e->getMessage()]);
        }
    }
}
