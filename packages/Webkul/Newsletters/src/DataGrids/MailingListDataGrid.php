<?php

namespace Webkul\Newsletters\DataGrids;

use Webkul\Ui\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;

class MailingListDataGrid extends DataGrid
{
    /**
     * Set index columns, ex: id.
     *
     * @var int
     */
    protected $index = 'id';

    /**
     * Default sort order of datagrid.
     *
     * @var string
     */
    protected $sortOrder = 'desc';

    /**
     * Locale.
     *
     * @var string
     */
    protected $locale = 'all';

    /**
     * Channel.
     *
     * @var string
     */
    protected $channel = 'all';

    /**
     * Contains the keys for which extra filters to render.
     *
     * @var string[]
     */
    protected $extraFilters = [
        'channels',
        'locales',
    ];

    /**
     * Prepare query builder.
     *
     * @return void
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('newsletters_mailing_lists')
            ->leftJoin('newsletters_whatsapp_instances', 'newsletters_mailing_lists.whatsapp_instance_id', '=', 'newsletters_whatsapp_instances.id')
            ->leftJoin('admins', 'newsletters_mailing_lists.created_by', '=', 'admins.id')
            ->addSelect(
                'newsletters_mailing_lists.id',
                'newsletters_mailing_lists.name',
                'newsletters_mailing_lists.description',
                'newsletters_mailing_lists.status',
                'newsletters_mailing_lists.scheduled_at',
                'newsletters_mailing_lists.sent_at',
                'newsletters_mailing_lists.total_recipients',
                'newsletters_mailing_lists.sent_count',
                'newsletters_mailing_lists.failed_count',
                'newsletters_mailing_lists.created_at',
                'newsletters_whatsapp_instances.name as whatsapp_instance_name',
                'admins.name as created_by_name'
            );

        $this->addFilter('id', 'newsletters_mailing_lists.id');
        $this->addFilter('name', 'newsletters_mailing_lists.name');
        $this->addFilter('status', 'newsletters_mailing_lists.status');
        $this->addFilter('created_at', 'newsletters_mailing_lists.created_at');

        $this->setQueryBuilder($queryBuilder);
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function addColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.datagrid.id'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('newsletters::app.admin.mailing-lists.name'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'whatsapp_instance_name',
            'label'      => trans('newsletters::app.admin.mailing-lists.whatsapp-instance'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('newsletters::app.admin.mailing-lists.status'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
            'closure'    => function ($row) {
                $statusLabels = [
                    'draft' => 'Draft',
                    'scheduled' => 'Scheduled',
                    'sending' => 'Sending',
                    'sent' => 'Sent',
                    'failed' => 'Failed',
                ];

                return $statusLabels[$row->status] ?? $row->status;
            },
        ]);

        $this->addColumn([
            'index'      => 'total_recipients',
            'label'      => trans('newsletters::app.admin.mailing-lists.total-recipients'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index'      => 'sent_count',
            'label'      => trans('newsletters::app.admin.mailing-lists.sent-count'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('admin::app.datagrid.created'),
            'type'       => 'datetime',
            'sortable'   => true,
            'filterable' => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'title'  => trans('admin::app.datagrid.edit'),
            'method' => 'GET',
            'route'  => 'admin.newsletters.mailing-lists.edit',
            'icon'   => 'icon pencil-lg-icon',
        ]);

        $this->addAction([
            'title'        => trans('admin::app.datagrid.delete'),
            'method'       => 'DELETE',
            'route'        => 'admin.newsletters.mailing-lists.destroy',
            'confirm_text' => trans('ui::app.datagrid.massaction.delete-singular'),
            'icon'         => 'icon trash-icon',
        ]);

        $this->addAction([
            'title'  => trans('newsletters::app.admin.mailing-lists.send'),
            'method' => 'POST',
            'route'  => 'admin.newsletters.mailing-lists.send',
            'icon'   => 'icon send-icon',
        ]);
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        $this->addMassAction([
            'type'   => 'delete',
            'label'  => trans('admin::app.datagrid.delete'),
            'action' => route('admin.newsletters.mailing-lists.mass-delete'),
            'method' => 'POST',
        ]);
    }
}
