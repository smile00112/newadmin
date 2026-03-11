<?php

namespace Webkul\Reporting\Services;

use Illuminate\Support\Facades\DB;

class FunnelRetentionService extends BaseAnalyticsService
{
    public function getActiveUsers(): array
    {
        $base = DB::table('orders')
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('customer_id');

        $this->applyDimensionFilters($base, 'channel_name');

        $dau = (clone $base)->whereDate('created_at', now()->toDateString())->distinct('customer_id')->count('customer_id');
        $wau = (clone $base)->where('created_at', '>=', now()->subDays(7))->distinct('customer_id')->count('customer_id');
        $mau = (clone $base)->where('created_at', '>=', now()->subDays(30))->distinct('customer_id')->count('customer_id');

        return compact('dau', 'wau', 'mau');
    }

    public function getNewUsers(): array
    {
        $daily = DB::table('customers')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $weekly = DB::table('customers')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return [
            'daily'  => $daily,
            'weekly' => $weekly,
        ];
    }

    public function getSessionToOrderConversion(): array
    {
        $totalCustomers = DB::table('customers')
            ->where('created_at', '<=', $this->endDate)
            ->count();

        $withOrder = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count('customer_id');

        $overall = $this->safeDiv($withOrder, $totalCustomers, 4) * 100;

        $byChannel = DB::table('orders')
            ->join('order_payment', 'order_payment.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('orders.status', ['canceled', 'failed', 'fraud'])
            ->select('order_payment.method_title as channel')
            ->selectRaw('COUNT(DISTINCT orders.customer_id) as customers, COUNT(*) as orders')
            ->groupBy('order_payment.method_title')
            ->get()
            ->map(fn ($r) => [
                'channel'    => $r->channel,
                'customers'  => (int) $r->customers,
                'orders'     => (int) $r->orders,
                'avg_orders' => $this->safeDiv($r->orders, $r->customers, 2),
            ]);

        return [
            'overall'    => round($overall, 2),
            'total'      => $totalCustomers,
            'with_order' => $withOrder,
            'by_channel' => $byChannel,
        ];
    }

    public function getFunnelDropoff(): array
    {
        $registered = DB::table('customers')
            ->where('created_at', '<=', $this->endDate)
            ->count();

        $createdCart = DB::table('cart')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count('customer_id');

        $ordersQuery = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        $this->applyDimensionFilters($ordersQuery, 'channel_name');

        $placedOrder = (clone $ordersQuery)->distinct('customer_id')->count('customer_id');

        $paid = (clone $ordersQuery)
            ->whereNotIn('status', ['canceled', 'canceled', 'failed', 'fraud', 'pending'])
            ->distinct('customer_id')
            ->count('customer_id');

        $completed = (clone $ordersQuery)
            ->where('status', 'completed')
            ->distinct('customer_id')
            ->count('customer_id');

        $steps = [
            ['step' => 'registered', 'count' => $registered],
            ['step' => 'created_cart', 'count' => $createdCart],
            ['step' => 'placed_order', 'count' => $placedOrder],
            ['step' => 'paid', 'count' => $paid],
            ['step' => 'completed', 'count' => $completed],
        ];

        $funnel = [];
        $prev = null;

        foreach ($steps as $s) {
            $funnel[] = [
                'step'    => $s['step'],
                'count'   => $s['count'],
                'dropoff' => $prev !== null ? round((1 - $this->safeDiv($s['count'], $prev)) * 100, 2) : 0,
            ];
            $prev = $s['count'];
        }

        return $funnel;
    }

    public function getTimeToPayment(): array
    {
        $query = DB::table('orders')
            ->join('order_payment', 'order_payment.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('orders.status', ['canceled', 'failed', 'fraud']);

        $this->applyDimensionFilters($query, 'orders.channel_name');

        $stats = $query->selectRaw("
            AVG(TIMESTAMPDIFF(SECOND, orders.created_at, order_payment.created_at)) as avg_seconds,
            COUNT(*) as total,
            SUM(CASE WHEN TIMESTAMPDIFF(SECOND, orders.created_at, order_payment.created_at) < 60 THEN 1 ELSE 0 END) as under_60
        ")->first();

        $avg = max(0, (int) ($stats->avg_seconds ?? 0));

        return [
            'avg_seconds'     => $avg,
            'under_60s_count' => (int) ($stats->under_60 ?? 0),
            'total'           => (int) ($stats->total ?? 0),
            'under_60s_share' => $this->safeDiv($stats->under_60 ?? 0, $stats->total ?? 0, 4) * 100,
        ];
    }

    public function getCohortRetention(): array
    {
        $firstOrders = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('customer_id')
            ->select('customer_id')
            ->selectRaw('MIN(DATE(created_at)) as cohort_date')
            ->groupBy('customer_id')
            ->get();

        $cohortSize = $firstOrders->count();

        if ($cohortSize === 0) {
            return ['cohort_size' => 0, 'd1' => 0, 'd7' => 0, 'd30' => 0];
        }

        $retention = [];

        foreach ([1, 7, 30] as $day) {
            $returned = 0;

            foreach ($firstOrders as $user) {
                $targetDate = \Carbon\Carbon::parse($user->cohort_date)->addDays($day)->toDateString();

                $hasOrder = DB::table('orders')
                    ->where('customer_id', $user->customer_id)
                    ->whereDate('created_at', $targetDate)
                    ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
                    ->exists();

                if ($hasOrder) $returned++;
            }

            $retention["d{$day}"] = round($this->safeDiv($returned, $cohortSize, 4) * 100, 2);
        }

        return array_merge(['cohort_size' => $cohortSize], $retention);
    }

    public function getOrdersPerUser(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('customer_id');

        $this->applyDimensionFilters($query, 'channel_name');

        $stats = $query->selectRaw('COUNT(*) as total_orders, COUNT(DISTINCT customer_id) as unique_customers')
            ->first();

        return [
            'orders_per_user'    => $this->safeDiv($stats->total_orders, $stats->unique_customers, 2),
            'total_orders'       => (int) $stats->total_orders,
            'unique_customers'   => (int) $stats->unique_customers,
        ];
    }

    public function getMedianTimeBetweenOrders(): array
    {
        $orders = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('customer_id')
            ->select('customer_id', 'created_at')
            ->orderBy('customer_id')
            ->orderBy('created_at')
            ->get();

        $gaps = [];
        $prev = [];

        foreach ($orders as $order) {
            if (isset($prev[$order->customer_id])) {
                $gaps[] = strtotime($order->created_at) - strtotime($prev[$order->customer_id]);
            }
            $prev[$order->customer_id] = $order->created_at;
        }

        if (empty($gaps)) {
            return ['median_hours' => 0, 'median_days' => 0, 'sample_size' => 0];
        }

        sort($gaps);
        $mid = (int) floor(count($gaps) / 2);
        $median = count($gaps) % 2 === 0
            ? ($gaps[$mid - 1] + $gaps[$mid]) / 2
            : $gaps[$mid];

        return [
            'median_hours' => round($median / 3600, 1),
            'median_days'  => round($median / 86400, 1),
            'sample_size'  => count($gaps),
        ];
    }

    public function getRevenuePerUser(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud']);

        $gmv = (float) (clone $query)->sum('base_grand_total');

        $totalCustomers = DB::table('customers')
            ->where('created_at', '<=', $this->endDate)
            ->count();

        $payingUsers = (clone $query)
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count('customer_id');

        return [
            'arpu' => $this->safeDiv($gmv, max($totalCustomers, 1), 2),
            'rppu' => $this->safeDiv($gmv, max($payingUsers, 1), 2),
            'gmv'  => round($gmv, 2),
            'total_customers' => $totalCustomers,
            'paying_users' => $payingUsers,
        ];
    }
}
