<?php

return [
    [
        'key'        => 'tochka-payment',
        'name'       => 'tochka-payment::app.admin.menu.tochka-payment',
        'route'      => 'admin.tochka-payment.history.index',
        'sort'       => 50,
        'icon'       => 'icon-payment',
    ],
    [
        'key'        => 'tochka-payment.history',
        'name'       => 'tochka-payment::app.admin.menu.payment-history',
        'route'      => 'admin.tochka-payment.history.index',
        'sort'       => 1,
        'icon'       => '',
    ],
    [
        'key'        => 'tochka-payment.settings',
        'name'       => 'tochka-payment::app.admin.menu.settings',
        'route'      => 'admin.tochka-payment.settings.index',
        'sort'       => 2,
        'icon'       => '',
    ],
    [
        'key'        => 'tochka-payment.test-order',
        'name'       => 'tochka-payment::app.admin.menu.test-order',
        'route'      => 'admin.tochka-payment.test-order.index',
        'sort'       => 3,
        'icon'       => '',
    ],
];
