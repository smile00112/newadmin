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
            // Configuration tab fields
            [
                'key'         => 'enabled',
                'title'       => 'iiko-integration::app.settings.enabled',
                'type'        => 'boolean',
                'default'     => false,
                'group'       => 'configuration',
                'description' => 'iiko-integration::app.settings.enabled-info',
            ],
            [
                'key'         => 'api_login',
                'title'       => 'iiko-integration::app.settings.api_login',
                'type'        => 'text',
                'group'       => 'configuration',
                'description' => 'iiko-integration::app.settings.api_login-info',
            ],
            [
                'key'         => 'base_url',
                'title'       => 'iiko-integration::app.settings.base_url',
                'type'        => 'text',
                'default'     => 'https://api-ru.iiko.services',
                'group'       => 'configuration',
                'description' => 'iiko-integration::app.settings.base_url-info',
            ],
            [
                'key'         => 'webhook_secret',
                'title'       => 'iiko-integration::app.settings.webhook_secret',
                'type'        => 'password',
                'group'       => 'configuration',
                'description' => 'iiko-integration::app.settings.webhook_secret-info',
            ],
            [
                'key'         => 'send_orders_to_iiko',
                'title'       => 'iiko-integration::app.settings.send_orders_to_iiko',
                'type'        => 'boolean',
                'default'     => false,
                'group'       => 'configuration',
                'description' => 'iiko-integration::app.settings.send_orders_to_iiko-info',
            ],
            [
                'key'         => 'sync_users',
                'title'       => 'iiko-integration::app.settings.sync_users',
                'type'        => 'boolean',
                'default'     => false,
                'group'       => 'configuration',
                'description' => 'iiko-integration::app.settings.sync_users-info',
            ],
            [
                'key'         => 'sync_bonuses',
                'title'       => 'iiko-integration::app.settings.sync_bonuses',
                'type'        => 'boolean',
                'default'     => false,
                'group'       => 'configuration',
                'description' => 'iiko-integration::app.settings.sync_bonuses-info',
            ],
            [
                'key'         => 'send_promocodes_to_iiko',
                'title'       => 'iiko-integration::app.settings.send_promocodes_to_iiko',
                'type'        => 'boolean',
                'default'     => false,
                'group'       => 'configuration',
                'description' => 'iiko-integration::app.settings.send_promocodes_to_iiko-info',
            ],
            // Import tab fields
            [
                'key'         => 'update_product_name',
                'title'       => 'iiko-integration::app.settings.update_product_name',
                'type'        => 'boolean',
                'default'     => true,
                'group'       => 'import',
                'description' => 'iiko-integration::app.settings.update_product_name-info',
            ],
            [
                'key'         => 'update_product_description',
                'title'       => 'iiko-integration::app.settings.update_product_description',
                'type'        => 'boolean',
                'default'     => true,
                'group'       => 'import',
                'description' => 'iiko-integration::app.settings.update_product_description-info',
            ],
            [
                'key'         => 'update_product_price',
                'title'       => 'iiko-integration::app.settings.update_product_price',
                'type'        => 'boolean',
                'default'     => true,
                'group'       => 'import',
                'description' => 'iiko-integration::app.settings.update_product_price-info',
            ],
            [
                'key'         => 'update_product_image',
                'title'       => 'iiko-integration::app.settings.update_product_image',
                'type'        => 'boolean',
                'default'     => true,
                'group'       => 'import',
                'description' => 'iiko-integration::app.settings.update_product_image-info',
            ],
            [
                'key'         => 'update_product_nutritional',
                'title'       => 'iiko-integration::app.settings.update_product_nutritional',
                'type'        => 'boolean',
                'default'     => true,
                'group'       => 'import',
                'description' => 'iiko-integration::app.settings.update_product_nutritional-info',
            ],
        ];
    }
}
