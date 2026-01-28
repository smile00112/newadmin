<?php

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
        'fields' => [
            [
                'name'    => 'enabled',
                'title'   => 'bonus::app.admin.settings.fields.enabled',
                'type'    => 'boolean',
                'default' => false,
            ],
            [
                'name'    => 'max_usage_percent',
                'title'   => 'bonus::app.admin.settings.fields.max-usage-percent',
                'type'    => 'text',
                'default' => 100,
                'info'    => 'bonus::app.admin.settings.fields.max-usage-percent-info',
            ],
            [
                'name'    => 'expiry_days',
                'title'   => 'bonus::app.admin.settings.fields.expiry-days',
                'type'    => 'text',
                'default' => 365,
                'info'    => 'bonus::app.admin.settings.fields.expiry-days-info',
            ],
            [
                'name'  => 'participating_product_ids',
                'title' => 'bonus::app.admin.settings.fields.participating-products',
                'type'  => 'multiselect',
                'info'  => 'bonus::app.admin.settings.fields.participating-products-info',
            ],
            [
                'name'  => 'excluded_product_ids',
                'title' => 'bonus::app.admin.settings.fields.excluded-products',
                'type'  => 'multiselect',
                'info'  => 'bonus::app.admin.settings.fields.excluded-products-info',
            ],
        ],
    ],
];
