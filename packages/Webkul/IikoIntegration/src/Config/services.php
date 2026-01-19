<?php

return [
    'iiko' => [
        'api_login'         => env('IIKO_API_LOGIN'),
        'api_password'     => env('IIKO_API_PASSWORD'),
        'base_url'         => env('IIKO_BASE_URL', 'https://api-ru.iiko.services'),
        'organization_id'  => env('IIKO_ORGANIZATION_ID'),
        'terminal_group_id' => env('IIKO_TERMINAL_GROUP_ID'),
        'webhook_secret'   => env('IIKO_WEBHOOK_SECRET'),
    ],
];
