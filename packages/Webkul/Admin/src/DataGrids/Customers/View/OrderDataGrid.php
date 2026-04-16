<?php

namespace Webkul\Admin\DataGrids\Customers\View;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderAddress;
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
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('orders')
            ->leftJoin('addresses as order_address_billing', function ($leftJoin) {
                $leftJoin->on('order_address_billing.order_id', '=', 'orders.id')
                    ->where('order_address_billing.address_type', OrderAddress::ADDRESS_TYPE_BILLING);
            })
            ->leftJoin('order_payment', 'orders.id', '=', 'order_payment.order_id')
            ->select(
                'orders.id',
                'orders.increment_id',
                'order_payment.method',
                'orders.base_grand_total',
                'orders.created_at',
                'channel_name',
                'status',
                'order_address_billing.email as customer_email',
                'orders.cart_id as image',
                DB::raw('CONCAT('.$tablePrefix.'order_address_billing.first_name, " ", '.$tablePrefix.'order_address_billing.last_name) as full_name'),
                DB::raw('CONCAT('.$tablePrefix.'order_address_billing.address, ", ", '.$tablePrefix.'order_address_billing.city,", ", '.$tablePrefix.'order_address_billing.state, ", ", '.$tablePrefix.'order_address_billing.country) as location')
            )
            ->where('orders.customer_id', request()->route('id'));

        $this->addFilter('full_name', DB::raw('CONCAT('.$tablePrefix.'orders.customer_first_name, " ", '.$tablePrefix.'orders.customer_last_name)'));
        $this->addFilter('created_at', 'orders.created_at');
        $this->addFilter('status', 'orders.status');

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
            'label'      => trans('admin::app.customers.customers.view.datagrid.orders.order-id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('admin::app.customers.customers.view.datagrid.orders.status'),
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
            'label'      => trans('admin::app.customers.customers.view.datagrid.orders.grand-total'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'method',
            'label'      => trans('admin::app.customers.customers.view.datagrid.orders.pay-via'),
            'type'       => 'string',
            'closure'    => function ($row) {
                return core()->getConfigData('sales.payment_methods.'.$row->method.'.title');
            },
        ]);

        $this->addColumn([
            'index'              => 'channel_name',
            'label'              => trans('admin::app.customers.customers.view.datagrid.orders.channel-name'),
            'type'               => 'string',
            'searchable'         => false,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => core()->getAllChannels()
                ->map(fn ($channel) => ['label' => $channel->name, 'value' => $channel->id])
                ->values()
                ->toArray(),
            'sortable'           => true,
        ]);

        $this->addColumn([
            'index'      => 'full_name',
            'label'      => trans('admin::app.customers.customers.view.datagrid.orders.customer-name'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
        ]);

        /**
         * Searchable dropdown sample. In testing phase.
         */
        $this->addColumn([
            'index'      => 'customer_email',
            'label'      => trans('admin::app.customers.customers.view.datagrid.orders.email'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'location',
            'label'      => trans('admin::app.customers.customers.view.datagrid.orders.location'),
            'type'       => 'string',
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('admin::app.customers.customers.view.datagrid.orders.date'),
            'type'            => 'date',
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
        ]);
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
                'title'  => trans('admin::app.customers.customers.view.datagrid.orders.view'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.sales.orders.view', $row->id);
                },
            ]);
        }
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
}
