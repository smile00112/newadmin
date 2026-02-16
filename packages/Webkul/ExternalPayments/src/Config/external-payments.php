<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | API prefix
    |--------------------------------------------------------------------------
    |
    | URL prefix for external payments API routes.
    |
    */
    'api' => [
        'prefix' => 'external-payments',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment providers
    |--------------------------------------------------------------------------
    |
    | Available payment providers for external systems. Each provider must
    | have an adapter registered (see adapters below).
    |
    */
    'providers' => [
        'tochka' => [
            'name'    => 'Tochka Bank',
            'enabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider adapters
    |--------------------------------------------------------------------------
    |
    | Class names for payment provider adapters (implement
    | PaymentProviderAdapterInterface). Key must match provider key above.
    |
    */
    'adapters' => [
        'tochka' => \Webkul\ExternalPayments\Services\Adapters\TochkaPaymentAdapter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Minimum amount (fallback)
    |--------------------------------------------------------------------------
    |
    | Default minimum payment amount when provider does not define one.
    |
    */
    'min_amount' => 1.00,
];
