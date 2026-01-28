<?php

namespace App\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class BonusHistoryDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('bonus_history')
            ->select(
                'bonus_history.id',
                'bonus_history.customer_id',
                'bonus_history.order_id',
                'bonus_history.type',
                'bonus_history.amount',
                'bonus_history.base_amount',
                'bonus_history.balance_after',
                'bonus_history.expires_at',
                'bonus_history.description',
                'bonus_history.created_at',
                'customers.first_name',
                'customers.last_name',
                'customers.email',
                'orders.increment_id'
            )
            ->leftJoin('customers', 'bonus_history.customer_id', '=', 'customers.id')
            ->leftJoin('orders', 'bonus_history.order_id', '=', 'orders.id');

        $this->addFilter('id', 'bonus_history.id');
        $this->addFilter('customer_id', 'bonus_history.customer_id');
        $this->addFilter('type', 'bonus_history.type');
        $this->addFilter('created_at', 'bonus_history.created_at');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.datagrid.id'),
            'type'       => 'integer',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'customer_name',
            'label'      => 'Клиент',
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => false,
            'closure'    => function ($row) {
                return $row->first_name . ' ' . $row->last_name . ' (' . $row->email . ')';
            },
        ]);

        $this->addColumn([
            'index'      => 'order_id',
            'label'      => 'Заказ',
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->increment_id ? '#' . $row->increment_id : '-';
            },
        ]);

        $this->addColumn([
            'index'      => 'type',
            'label'      => 'Тип операции',
            'type'       => 'string',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
            'closure'    => function ($row) {
                $types = [
                    'accrual' => 'Начисление',
                    'deduction' => 'Списание',
                    'refund' => 'Возврат',
                    'expiration' => 'Истечение',
                ];

                return $types[$row->type] ?? $row->type;
            },
        ]);

        $this->addColumn([
            'index'      => 'amount',
            'label'      => 'Сумма',
            'type'       => 'decimal',
            'searchable' => false,
            'sortable'   => true,
            'closure'    => function ($row) {
                return core()->formatPrice($row->amount);
            },
        ]);

        $this->addColumn([
            'index'      => 'balance_after',
            'label'      => 'Баланс после',
            'type'       => 'decimal',
            'searchable' => false,
            'sortable'   => true,
            'closure'    => function ($row) {
                return core()->formatPrice($row->balance_after);
            },
        ]);

        $this->addColumn([
            'index'      => 'expires_at',
            'label'      => 'Срок действия',
            'type'       => 'datetime',
            'searchable' => false,
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->expires_at ? \Carbon\Carbon::parse($row->expires_at)->format('d.m.Y H:i') : '-';
            },
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => 'Дата',
            'type'       => 'datetime',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
            'closure'    => function ($row) {
                return \Carbon\Carbon::parse($row->created_at)->format('d.m.Y H:i');
            },
        ]);
    }
}
