<?php

declare(strict_types=1);

return [
    [
        'key'   => 'external-payments',
        'name'  => 'external-payments::app.admin.menu.external-payments',
        'route' => 'admin.external-payments.systems.index',
        'sort'  => 51,
        'icon'  => 'icon-api',
    ],
    [
        'key'   => 'external-payments.systems',
        'name'  => 'external-payments::app.admin.menu.systems',
        'route' => 'admin.external-payments.systems.index',
        'sort'  => 1,
        'icon'  => '',
    ],
];
