<?php

return [
    'flatrate' => [
        'code'         => 'flatrate',
        'title'        => 'Платная доставка',
        'description'  => 'Доставка по фиксированной ставке',
        'active'       => true,
        'default_rate' => '10',
        'type'         => 'per_unit',
        'class'        => 'Webkul\Shipping\Carriers\FlatRate',
    ],

    'free' => [
        'code'         => 'free',
        'title'        => 'Бесплатная доставка',
        'description'  => 'Бесплатная доставка',
        'active'       => true,
        'default_rate' => '0',
        'class'        => 'Webkul\Shipping\Carriers\Free',
    ],

    'zone' => [
        'code'         => 'zone',
        'title'        => 'Доставка по зонам',
        'description'  => 'Доставка по зонам',
        'active'       => false,
        'default_rate' => '0',
        'type'         => 'per_unit',
        'class'        => 'Webkul\Shipping\Carriers\Zone',
    ],

    'pickup' => [
        'code'         => 'pickup',
        'title'        => 'Самовывоз',
        'description'  => 'Самовывоз',
        'active'       => true,
        'default_rate' => '0',
        'class'        => 'Webkul\Shipping\Carriers\Pickup',
    ],

    'dinein' => [
        'code'         => 'dinein',
        'title'        => 'В зале',
        'description'  => 'Получение в зале',
        'active'       => true,
        'default_rate' => '0',
        'class'        => 'Webkul\Shipping\Carriers\DineIn',
    ],
];
