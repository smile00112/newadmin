<?php

namespace Webkul\Reporting\Services;

use Illuminate\Support\Facades\DB;

class MenuAnalyticsService extends BaseAnalyticsService
{
    public function getTopDishesByRevenue(int $limit = 20): array
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereBetween('o.created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('o.status', ['canceled', 'failed', 'fraud'])
            ->where(function ($q) {
                $this->applyDimensionFilters($q, 'o.channel_name', 'o.location_id');
            })
            ->select('oi.name', 'oi.sku', 'oi.product_id')
            ->selectRaw('SUM(oi.base_total) as revenue, SUM(oi.qty_ordered) as quantity')
            ->groupBy('oi.name', 'oi.sku', 'oi.product_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getTopDishesByQuantity(int $limit = 20): array
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereBetween('o.created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('o.status', ['canceled', 'failed', 'fraud'])
            ->where(function ($q) {
                $this->applyDimensionFilters($q, 'o.channel_name', 'o.location_id');
            })
            ->select('oi.name', 'oi.sku', 'oi.product_id')
            ->selectRaw('SUM(oi.qty_ordered) as quantity, SUM(oi.base_total) as revenue')
            ->groupBy('oi.name', 'oi.sku', 'oi.product_id')
            ->orderByDesc('quantity')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getDrinkDessertAttachRate(array $categoryKeywords = ['Напитки']): array
    {
        $query = DB::table('category_translations')->where('locale', 'ru');
        $query->where(function ($q) use ($categoryKeywords) {
            foreach ($categoryKeywords as $kw) {
                $q->orWhere('name', 'LIKE', "%{$kw}%");
            }
        });
        $categoryIds = $query->pluck('category_id');

        if ($categoryIds->isEmpty()) {
            return ['rate' => 0, 'with_attach' => 0, 'total' => 0];
        }

        $base = DB::table('orders as o')
            ->whereBetween('o.created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('o.status', ['canceled', 'failed', 'fraud']);

        $this->applyDimensionFilters($base, 'o.channel_name');

        $total = (clone $base)->count();

        $withAttach = (clone $base)
            ->whereExists(function ($q) use ($categoryIds) {
                $q->select(DB::raw(1))
                    ->from('order_items as oi')
                    ->join('product_categories as pc', 'pc.product_id', '=', 'oi.product_id')
                    ->whereColumn('oi.order_id', 'o.id')
                    ->whereIn('pc.category_id', $categoryIds);
            })
            ->count();

        return [
            'rate'        => $this->safeDiv($withAttach, $total, 4) * 100,
            'with_attach' => $withAttach,
            'total'       => $total,
        ];
    }

    public function getCustomizationRate(): array
    {
        $base = DB::table('orders as o')
            ->whereBetween('o.created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('o.status', ['canceled', 'failed', 'fraud']);

        $this->applyDimensionFilters($base, 'o.channel_name');

        $total = (clone $base)->count();

        $customized = (clone $base)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('order_items as oi')
                    ->whereColumn('oi.order_id', 'o.id')
                    ->groupBy('oi.order_id')
                    ->havingRaw('COUNT(DISTINCT oi.product_id) > 1');
            })
            ->count();

        return [
            'rate'       => $this->safeDiv($customized, $total, 4) * 100,
            'customized' => $customized,
            'total'      => $total,
        ];
    }

    public function getTopIngredients(string $type = 'added', int $limit = 10): array
    {
        $key = $type === 'added' ? 'added_ingredients' : 'removed_ingredients';

        $items = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereBetween('o.created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('o.status', ['canceled', 'failed', 'fraud'])
            ->whereNotNull('oi.additional')
            ->select('oi.additional')
            ->get();

        $counts = [];

        foreach ($items as $item) {
            $additional = json_decode($item->additional, true);

            if (! is_array($additional) || ! isset($additional[$key])) {
                continue;
            }

            foreach ((array) $additional[$key] as $ingredient) {
                $name = is_array($ingredient) ? ($ingredient['name'] ?? 'unknown') : (string) $ingredient;
                $counts[$name] = ($counts[$name] ?? 0) + 1;
            }
        }

        arsort($counts);

        return array_slice(
            array_map(fn ($name, $count) => ['name' => $name, 'count' => $count], array_keys($counts), $counts),
            0,
            $limit
        );
    }

    public function getDeadItems(int $dayThreshold = 14, int $limit = 30): array
    {
        $cutoff = now()->subDays($dayThreshold);

        return DB::table('products as p')
            ->join('product_flat as pf', function ($join) {
                $join->on('pf.product_id', '=', 'p.id')
                    ->where('pf.status', 1);
            })
            ->leftJoin('order_items as oi', function ($join) use ($cutoff) {
                $join->on('oi.product_id', '=', 'p.id')
                    ->where('oi.created_at', '>=', $cutoff);
            })
            ->whereNull('oi.id')
            ->select('p.id', 'p.sku', 'pf.name')
            ->groupBy('p.id', 'p.sku', 'pf.name')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getAovUplift(int $limit = 10): array
    {
        $base = DB::table('orders as o')
            ->whereBetween('o.created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('o.status', ['canceled', 'failed', 'fraud']);

        $this->applyDimensionFilters($base, 'o.channel_name');

        $overallAov = (float) (clone $base)->avg('o.base_grand_total');

        $topProducts = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereBetween('o.created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('o.status', ['canceled', 'failed', 'fraud'])
            ->select('oi.product_id', 'oi.name', 'oi.sku')
            ->selectRaw('AVG(o.base_grand_total) as aov_with_item, COUNT(DISTINCT o.id) as order_count')
            ->groupBy('oi.product_id', 'oi.name', 'oi.sku')
            ->having('order_count', '>=', 5)
            ->orderByDesc(DB::raw('AVG(o.base_grand_total)'))
            ->limit($limit)
            ->get();

        return $topProducts->map(fn ($p) => [
            'name'         => $p->name,
            'sku'          => $p->sku,
            'aov_with'     => round($p->aov_with_item, 2),
            'aov_overall'  => round($overallAov, 2),
            'uplift'       => round($p->aov_with_item - $overallAov, 2),
            'order_count'  => $p->order_count,
        ])->toArray();
    }

    public function getNewDishMetrics(int $newDishDays = 30): array
    {
        $cutoff = now()->subDays($newDishDays);

        $newProductIds = DB::table('products')
            ->where('created_at', '>=', $cutoff)
            ->pluck('id');

        if ($newProductIds->isEmpty()) {
            return ['trial_rate' => 0, 'repeat_rate' => 0, 'new_products' => 0];
        }

        $base = DB::table('orders as o')
            ->whereBetween('o.created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('o.status', ['canceled', 'failed', 'fraud']);

        $this->applyDimensionFilters($base, 'o.channel_name');

        $totalOrders = (clone $base)->count();

        $ordersWithNew = (clone $base)
            ->whereExists(function ($q) use ($newProductIds) {
                $q->select(DB::raw(1))
                    ->from('order_items as oi')
                    ->whereColumn('oi.order_id', 'o.id')
                    ->whereIn('oi.product_id', $newProductIds);
            })
            ->count();

        $customerTrials = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereBetween('o.created_at', [$this->startDate, $this->endDate])
            ->whereNotIn('o.status', ['canceled', 'failed', 'fraud'])
            ->whereIn('oi.product_id', $newProductIds)
            ->whereNotNull('o.customer_id')
            ->select('o.customer_id', 'oi.product_id')
            ->selectRaw('COUNT(DISTINCT o.id) as order_count')
            ->groupBy('o.customer_id', 'oi.product_id');

        $trialStats = DB::query()->fromSub($customerTrials, 'ct')
            ->selectRaw('COUNT(*) as total_trials, SUM(CASE WHEN order_count >= 2 THEN 1 ELSE 0 END) as repeats')
            ->first();

        return [
            'trial_rate'    => $this->safeDiv($ordersWithNew, $totalOrders, 4) * 100,
            'repeat_rate'   => $this->safeDiv($trialStats->repeats ?? 0, $trialStats->total_trials ?? 0, 4) * 100,
            'new_products'  => $newProductIds->count(),
            'orders_with_new' => $ordersWithNew,
        ];
    }
}
