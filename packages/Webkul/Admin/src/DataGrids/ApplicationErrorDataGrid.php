<?php

namespace Webkul\Admin\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ApplicationErrorDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'error_id';

    /**
     * Default sort column of datagrid.
     *
     * @var ?string
     */
    protected $sortColumn = 'created_at';

    /**
     * Default sort order of datagrid.
     *
     * @var string
     */
    protected $sortOrder = 'desc';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('application_errors')
            ->select(
                'id as error_id',
                'message',
                'code',
                'source',
                'file',
                'line',
                'created_at'
            );

        $this->addFilter('error_id', 'id');
        $this->addFilter('message', 'message');
        $this->addFilter('code', 'code');
        $this->addFilter('source', 'source');
        $this->addFilter('created_at', 'created_at');

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
            'index'      => 'error_id',
            'label'      => trans('admin::app.application_errors.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'message',
            'label'      => trans('admin::app.application_errors.index.datagrid.message'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return \Illuminate\Support\Str::limit($row->message, 80);
            },
        ]);

        $this->addColumn([
            'index'      => 'code',
            'label'      => trans('admin::app.application_errors.index.datagrid.code'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'source',
            'label'      => trans('admin::app.application_errors.index.datagrid.source'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('admin::app.application_errors.index.datagrid.created_at'),
            'type'       => 'datetime',
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('application_errors.view')) {
            $this->addAction([
                'index'  => 'view',
                'icon'   => 'icon-view',
                'title'  => trans('admin::app.application_errors.index.datagrid.view'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.application_errors.show', $row->error_id);
                },
            ]);
        }
    }
}
