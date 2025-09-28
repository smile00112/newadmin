<?php

return [
    'flatrate' => [
        'code'         => 'flatrate',
        'title'        => 'Flat Rate',
        'description'  => 'Flat Rate Shipping',
        'active'       => true,
        'default_rate' => '10',
        'type'         => 'per_unit',
        'class'        => 'Webkul\Shipping\Carriers\FlatRate',
    ],

    'free' => [
        'code'         => 'free',
        'title'        => 'Free Shipping',
        'description'  => 'Free Shipping',
        'active'       => true,
        'default_rate' => '0',
        'class'        => 'Webkul\Shipping\Carriers\Free',
    ],
    
    'zone' => [
        'code'         => 'zone',
        'title'        => 'Zone Delivery',
        'description'  => 'Zone-based Delivery',
        'active'       => false,
        'default_rate' => '0',
        'type'         => 'per_unit',
        'class'        => 'Webkul\Shipping\Carriers\Zone',
    ],
    
    'pickup' => [
        'code'         => 'pickup',
        'title'        => 'Pickup',
        'description'  => 'Store Pickup',
        'active'       => true,
        'default_rate' => '0',
        'class'        => 'Webkul\Shipping\Carriers\Pickup',
    ],
];
