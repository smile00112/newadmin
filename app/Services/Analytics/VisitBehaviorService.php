<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\DB;

/**
 * D. First / Second Visit Behavior
 *
 * All metrics derived from orders / order_items tables.
 * Visit number determined by order sequence per customer.
 */
class VisitBehaviorService extends BaseAnalyticsService
{
    /**
     * Order mix for a given visit number (1st order, 2nd order, etc.)
     * Determined by ordering customer's orders chronologically.
     */
    public function getOrderMix(int $visitNumber, int $limit = 10): array
    {
        // Get orders for the given visit number using a ranked subquery
        $ranked = DB::table('orders')
            ->select('id', 'customer_id', 'created_at')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY customer_id ORDER BY created_at) as visit_num')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['cancelled', 'failed', 'fraud'])
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

    /**
     * Category transition between first and second orders.
     */
    public function getCategoryTransitionMap(int $limit = 15): array
    {
        $ranked = DB::table('orders')
            ->select('id', 'customer_id')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY customer_id ORDER BY created_at) as visit_num')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['cancelled', 'failed', 'fraud'])
            ->whereNotNull('customer_id');

        return DB::query()
            ->fromSub($ranked, 'r1')
            ->join(DB::raw("({$ranked->toSql()}) as r2"), function ($join) {
                $join->on('r1.customer_id', '=', 'r2.customer_id')
                    ->where('r1.visit_num', '=', 1)
                    ->where('r2.visit_num', '=', 2);
            })
            ->mergeBindings($ranked)
            ->join('order_items as oi1', 'oi1.order_id', '=', 'r1.id')
            ->join('order_items as oi2', 'oi2.order_id', '=', 'r2.id')
            ->select('oi1.name as first_item', 'oi2.name as second_item')
            ->selectRaw('COUNT(*) as transitions')
            ->groupBy('oi1.name', 'oi2.name')
            ->orderByDesc('transitions')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Repeat dish rate: items ordered in both 1st and 2nd orders.
     */
    public function getRepeatDishRate(): array
    {
        // Find customers with at least 2 orders
        $customers = DB::table('orders')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['cancelled', 'failed', 'fraud'])
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
                ->whereNotIn('status', ['cancelled', 'failed', 'fraud'])
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

    /**
     * AOV by visit number (1st vs 2nd order).
     */
    public function getAovByVisit(): array
    {
        $ranked = DB::table('orders')
            ->select('id', 'customer_id', 'base_grand_total', 'created_at')
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY customer_id ORDER BY created_at) as visit_num')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('status', ['cancelled', 'failed', 'fraud'])
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
