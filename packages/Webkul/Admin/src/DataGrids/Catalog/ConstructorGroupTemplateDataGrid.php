<?php

namespace Webkul\Admin\DataGrids\Catalog;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ConstructorGroupTemplateDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('product_constructor_group_templates')
            ->select(
                'id',
                'template_name',
                'name',
                'field_type',
                'checked_type',
                'required',
                'hidden',
                'sort',
                'created_at'
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
            'label'      => trans('admin::app.catalog.constructor-templates.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'template_name',
            'label'      => trans('admin::app.catalog.constructor-templates.index.datagrid.template-name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.catalog.constructor-templates.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

//        $this->addColumn([
//            'index'      => 'field_type',
//            'label'      => trans('admin::app.catalog.constructor-templates.index.datagrid.field-type'),
//            'type'       => 'string',
//            'filterable' => true,
//            'sortable'   => true,
//            'closure'    => function ($row) {
//                $types = [
//                    'checkbox' => trans('admin::app.catalog.constructor-templates.index.datagrid.type-checkbox'),
//                    'radio'    => trans('admin::app.catalog.constructor-templates.index.datagrid.type-radio'),
//                    'list'     => trans('admin::app.catalog.constructor-templates.index.datagrid.type-list'),
//                ];
//                return $types[$row->field_type] ?? $row->field_type;
//            },
//        ]);
//
//        $this->addColumn([
//            'index'      => 'required',
//            'label'      => trans('admin::app.catalog.constructor-templates.index.datagrid.required'),
//            'type'       => 'boolean',
//            'filterable' => true,
//            'sortable'   => true,
//            'closure'    => function ($row) {
//                return $row->required
//                    ? '<span class="badge badge-md badge-success">'.trans('admin::app.catalog.constructor-templates.index.datagrid.yes').'</span>'
//                    : '<span class="badge badge-md badge-secondary">'.trans('admin::app.catalog.constructor-templates.index.datagrid.no').'</span>';
//            },
//        ]);
//
//        $this->addColumn([
//            'index'      => 'sort',
//            'label'      => trans('admin::app.catalog.constructor-templates.index.datagrid.sort'),
//            'type'       => 'integer',
//            'filterable' => true,
//            'sortable'   => true,
//        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('admin::app.catalog.constructor-templates.index.datagrid.created-at'),
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
        if (bouncer()->hasPermission('catalog.constructor_templates.edit')) {
            $this->addAction([
                'index'  => 'edit',
                'icon'   => 'icon-edit',
                'title'  => trans('admin::app.catalog.constructor-templates.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.catalog.constructor_templates.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('catalog.constructor_templates.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => trans('admin::app.catalog.constructor-templates.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.catalog.constructor_templates.delete', $row->id);
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
        if (bouncer()->hasPermission('catalog.constructor_templates.delete')) {
            $this->addMassAction([
                'icon'   => 'icon-delete',
                'title'  => trans('admin::app.catalog.constructor-templates.index.datagrid.mass-delete'),
                'method' => 'POST',
                'url'    => route('admin.catalog.constructor_templates.mass_delete'),
            ]);
        }
    }
}

