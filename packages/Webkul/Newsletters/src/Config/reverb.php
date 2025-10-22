<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reverb Server Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Reverb WebSocket server.
    | You can customize these settings based on your application's needs.
    |
    */

    'host' => env('REVERB_HOST', 'localhost'),
    'port' => env('REVERB_PORT', 8080),
    'scheme' => env('REVERB_SCHEME', 'http'),
    'app_id' => env('REVERB_APP_ID'),
    'app_key' => env('REVERB_APP_KEY'),
    'app_secret' => env('REVERB_APP_SECRET'),
    'use_tls' => env('REVERB_SCHEME', 'http') === 'https',
    'options' => [
        'cluster' => env('REVERB_CLUSTER', 'mt1'),
    ],
];
