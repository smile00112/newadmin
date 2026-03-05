<?php

namespace Webkul\Shop\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderStatus;

class OrderDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return void
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('orders')
            ->addSelect(
                'orders.id',
                'orders.increment_id',
                'orders.status',
                'orders.created_at',
                'orders.grand_total',
                'orders.order_currency_code'
            )
            ->where('customer_id', auth()->guard('customer')->user()->id);

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'increment_id',
            'label'      => trans('shop::app.customers.account.orders.order-id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('shop::app.customers.account.orders.order-date'),
            'type'            => 'date',
            'searchable'      => true,
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
        ]);

        $this->addColumn([
            'index'      => 'grand_total',
            'label'      => trans('shop::app.customers.account.orders.total'),
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return core()->formatPrice($row->grand_total, $row->order_currency_code);
            },
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('shop::app.customers.account.orders.status.title'),
            'type'               => 'string',
            'searchable'         => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => $this->getDynamicStatusFilterOptions(),
            'sortable'   => true,
            'closure'    => function ($row) {
                return $this->renderStatusBadge($row->status);
            },
        ]);
    }

    /**
     * Get dynamic status filter options from order_statuses table.
     */
    protected function getDynamicStatusFilterOptions(): array
    {
        try {
            return OrderStatus::orderBy('sort_order')
                ->get()
                ->map(fn ($s) => ['label' => $s->name, 'value' => $s->code])
                ->toArray();
        } catch (\Exception $e) {
            return [
                ['label' => 'Новый', 'value' => 'pending'],
                ['label' => 'Обработка', 'value' => 'processing'],
                ['label' => 'Выполнен', 'value' => 'completed'],
                ['label' => 'Отменён', 'value' => 'canceled'],
            ];
        }
    }

    /**
     * Render a colored status badge using dynamic color from order_statuses table.
     */
    protected function renderStatusBadge(string $status): string
    {
        static $statusMap = null;
        if ($statusMap === null) {
            try {
                $statusMap = OrderStatus::orderBy('sort_order')
                    ->get()
                    ->keyBy('code')
                    ->toArray();
            } catch (\Exception $e) {
                $statusMap = [];
            }
        }

        $info = $statusMap[$status] ?? null;
        $name = $info['name'] ?? $status;
        $color = $info['color'] ?? '#6b7280';

        return '<span style="display:inline-block;padding:2px 10px;border-radius:9999px;font-size:12px;font-weight:600;background:'.$color.'1a;color:'.$color.';">'.$name.'</span>';
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'icon'   => 'icon-eye',
            'title'  => trans('shop::app.customers.account.orders.action-view'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('shop.customers.account.orders.view', $row->id);
            },
        ]);
    }
}
