<?php

namespace Webkul\Reporting\Services;

use Illuminate\Support\Facades\DB;

class PaymentsChannelsService extends BaseAnalyticsService
{
    public function getPaymentSuccessRate(): array
    {
        $query = DB::table('order_payment')
            ->join('orders', 'orders.id', '=', 'order_payment.order_id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate]);

        $this->applyDimensionFilters($query, 'orders.channel_name');

        $byMethod = $query
            ->select('order_payment.method_title as payment_method')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN orders.status NOT IN ('canceled','failed','fraud') THEN 1 ELSE 0 END) as successes,
                SUM(CASE WHEN orders.status IN ('canceled','failed','fraud') THEN 1 ELSE 0 END) as failures
            ")
            ->groupBy('order_payment.method_title')
            ->get()
            ->map(fn ($r) => [
                'method'       => $r->payment_method,
                'total'        => $r->total,
                'successes'    => (int) $r->successes,
                'failures'     => (int) $r->failures,
                'success_rate' => $this->safeDiv($r->successes, $r->total, 4) * 100,
            ]);

        $totals = $byMethod->reduce(fn ($carry, $item) => [
            'total'     => ($carry['total'] ?? 0) + $item['total'],
            'successes' => ($carry['successes'] ?? 0) + $item['successes'],
        ], []);

        return [
            'overall_rate' => $this->safeDiv($totals['successes'] ?? 0, $totals['total'] ?? 0, 4) * 100,
            'by_method'    => $byMethod->toArray(),
        ];
    }

    public function getPaymentFailReasons(int $limit = 10): array
    {
        return DB::table('order_payment')
            ->join('orders', 'orders.id', '=', 'order_payment.order_id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->whereIn('orders.status', ['canceled', 'canceled', 'failed', 'fraud'])
            ->select('order_payment.method_title as fail_reason')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('order_payment.method_title')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getCrashFreeSessions(): array
    {
        $total = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->count();

        $failed = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereIn('status', ['failed', 'fraud'])
            ->count();

        return [
            'crash_free_rate' => $this->safeDiv($total - $failed, max($total, 1), 4) * 100,
            'total_sessions'  => $total,
            'crashed_sessions' => $failed,
        ];
    }

    public function getAvgScreenLatency(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereRaw('updated_at > created_at');

        $avg = (float) $query->selectRaw('AVG(LEAST(TIMESTAMPDIFF(SECOND, created_at, updated_at), 7200)) as avg_sec')
            ->value('avg_sec');

        return [
            'avg_seconds' => round($avg, 0),
            'formatted'   => gmdate('i:s', (int) $avg),
            'sample_size' => (clone $query)->count(),
        ];
    }

    public function getChannelSplit(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud']);

        $this->applyDimensionFilters($query, 'channel_name');

        $results = $query
            ->select('channel_name')
            ->selectRaw('COUNT(*) as orders, SUM(base_grand_total) as revenue')
            ->groupBy('channel_name')
            ->get();

        $total = $results->sum('orders');

        return $results->map(fn ($r) => [
            'channel_name' => $r->channel_name,
            'orders' => $r->orders,
            'revenue' => $r->revenue,
            'pct' => round($this->safeDiv($r->orders, $total, 4) * 100, 1),
        ])->toArray();
    }

    public function getDineInVsTakeaway(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud']);

        $this->applyDimensionFilters($query, 'channel_name');

        $total = (clone $query)->count();

        $dineIn = (clone $query)->where('shipping_method', 'LIKE', '%dinein%')->count();
        $takeaway = $total - $dineIn;

        $results = [];
        if ($total > 0) {
            $results[] = [
                'type' => 'dine_in',
                'orders' => $dineIn,
                'pct' => round($this->safeDiv($dineIn, $total, 4) * 100, 1),
            ];
            $results[] = [
                'type' => 'takeaway',
                'orders' => $takeaway,
                'pct' => round($this->safeDiv($takeaway, $total, 4) * 100, 1),
            ];
        }

        return $results;
    }

    public function getOrdersByLocation(int $limit = 20): array
    {
        return DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->select('channel_name as location')
            ->selectRaw('COUNT(*) as orders, SUM(base_grand_total) as revenue')
            ->groupBy('channel_name')
            ->orderByDesc('orders')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getPostOrderRating(): array
    {
        $query = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('rating')
            ->where('rating', '>', 0);

        $this->applyDimensionFilters($query, 'channel_name');

        $avg = (float) (clone $query)->avg('rating');
        $total = (clone $query)->count();
        $ratedOrders = $total;

        return [
            'avg' => $total > 0 ? round($avg, 1) : null,
            'rated_orders' => $ratedOrders,
        ];
    }

    public function getNPS(): array
    {
        $ratings = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('rating')
            ->where('rating', '>', 0)
            ->select('rating')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('rating')
            ->get()
            ->keyBy('rating');

        $total = $ratings->sum('count');

        if ($total === 0) {
            return ['score' => null, 'nps' => 0, 'promoters' => 0, 'passives' => 0, 'detractors' => 0, 'total' => 0];
        }

        $promoters = $ratings->filter(fn ($r, $k) => $k >= 5)->sum('count');
        $detractors = $ratings->filter(fn ($r, $k) => $k <= 3)->sum('count');
        $passives = $total - $promoters - $detractors;

        $score = round((($promoters - $detractors) / $total) * 100, 1);

        return [
            'score'      => $score,
            'nps'        => $score,
            'promoters'  => $promoters,
            'passives'   => $passives,
            'detractors' => $detractors,
            'total'      => $total,
        ];
    }

    public function getComplaintsStats(): array
    {
        $total = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'canceled')
            ->count();

        $resolved = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereIn('status', ['canceled'])
            ->where('updated_at', '>', DB::raw('created_at'))
            ->count();

        $avgResolution = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'canceled')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes')
            ->value('avg_minutes');

        $themes = DB::table('orders')
            ->join('order_payment', 'order_payment.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->where('orders.status', 'canceled')
            ->select('order_payment.method_title as feedback_theme')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('order_payment.method_title')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'total'              => $total,
            'resolved'           => $resolved,
            'open'               => max(0, $total - $resolved),
            'avg_resolution_min' => (int) ($avgResolution ?? 0),
            'top_themes'         => $themes,
        ];
    }

    public function getKioskUptime(): array
    {
        return DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->select('channel_name as kiosk')
            ->selectRaw("
                channel_name as location,
                COUNT(*) as total_orders,
                ROUND(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as uptime_pct,
                CASE
                    WHEN MAX(created_at) >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 'online'
                    WHEN MAX(created_at) >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 'degraded'
                    ELSE 'offline'
                END as status
            ")
            ->groupBy('channel_name')
            ->get()
            ->toArray();
    }
}
