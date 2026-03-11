<?php

namespace Webkul\Reporting\Services;

use Illuminate\Support\Facades\DB;

class VisitBehaviorService extends BaseAnalyticsService
{
    public function getOrderMix(int $visitNumber, int $limit = 10): array
    {
        $ranked = DB::table('orders')
            ->select('id', 'customer_id', 'created_at')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY customer_id ORDER BY created_at) as visit_num')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('customer_id');

        return DB::table('order_items as oi')
            ->joinSub($ranked, 'ranked', fn ($join) => $join->on('oi.order_id', '=', 'ranked.id'))
            ->where('ranked.visit_num', $visitNumber)
            ->select('oi.name', 'oi.sku')
            ->selectRaw('SUM(oi.qty_ordered) as quantity, SUM(oi.base_total) as revenue')
            ->groupBy('oi.name', 'oi.sku')
            ->orderByDesc('quantity')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getCategoryTransitionMap(int $limit = 15): array
    {
        $customers = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('customer_id')
            ->select('customer_id')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) >= 2')
            ->pluck('customer_id');

        if ($customers->isEmpty()) {
            return [];
        }

        $transitions = [];

        foreach ($customers as $customerId) {
            $orders = DB::table('orders')
                ->where('customer_id', $customerId)
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
                ->orderBy('created_at')
                ->limit(2)
                ->pluck('id');

            if ($orders->count() < 2) continue;

            $firstItems = DB::table('order_items')->where('order_id', $orders[0])->pluck('name')->toArray();
            $secondItems = DB::table('order_items')->where('order_id', $orders[1])->pluck('name')->toArray();

            foreach ($firstItems as $fi) {
                foreach ($secondItems as $si) {
                    $key = $fi . '→' . $si;
                    $transitions[$key] = ($transitions[$key] ?? 0) + 1;
                }
            }
        }

        arsort($transitions);

        return array_slice(
            array_map(fn ($key, $count) => [
                'first_item'  => explode('→', $key)[0],
                'second_item' => explode('→', $key)[1],
                'transitions'  => $count,
            ], array_keys($transitions), $transitions),
            0, $limit
        );
    }

    public function getRepeatDishRate(): array
    {
        $customers = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('customer_id')
            ->select('customer_id')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) >= 2')
            ->pluck('customer_id');

        if ($customers->isEmpty()) {
            return ['rate' => 0, 'repeated' => 0, 'total_items' => 0, 'sample_size' => 0];
        }

        $repeatCount = 0;
        $totalItems = 0;

        foreach ($customers as $customerId) {
            $orders = DB::table('orders')
                ->where('customer_id', $customerId)
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
                ->orderBy('created_at')
                ->limit(2)
                ->pluck('id');

            if ($orders->count() < 2) {
                continue;
            }

            $firstSkus = DB::table('order_items')
                ->where('order_id', $orders[0])
                ->pluck('sku')
                ->toArray();

            $secondItems = DB::table('order_items')
                ->where('order_id', $orders[1])
                ->pluck('sku');

            $totalItems += $secondItems->count();
            $repeatCount += $secondItems->filter(fn ($sku) => in_array($sku, $firstSkus))->count();
        }

        return [
            'rate'        => $this->safeDiv($repeatCount, $totalItems, 4) * 100,
            'repeated'    => $repeatCount,
            'total_items' => $totalItems,
            'sample_size' => $customers->count(),
        ];
    }

    public function getAovByVisit(): array
    {
        $ranked = DB::table('orders')
            ->select('id', 'customer_id', 'base_grand_total', 'created_at')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY customer_id ORDER BY created_at) as visit_num')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('customer_id');

        $result = DB::query()->fromSub($ranked, 'ranked')
            ->whereIn('visit_num', [1, 2])
            ->select('visit_num')
            ->selectRaw('AVG(base_grand_total) as aov, COUNT(*) as orders')
            ->groupBy('visit_num')
            ->get()
            ->keyBy('visit_num');

        return [
            'first_visit'  => [
                'aov'    => round($result->get(1)?->aov ?? 0, 2),
                'orders' => (int) ($result->get(1)?->orders ?? 0),
            ],
            'second_visit' => [
                'aov'    => round($result->get(2)?->aov ?? 0, 2),
                'orders' => (int) ($result->get(2)?->orders ?? 0),
            ],
        ];
    }
}
