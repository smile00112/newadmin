<?php

namespace Webkul\Reporting\Services;

use Illuminate\Support\Facades\DB;

class NorthStarService extends BaseAnalyticsService
{
    public function getAll(): array
    {
        return [
            'online_order_share'  => $this->getOnlineOrderShare(),
            'aov'                 => $this->getAOV(),
            'gmv'                 => $this->getGMV(),
            'avg_order_ready'     => $this->getAvgOrderReadyTime(),
            'orders_within_sla'   => $this->getOrdersWithinSLA(),
            'repeat_rate'         => $this->getRepeatRate(),
            'total_orders'        => $this->getTotalOrders(),
        ];
    }

    public function getOnlineOrderShare(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud']);

        $this->applyDimensionFilters($query, 'channel_name');

        $total = (clone $query)->count();

        $online = (clone $query)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('order_payment')
                    ->whereColumn('order_payment.order_id', 'orders.id')
                    ->where('order_payment.method', '!=', 'cashondelivery');
            })
            ->count();

        $value = $this->safeDiv($online, $total, 4) * 100;

        return [
            'value'   => round($value, 2),
            'total'   => $total,
            'online'  => $online,
            'target'  => 90,
            'on_track' => $value >= 90,
        ];
    }

    public function getAOV(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud']);

        $this->applyDimensionFilters($query, 'channel_name');

        $stats = $query->selectRaw('COUNT(*) as cnt, COALESCE(SUM(base_grand_total), 0) as total')
            ->first();

        $current = $this->safeDiv($stats->total, $stats->cnt, 2);

        [$prevStart, $prevEnd] = $this->previousPeriodDates();
        $prevQuery = DB::table('orders')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud']);
        $this->applyDimensionFilters($prevQuery, 'channel_name');
        $prevStats = $prevQuery->selectRaw('COUNT(*) as cnt, COALESCE(SUM(base_grand_total), 0) as total')->first();
        $previous = $this->safeDiv($prevStats->total, $prevStats->cnt, 2);

        return [
            'value'    => $current,
            'previous' => $previous,
            'change'   => $this->percentChange($current, $previous),
        ];
    }

    public function getGMV(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud']);

        $this->applyDimensionFilters($query, 'channel_name');

        $current = (float) $query->sum('base_grand_total');

        [$prevStart, $prevEnd] = $this->previousPeriodDates();
        $prevQuery = DB::table('orders')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud']);
        $this->applyDimensionFilters($prevQuery, 'channel_name');
        $previous = (float) $prevQuery->sum('base_grand_total');

        return [
            'value'    => round($current, 2),
            'previous' => round($previous, 2),
            'change'   => $this->percentChange($current, $previous),
        ];
    }

    public function getAvgOrderReadyTime(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereIn('status', ['completed', 'ready'])
            ->whereRaw('updated_at > created_at');

        $this->applyDimensionFilters($query, 'channel_name');

        $avg = (int) $query->selectRaw('AVG(LEAST(TIMESTAMPDIFF(SECOND, created_at, updated_at), 7200)) as avg_sec')
            ->value('avg_sec');

        return [
            'seconds'   => $avg,
            'formatted' => gmdate('i:s', max($avg, 0)),
        ];
    }

    public function getOrdersWithinSLA(int $slaSeconds = 420): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereIn('status', ['completed', 'ready'])
            ->whereRaw('updated_at > created_at');

        $this->applyDimensionFilters($query, 'channel_name');

        $total = (clone $query)->count();
        $within = (clone $query)
            ->whereRaw('TIMESTAMPDIFF(SECOND, created_at, updated_at) <= ?', [$slaSeconds])
            ->count();
        $pct = $this->safeDiv($within, $total, 4) * 100;

        return [
            'value'    => round($pct, 2),
            'within'   => $within,
            'total'    => $total,
            'sla_seconds' => $slaSeconds,
        ];
    }

    public function getRepeatRate(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('customer_id');

        $this->applyDimensionFilters($query, 'channel_name');

        $sub = (clone $query)
            ->select('customer_id')
            ->selectRaw('COUNT(*) as order_count')
            ->groupBy('customer_id');

        $stats = DB::query()->fromSub($sub, 'c')
            ->selectRaw('COUNT(*) as total_customers, SUM(CASE WHEN order_count >= 2 THEN 1 ELSE 0 END) as repeat_customers')
            ->first();

        $pct = $this->safeDiv($stats->repeat_customers, $stats->total_customers, 4) * 100;

        return [
            'value'             => round($pct, 2),
            'repeat_customers'  => (int) $stats->repeat_customers,
            'total_customers'   => (int) $stats->total_customers,
        ];
    }

    public function getTotalOrders(): int
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud']);

        $this->applyDimensionFilters($query, 'channel_name');

        return $query->count();
    }

    public function getRevenueByChannel(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->select('channel_name')
            ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(base_grand_total), 0) as revenue')
            ->groupBy('channel_name');

        if ($this->locationId) {
            $query->where('location_id', $this->locationId);
        }

        return $query->get()->keyBy('channel_name')->toArray();
    }

    public function getDiscountedOrdersShare(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud']);

        $this->applyDimensionFilters($query, 'channel_name');

        $total = (clone $query)->count();
        $discounted = (clone $query)->where('base_discount_amount', '>', 0)->count();
        $discountTotal = (clone $query)->sum('base_discount_amount');

        return [
            'discounted_orders' => $discounted,
            'total_orders'      => $total,
            'share'             => $this->safeDiv($discounted, $total, 4) * 100,
            'discount_total'    => round(abs($discountTotal), 2),
        ];
    }
}
