<?php

namespace Webkul\Admin\DataGrids\Marketing\PushNotifications;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class PushCampaignDataGrid extends DataGrid
{
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('push_campaigns')
            ->select(
                'id',
                'name',
                'status',
                'total_recipients',
                'delivered_count',
                'opened_count',
                'created_at',
                DB::raw('CASE WHEN total_recipients > 0 THEN ROUND(opened_count * 100.0 / total_recipients, 1) ELSE 0 END as conversion_rate')
            );

        $this->addFilter('status', 'push_campaigns.status');

        return $queryBuilder;
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => '#',
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => 'Название',
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => 'Статус',
            'type'               => 'string',
            'searchable'         => false,
            'sortable'           => true,
            'filterable'         => true,
            'filterable_options' => [
                ['label' => 'Черновик',      'value' => 'draft'],
                ['label' => 'Запланирована', 'value' => 'scheduled'],
                ['label' => 'Отправляется',  'value' => 'sending'],
                ['label' => 'Отправлена',    'value' => 'sent'],
                ['label' => 'Ошибка',        'value' => 'failed'],
            ],
            'closure' => function ($value) {
                $labels = [
                    'draft'     => '<span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:12px;font-weight:500;background:#f3f4f6;color:#6b7280;">Черновик</span>',
                    'scheduled' => '<span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:12px;font-weight:500;background:#dbeafe;color:#1d4ed8;">Запланирована</span>',
                    'sending'   => '<span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:12px;font-weight:500;background:#fef3c7;color:#d97706;">Отправляется</span>',
                    'sent'      => '<span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:12px;font-weight:500;background:#dcfce7;color:#16a34a;">Отправлена</span>',
                    'failed'    => '<span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:12px;font-weight:500;background:#fee2e2;color:#dc2626;">Ошибка</span>',
                ];

                return $labels[$value->status] ?? $value->status;
            },
        ]);

        $this->addColumn([
            'index'    => 'total_recipients',
            'label'    => 'Получателей',
            'type'     => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'    => 'delivered_count',
            'label'    => 'Доставлено',
            'type'     => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'    => 'opened_count',
            'label'    => 'Открыто',
            'type'     => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'    => 'conversion_rate',
            'label'    => 'Конверсия',
            'type'     => 'string',
            'sortable' => true,
            'closure'  => function ($value) {
                return $value->conversion_rate . '%';
            },
        ]);

        $this->addColumn([
            'index'    => 'created_at',
            'label'    => 'Создана',
            'type'     => 'datetime',
            'sortable' => true,
        ]);
    }

    public function prepareActions()
    {
        if (bouncer()->hasPermission('marketing.push_notifications.campaigns.view')) {
            $this->addAction([
                'index'  => 'view',
                'icon'   => 'icon-eye',
                'title'  => 'Статистика',
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.marketing.push_notifications.campaigns.show', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('marketing.push_notifications.campaigns.send')) {
            $this->addAction([
                'index'   => 'send',
                'icon'    => 'icon-send',
                'title'   => 'Отправить',
                'method'  => 'POST',
                'url'     => function ($row) {
                    if (in_array($row->status, ['draft', 'failed'])) {
                        return route('admin.marketing.push_notifications.campaigns.send', $row->id);
                    }
                    return null;
                },
            ]);
        }

        if (bouncer()->hasPermission('marketing.push_notifications.campaigns.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => 'Удалить',
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.marketing.push_notifications.campaigns.delete', $row->id);
                },
            ]);
        }
    }
}
