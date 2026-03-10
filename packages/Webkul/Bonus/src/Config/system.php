<?php

use Webkul\Sales\Models\Order;
use Webkul\Bonus\Models\BonusLevel;

return [
    /**
     * Bonus System Settings.
     */
    [
        'key'  => 'bonus',
        'name' => 'bonus::app.admin.settings.title',
        'info' => 'bonus::app.admin.settings.info',
        'sort' => 11,
    ],
    [
        'key'    => 'bonus.general',
        'name'   => 'bonus::app.admin.settings.general.title',
        'info'   => 'bonus::app.admin.settings.general.info',
        'icon'   => 'settings/store.svg',
        'sort'   => 1,
        // Убираем fields отсюда - они будут в дочернем элементе
    ],
    [
        'key'    => 'bonus.general.settings',
        'name'   => 'bonus::app.admin.settings.general.title',
        'info'   => 'bonus::app.admin.settings.general.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'enabled',
                'title'         => 'bonus::app.admin.settings.fields.enabled',
                'type'          => 'boolean',
                'default'       => false,
                'channel_based' => true,
            ],
            [
                'name'          => 'calculation_type',
                'title'         => 'bonus::app.admin.settings.fields.calculation-type',
                'type'          => 'select',
                'default'       => BonusLevel::CALCULATION_TYPE_TOTAL_SPENT,
                'channel_based' => true,
                'options'       => [
                    [
                        'title' => 'bonus::app.admin.settings.fields.calculation-type-total-spent',
                        'value' => BonusLevel::CALCULATION_TYPE_TOTAL_SPENT,
                    ],
                    [
                        'title' => 'bonus::app.admin.settings.fields.calculation-type-orders-count',
                        'value' => BonusLevel::CALCULATION_TYPE_ORDERS_COUNT,
                    ],
                    [
                        'title' => 'bonus::app.admin.settings.fields.calculation-type-cart-value',
                        'value' => BonusLevel::CALCULATION_TYPE_CART_VALUE,
                    ],
                ],
            ],
            [
                'name'          => 'max_usage_percent',
                'title'         => 'bonus::app.admin.settings.fields.max-usage-percent',
                'type'          => 'text',
                'default'       => 100,
                'info'          => 'bonus::app.admin.settings.fields.max-usage-percent-info',
                'channel_based' => true,
            ],
            [
                'name'          => 'expiry_days',
                'title'         => 'bonus::app.admin.settings.fields.expiry-days',
                'type'          => 'text',
                'default'       => 365,
                'info'          => 'bonus::app.admin.settings.fields.expiry-days-info',
                'channel_based' => true,
            ],
            [
                'name'          => 'accrual_status',
                'title'         => 'bonus::app.admin.settings.fields.accrual-status',
                'type'          => 'select',
                'channel_based' => true,
                'options'       => 'Webkul\Sales\Models\OrderStatus@getConfigOptions',
            ],
            [
                'name'          => 'deduction_status',
                'title'         => 'bonus::app.admin.settings.fields.deduction-status',
                'type'          => 'select',
                'channel_based' => true,
                'options'       => 'Webkul\Sales\Models\OrderStatus@getConfigOptions',
            ],
            [
                'name'          => 'refund_status',
                'title'         => 'bonus::app.admin.settings.fields.refund-status',
                'type'          => 'select',
                'channel_based' => true,
                'options'       => 'Webkul\Sales\Models\OrderStatus@getConfigOptions',
            ],
            [
                'name'          => 'participating_product_ids',
                'title'         => 'bonus::app.admin.settings.fields.participating-products',
                'type'          => 'multiselect',
                'info'          => 'bonus::app.admin.settings.fields.participating-products-info',
                'channel_based' => true,
            ],
            [
                'name'          => 'excluded_product_ids',
                'title'         => 'bonus::app.admin.settings.fields.excluded-products',
                'type'          => 'multiselect',
                'info'          => 'bonus::app.admin.settings.fields.excluded-products-info',
                'channel_based' => true,
            ],
        ],
    ],
    [
        'key'    => 'bonus.general.manage',
        'name'   => 'bonus::app.admin.settings.manage.title',
        'info'   => 'bonus::app.admin.settings.manage.info',
        'sort'   => 2,
        'fields' => [
            [
                'name' => 'manage_bonuses',
                'title' => 'bonus::app.admin.settings.manage.title',
                'type' => 'blade',
                'path' => 'bonus::admin.settings.manage',
            ],
        ],
    ],
    [
        'key'    => 'bonus.general.levels',
        'name'   => 'bonus::app.admin.settings.levels.title',
        'info'   => 'bonus::app.admin.settings.levels.info',
        'sort'   => 3,
        'fields' => [
            [
                'name' => 'manage_levels',
                'title' => 'bonus::app.admin.settings.levels.title',
                'type' => 'blade',
                'path' => 'bonus::admin.settings.levels',
            ],
        ],
    ],
];
