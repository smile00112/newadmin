<?php

return [
    'test_url' => 'https://alfa.rbsuat.com/payment/rest/',
    'prod_url' => 'https://pay.alfabank.ru/payment/rest/',

    'log_channel' => env('ALFABANK_LOG_CHANNEL', 'daily'),
    'log_enabled' => env('ALFABANK_LOG_ENABLED', true),
];
