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

    'alfabank' => [
        'code'        => 'alfabank',
        'title'       => 'Альфа-Банк',
        'description' => 'Оплата картой через Альфа-Банк',
        'class'       => 'Webkul\AlfabankPayment\Payment\AlfabankPayment',
        'active'      => true,
        'sort'        => 3,
    ],
];
