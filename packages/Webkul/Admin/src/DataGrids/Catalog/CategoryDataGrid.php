<?php

namespace Webkul\Admin\DataGrids\Catalog;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CategoryDataGrid extends DataGrid
{
    /**
     * Index.
     *
     * @var string
     */
    protected $primaryColumn = 'category_id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('categories')
            ->select(
                'categories.id as category_id',
                'category_translations.name',
                'categories.position',
                'categories.status',
                'category_translations.locale',
            )
            ->addSelect(DB::raw("(SELECT COUNT(*) FROM {$tablePrefix}product_categories WHERE category_id = {$tablePrefix}categories.id) as products_count"))
            ->leftJoin('category_translations', function ($join) {
                $join->on('categories.id', '=', 'category_translations.category_id')
                    ->where('category_translations.locale', '=', app()->getLocale());
            })
            ->where('category_translations.locale', app()->getLocale())
            ->groupBy('categories.id');

        $this->addFilter('category_id', 'categories.id');

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
            'index'      => 'category_id',
            'label'      => trans('admin::app.catalog.categories.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.catalog.categories.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'position',
            'label'      => trans('admin::app.catalog.categories.index.datagrid.position'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('admin::app.catalog.categories.index.datagrid.status'),
            'type'               => 'boolean',
            'filterable'         => true,
            'filterable_options' => [
                [
                    'label' => trans('admin::app.catalog.categories.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('admin::app.catalog.categories.index.datagrid.inactive'),
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($value) {
                if ($value->status) {
                    return '<span class="badge badge-md badge-success">'.trans('admin::app.catalog.categories.index.datagrid.active').'</span>';
                }

                return '<span class="badge badge-md badge-danger">'.trans('admin::app.catalog.categories.index.datagrid.inactive').'</span>';
            },
        ]);

        $this->addColumn([
            'index'      => 'products_count',
            'label'      => trans('admin::app.catalog.categories.index.datagrid.no-of-products'),
            'type'       => 'integer',
            'sortable'   => true,
            'closure'    => function ($row) {
                $url = route('admin.catalog.products.index', ['category' => $row->category_id]);
                return '<a href="' . $url . '" class="inline-block min-w-[32px] rounded-md bg-violet-100 px-2 py-0.5 text-center text-sm font-semibold text-violet-700 transition-colors hover:bg-violet-200 dark:bg-violet-900/30 dark:text-violet-300 dark:hover:bg-violet-900/50">' . (int) $row->products_count . '</a>';
            },
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('catalog.categories.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('admin::app.catalog.categories.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.catalog.categories.edit', $row->category_id);
                },
            ]);
        }

        if (bouncer()->hasPermission('catalog.categories.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('admin::app.catalog.categories.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.catalog.categories.delete', $row->category_id);
                },
            ]);
        }

        if (bouncer()->hasPermission('catalog.categories.delete')) {
            $this->addMassAction([
                'title'  => trans('admin::app.catalog.categories.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.catalog.categories.mass_delete'),
            ]);
        }

        if (bouncer()->hasPermission('catalog.categories.edit')) {
            $this->addMassAction([
                'title'   => trans('admin::app.catalog.categories.index.datagrid.update-status'),
                'method'  => 'POST',
                'url'     => route('admin.catalog.categories.mass_update'),
                'options' => [
                    [
                        'label' => trans('admin::app.catalog.categories.index.datagrid.active'),
                        'value' => 1,
                    ], [
                        'label' => trans('admin::app.catalog.categories.index.datagrid.inactive'),
                        'value' => 0,
                    ],
                ],
            ]);
        }
    }
}
