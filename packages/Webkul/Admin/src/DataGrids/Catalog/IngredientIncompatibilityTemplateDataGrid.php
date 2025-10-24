<?php

namespace Webkul\Admin\DataGrids\Catalog;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class IngredientIncompatibilityTemplateDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('product_ingredients_incompatibilities_templates')
            ->select(
                'id',
                'name',
                'description',
                'active',
                'created_at',
                'updated_at'
            );
    }

    /**
     * Add Columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.catalog.ingredient-compatibility.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.catalog.ingredient-compatibility.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'description',
            'label'      => trans('admin::app.catalog.ingredient-compatibility.index.datagrid.description'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'active',
            'label'      => trans('admin::app.catalog.ingredient-compatibility.index.datagrid.active'),
            'type'       => 'boolean',
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->active) {
                    return '<span class="badge badge-md badge-success">'.trans('admin::app.catalog.ingredient-compatibility.index.datagrid.active-yes').'</span>';
                } else {
                    return '<span class="badge badge-md badge-danger">'.trans('admin::app.catalog.ingredient-compatibility.index.datagrid.active-no').'</span>';
                }
            },
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('admin::app.catalog.ingredient-compatibility.index.datagrid.created-at'),
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
        if (bouncer()->hasPermission('catalog.ingredient_compatibility.edit')) {
            $this->addAction([
                'index'  => 'edit',
                'icon'   => 'icon-edit',
                'title'  => trans('admin::app.catalog.ingredient-compatibility.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.catalog.ingredient_compatibility.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('catalog.ingredient_compatibility.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => trans('admin::app.catalog.ingredient-compatibility.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.catalog.ingredient_compatibility.delete', $row->id);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('catalog.ingredient_compatibility.delete')) {
            $this->addMassAction([
                'icon'   => 'icon-delete',
                'title'  => trans('admin::app.catalog.ingredient-compatibility.index.datagrid.mass-delete'),
                'method' => 'POST',
                'url'    => route('admin.catalog.ingredient_compatibility.mass_delete'),
            ]);
        }
    }
}

