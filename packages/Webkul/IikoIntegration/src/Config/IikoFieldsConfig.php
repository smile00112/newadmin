<?php

namespace Webkul\IikoIntegration\Config;

class IikoFieldsConfig
{
    /**
     * Get all available field definitions for iiko integration.
     */
    public function getFields(): array
    {
        return [
            [
                'key'         => 'enabled',
                'title'       => 'iiko-integration::app.settings.enabled',
                'type'        => 'boolean',
                'default'     => false,
                'description' => 'iiko-integration::app.settings.enabled-info',
            ],
            [
                'key'         => 'api_login',
                'title'       => 'iiko-integration::app.settings.api_login',
                'type'        => 'text',
                'description' => 'iiko-integration::app.settings.api_login-info',
            ],
            [
                'key'         => 'api_password',
                'title'       => 'iiko-integration::app.settings.api_password',
                'type'        => 'password',
                'description' => 'iiko-integration::app.settings.api_password-info',
            ],
            [
                'key'         => 'base_url',
                'title'       => 'iiko-integration::app.settings.base_url',
                'type'        => 'text',
                'default'     => 'https://api-ru.iiko.services',
                'description' => 'iiko-integration::app.settings.base_url-info',
            ],
            [
                'key'         => 'organization_id',
                'title'       => 'iiko-integration::app.settings.organization_id',
                'type'        => 'text',
                'description' => 'iiko-integration::app.settings.organization_id-info',
            ],
            [
                'key'         => 'terminal_group_id',
                'title'       => 'iiko-integration::app.settings.terminal_group_id',
                'type'        => 'text',
                'description' => 'iiko-integration::app.settings.terminal_group_id-info',
            ],
            [
                'key'         => 'webhook_secret',
                'title'       => 'iiko-integration::app.settings.webhook_secret',
                'type'        => 'password',
                'description' => 'iiko-integration::app.settings.webhook_secret-info',
            ],
        ];
    }
}
