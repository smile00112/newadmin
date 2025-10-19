<?php

return [
    'default_whatsapp_instance' => env('NEWSLETTERS_DEFAULT_VACAP_INSTANCE', 1),

    'rate_limits' => [
        'sms_per_minute' => env('NEWSLETTERS_SMS_PER_MINUTE', 60),
        'sms_per_hour' => env('NEWSLETTERS_SMS_PER_HOUR', 1000),
        'sms_per_day' => env('NEWSLETTERS_SMS_PER_DAY', 10000),
    ],

    'retry_settings' => [
        'max_retries' => env('NEWSLETTERS_MAX_RETRIES', 3),
        'retry_delay' => env('NEWSLETTERS_RETRY_DELAY', 60), // seconds
    ],

    'validation' => [
        'phone_number_pattern' => '/^\+?[1-9]\d{1,14}$/',
        'max_message_length' => env('NEWSLETTERS_MAX_MESSAGE_LENGTH', 160),
    ],

    'queue' => [
        'connection' => env('NEWSLETTERS_QUEUE_CONNECTION', 'default'),
        'queue' => env('NEWSLETTERS_QUEUE_NAME', 'newsletters'),
    ],

    'cache' => [
        'ttl' => env('NEWSLETTERS_CACHE_TTL', 3600), // 1 hour
    ],
];








