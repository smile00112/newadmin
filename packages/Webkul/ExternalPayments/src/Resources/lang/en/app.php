<?php

declare(strict_types=1);

return [
    'admin' => [
        'menu' => [
            'external-payments' => 'External Payments',
            'systems'           => 'External Systems',
        ],
        'systems' => [
            'index' => [
                'title'           => 'External Systems',
                'name'            => 'Name',
                'webhook_url'     => 'Webhook URL',
                'providers'       => 'Payment providers',
                'is_active'       => 'Active',
                'actions'          => 'Actions',
                'edit'            => 'Edit',
                'create'           => 'Create External System',
                'yes'             => 'Yes',
                'no'              => 'No',
            ],
            'create' => [
                'title'           => 'Create External System',
                'name'            => 'Name',
                'api_token'       => 'API Token',
                'api_token_help'  => 'Leave empty to auto-generate. Use this token in Authorization: Bearer header.',
                'webhook_url'     => 'Webhook URL',
                'webhook_url_help' => 'URL to receive payment success notifications (optional).',
                'is_active'       => 'Active',
                'payment_providers' => 'Payment providers',
                'payment_providers_help' => 'Select at least one. Mark one as default for requests without payment_provider.',
                'allowed'         => 'Allowed',
                'default'         => 'Default',
                'save'            => 'Save',
                'cancel'          => 'Cancel',
            ],
            'edit' => [
                'title'           => 'Edit External System',
                'api_token_help'  => 'Leave empty to keep current token.',
                'save'            => 'Save',
            ],
            'messages' => [
                'created' => 'External system created successfully.',
                'updated' => 'External system updated successfully.',
                'deleted' => 'External system deleted successfully.',
                'validation' => [
                    'providers_required' => 'Select at least one payment provider.',
                    'default_must_be_allowed' => 'Default provider must be one of the allowed providers.',
                ],
            ],
        ],
    ],
    'api' => [
        'token_required'       => 'Authentication token required',
        'token_invalid'       => 'Invalid or inactive token',
        'no_default_provider' => 'No payment provider specified and no default set for this system',
        'provider_not_allowed'=> 'Payment provider is not allowed for this system',
        'unknown_provider'    => 'Unknown payment provider',
        'validation_failed'   => 'Validation failed',
        'create_failed'       => 'Failed to create payment',
    ],
];
