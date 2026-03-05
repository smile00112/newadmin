<?php

namespace Webkul\Admin\DataGrids\Sales;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderAddress;
use Webkul\Sales\Models\OrderStatus;
use Webkul\Sales\Repositories\OrderRepository;

class OrderDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('orders')
            ->leftJoin('addresses as order_address_shipping', function ($leftJoin) {
                $leftJoin->on('order_address_shipping.order_id', '=', 'orders.id')
                    ->where('order_address_shipping.address_type', OrderAddress::ADDRESS_TYPE_SHIPPING);
            })
            ->leftJoin('addresses as order_address_billing', function ($leftJoin) {
                $leftJoin->on('order_address_billing.order_id', '=', 'orders.id')
                    ->where('order_address_billing.address_type', OrderAddress::ADDRESS_TYPE_BILLING);
            })
            ->leftJoin('order_payment', 'orders.id', '=', 'order_payment.order_id')
            ->select(
                'orders.id',
                DB::raw('GROUP_CONCAT('.DB::getTablePrefix().'order_payment.method SEPARATOR "|") as method'),
                'orders.increment_id',
                'orders.base_grand_total',
                'orders.created_at',
                'channel_name',
                'channel_id',
                'status',
                'customer_email',
                'orders.cart_id as items',
                DB::raw('CONCAT('.DB::getTablePrefix().'orders.customer_first_name, " ", '.DB::getTablePrefix().'orders.customer_last_name) as full_name'),
                DB::raw('CONCAT('.DB::getTablePrefix().'order_address_billing.city, ", ", '.DB::getTablePrefix().'order_address_billing.state,", ", '.DB::getTablePrefix().'order_address_billing.country) as location')
            )
            ->groupBy('orders.id');

        $this->addFilter('full_name', DB::raw('CONCAT('.DB::getTablePrefix().'orders.customer_first_name, " ", '.DB::getTablePrefix().'orders.customer_last_name)'));
        $this->addFilter('created_at', 'orders.created_at');

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
            'label'      => trans('admin::app.sales.orders.index.datagrid.order-id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('admin::app.sales.orders.index.datagrid.status'),
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

        $this->addColumn([
            'index'      => 'base_grand_total',
            'label'      => trans('admin::app.sales.orders.index.datagrid.grand-total'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'method',
            'label'      => trans('admin::app.sales.orders.index.datagrid.pay-via'),
            'type'       => 'string',
            'closure'    => function ($row) {
                return collect(explode('|', $row->method))
                    ->map(fn ($method) => core()->getConfigData('sales.payment_methods.'.$method.'.title'))
                    ->filter()
                    ->unique()
                    ->join(', ');
            },
        ]);

        $this->addColumn([
            'index'              => 'channel_id',
            'label'              => trans('admin::app.sales.orders.index.datagrid.channel-name'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => core()->getAllChannels()
                ->map(fn ($channel) => ['label' => $channel->name, 'value' => $channel->id])
                ->values()
                ->toArray(),
        ]);

        $this->addColumn([
            'index'      => 'full_name',
            'label'      => trans('admin::app.sales.orders.index.datagrid.customer'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        /**
         * Searchable dropdown sample. In testing phase.
         */
        $this->addColumn([
            'index'      => 'customer_email',
            'label'      => trans('admin::app.sales.orders.index.datagrid.email'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'location',
            'label'      => trans('admin::app.sales.orders.index.datagrid.location'),
            'type'       => 'string',
        ]);

        $this->addColumn([
            'index'      => 'items',
            'label'      => trans('admin::app.sales.orders.index.datagrid.items'),
            'type'       => 'string',
            'exportable' => false,
            'closure'    => function ($value) {
                $order = app(OrderRepository::class)->with('items')->find($value->id);

                return view('admin::sales.orders.items', compact('order'))->render();
            },
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('admin::app.sales.orders.index.datagrid.date'),
            'type'            => 'date',
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
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
            // Fallback if table doesn't exist
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
        if (bouncer()->hasPermission('sales.orders.view')) {
            $this->addAction([
                'icon'   => 'icon-view',
                'title'  => trans('admin::app.sales.orders.index.datagrid.view'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.sales.orders.view', $row->id);
                },
            ]);
        }
    }
}
