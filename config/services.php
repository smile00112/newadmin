<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'exchange_api' => [
        'default' => 'exchange_rates',

        'fixer' => [
            'key'   => env('FIXER_API_KEY'),
            'class' => 'Webkul\Core\Helpers\Exchange\FixerExchange',
        ],

        'exchange_rates' => [
            'key'   => env('EXCHANGE_RATES_API_KEY'),
            'class' => 'Webkul\Core\Helpers\Exchange\ExchangeRates',
            'url'   => env('EXCHANGE_RATES_API_ENDPOINT'),
        ],
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_CALLBACK_URL'),
    ],

    'twitter' => [
        'client_id'     => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect'      => env('TWITTER_CALLBACK_URL'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_CALLBACK_URL'),
    ],

    'linkedin-openid' => [
        'client_id'     => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect'      => env('LINKEDIN_CALLBACK_URL'),
    ],

    'github' => [
        'client_id'     => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect'      => env('GITHUB_CALLBACK_URL'),
    ],

    // Firebase / FCM configuration
    'firebase' => [
        // VAPID key for Web Push (from .env FCM_API_VAPID_KEY preferred)
        'vapid_key' => env('FCM_API_VAPID_KEY', env('FCM_VAPID_KEY')),

        // Public web client config for Firebase initialization on the frontend
        'client' => [
            'apiKey'            => env('FCM_API_KEY'),
            'authDomain'        => env('FCM_AUTH_DOMAIN'),
            'projectId'         => env('FCM_PROJECT_ID'),
            'storageBucket'     => env('FCM_STORAGE_BUCKET'),
            'messagingSenderId' => env('FCM_MESSAGING_SENDER_ID'),
            'appId'             => env('FCM_APP_ID'),
        ],

        // Path to Service Account JSON for server-side messaging
        'credentials' => env('FCM_CREDENTIALS_PATH', base_path('firebase-credentials.json')),
    ],
];
