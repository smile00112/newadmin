<?php

return [
    'cashondelivery'  => [
        'code'        => 'cashondelivery',
        'title'       => 'Наличные',
        'description' => 'Оплата наличными',
        'class'       => 'Webkul\Payment\Payment\CashOnDelivery',
        'active'      => true,
        'sort'        => 1,
    ],

    'moneytransfer'   => [
        'code'        => 'moneytransfer',
        'title'       => 'Картой',
        'description' => 'Оплата картой',
        'class'       => 'Webkul\Payment\Payment\MoneyTransfer',
        'active'      => true,
        'sort'        => 2,
    ],
];
