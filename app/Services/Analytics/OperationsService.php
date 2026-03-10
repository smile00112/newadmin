<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\DB;

/**
 * G. Operations & Service Quality
 *
 * All metrics derived from orders / order_items / refunds tables.
 * Stage times estimated from order status transitions via updated_at.
 */
class OperationsService extends BaseAnalyticsService
{
    /**
     * Stage times: estimated from orders with completed/ready/preparing statuses.
     * Uses created_at → updated_at diff for orders that reached each stage.
     */
    public function getStageTimes(): array
    {
        // Average processing time for different statuses
        $preparing = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereIn('status', ['preparing', 'ready', 'completed'])
            ->selectRaw('AVG(LEAST(TIMESTAMPDIFF(SECOND, created_at, updated_at), 7200)) as avg_seconds, COUNT(*) as cnt')
            ->first();

        $ready = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereIn('status', ['ready', 'completed'])
            ->selectRaw('AVG(LEAST(TIMESTAMPDIFF(SECOND, created_at, updated_at), 7200)) as avg_seconds, COUNT(*) as cnt')
            ->first();

        $completed = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'completed')
            ->selectRaw('AVG(LEAST(TIMESTAMPDIFF(SECOND, created_at, updated_at), 7200)) as avg_seconds, COUNT(*) as cnt')
            ->first();

        // Estimate stage durations
        $acceptTime = (int) ($preparing->avg_seconds ?? 0);
        $totalReady = (int) ($ready->avg_seconds ?? 0);
        $totalComplete = (int) ($completed->avg_seconds ?? 0);

        // Accept ≈ 30% of total preparing time, prepare ≈ 70%
        $orderToAccepted = (int) ($acceptTime * 0.3);
        $acceptedToReady = max(0, $totalReady - $orderToAccepted);
        $readyToServed = max(0, $totalComplete - $totalReady);
        $totalAvg = $totalComplete ?: $totalReady ?: $acceptTime;

        $sampleSize = (int) ($completed->cnt ?? 0) + (int) ($ready->cnt ?? 0);

        return [
            'order_to_accepted' => [
                'seconds'   => $orderToAccepted,
                'formatted' => gmdate('i:s', $orderToAccepted),
            ],
            'accepted_to_ready' => [
                'seconds'   => $acceptedToReady,
                'formatted' => gmdate('i:s', $acceptedToReady),
            ],
            'ready_to_served' => [
                'seconds'   => $readyToServed,
                'formatted' => gmdate('i:s', $readyToServed),
            ],
            'total_avg' => [
                'seconds'   => $totalAvg,
                'formatted' => gmdate('i:s', $totalAvg),
            ],
            'sample_size' => $sampleSize,
        ];
    }

    /**
     * Incorrect orders rate: orders that were canceled after processing started.
     */
    public function getIncorrectOrdersRate(): array
    {
        $totalOrders = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['cancelled', 'failed', 'fraud'])
            ->count();

        // Canceled orders that had been in processing/preparing = "incorrect"
        $incorrect = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'canceled')
            ->where('updated_at', '>', DB::raw('DATE_ADD(created_at, INTERVAL 5 MINUTE)'))
            ->count();

        return [
            'rate'       => $this->safeDiv($incorrect, $totalOrders, 4) * 100,
            'count'      => $incorrect,
            'total'      => $totalOrders,
        ];
    }

    public function getCancelRefundRate(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        $this->applyDimensionFilters($query, 'channel_name');

        $total = (clone $query)->count();
        $cancelled = (clone $query)->where('status', 'canceled')->count();

        $refunded = DB::table('refunds')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();

        return [
            'cancel_rate' => $this->safeDiv($cancelled, $total, 4) * 100,
            'refund_rate' => $this->safeDiv($refunded, $total, 4) * 100,
            'combined'    => $this->safeDiv($cancelled + $refunded, $total, 4) * 100,
            'cancelled'   => $cancelled,
            'refunded'    => $refunded,
            'total'       => $total,
        ];
    }

    /**
     * Order heatmap: orders by hour and day of week.
     */
    public function getOrderHeatmap(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['cancelled', 'failed', 'fraud']);

        $this->applyDimensionFilters($query, 'channel_name');

        return $query
            ->selectRaw('DAYOFWEEK(created_at) as day_of_week, HOUR(created_at) as hour, COUNT(*) as orders')
            ->groupBy('day_of_week', 'hour')
            ->orderBy('day_of_week')
            ->orderBy('hour')
            ->get()
            ->toArray();
    }

    /**
     * Hand-off delays: time from "ready" status to "completed" (served).
     */
    public function getHandoffDelays(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'completed');

        $this->applyDimensionFilters($query, 'channel_name');

        // Use updated_at - created_at for completed orders as proxy for total time
        $stats = $query->selectRaw("
            AVG(LEAST(TIMESTAMPDIFF(SECOND, created_at, updated_at), 7200)) as avg_delay,
            MAX(LEAST(TIMESTAMPDIFF(SECOND, created_at, updated_at), 7200)) as max_delay,
            MIN(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as min_delay,
            COUNT(*) as sample_size
        ")->first();

        return [
            'avg_seconds' => (int) ($stats->avg_delay ?? 0),
            'max_seconds' => (int) ($stats->max_delay ?? 0),
            'min_seconds' => (int) ($stats->min_delay ?? 0),
            'formatted'   => gmdate('i:s', (int) ($stats->avg_delay ?? 0)),
            'sample_size' => (int) $stats->sample_size,
        ];
    }
}
