<?php

namespace Webkul\RestApi\Config;

class AuthChannelFieldsConfig
{
    /**
     * Get all available field definitions for a specific channel.
     */
    public function getFields(string $channel): array
    {
        return match ($channel) {
            'sms'      => $this->getSmsFields(),
            'telegram' => $this->getTelegramFields(),
            'whatsapp' => $this->getWhatsAppFields(),
            default    => [],
        };
    }

    /**
     * Get all available channels.
     */
    public function getChannels(): array
    {
        return [
            'sms'      => 'SMS (REDSMS)',
            'telegram' => 'Telegram',
            'whatsapp' => 'WhatsApp (Green API)',
        ];
    }

    /**
     * Get SMS (REDSMS) fields.
     */
    protected function getSmsFields(): array
    {
        return [
            [
                'key'         => 'enabled',
                'title'       => 'rest-api::app.auth_channels.settings.sms.enabled',
                'type'        => 'boolean',
                'default'     => false,
                'description' => 'rest-api::app.auth_channels.settings.sms.enabled-info',
            ],
            [
                'key'         => 'login',
                'title'       => 'rest-api::app.auth_channels.settings.sms.login',
                'type'        => 'text',
                'description' => 'rest-api::app.auth_channels.settings.sms.login-info',
            ],
            [
                'key'         => 'api_key',
                'title'       => 'rest-api::app.auth_channels.settings.sms.api_key',
                'type'        => 'password',
                'description' => 'rest-api::app.auth_channels.settings.sms.api_key-info',
            ],
            [
                'key'         => 'from',
                'title'       => 'rest-api::app.auth_channels.settings.sms.from',
                'type'        => 'text',
                'description' => 'rest-api::app.auth_channels.settings.sms.from-info',
            ],
            [
                'key'         => 'auth_message_text',
                'title'       => 'rest-api::app.auth_channels.settings.sms.auth_message_text',
                'type'        => 'text',
                'default'     => 'Ваш код подтверждения',
                'description' => 'rest-api::app.auth_channels.settings.sms.auth_message_text-info',
            ],
            [
                'key'         => 'test_phone_numbers',
                'title'       => 'rest-api::app.auth_channels.settings.sms.test_phone_numbers',
                'type'        => 'textarea',
                'default'     => '',
                'description' => 'rest-api::app.auth_channels.settings.sms.test_phone_numbers-info',
            ],
        ];
    }

    /**
     * Get Telegram fields.
     */
    protected function getTelegramFields(): array
    {
        return [
            [
                'key'         => 'enabled',
                'title'       => 'rest-api::app.auth_channels.settings.telegram.enabled',
                'type'        => 'boolean',
                'default'     => false,
                'description' => 'rest-api::app.auth_channels.settings.telegram.enabled-info',
            ],
            [
                'key'         => 'bot_token',
                'title'       => 'rest-api::app.auth_channels.settings.telegram.bot_token',
                'type'        => 'password',
                'description' => 'rest-api::app.auth_channels.settings.telegram.bot_token-info',
            ],
            [
                'key'         => 'bot_link',
                'title'       => 'rest-api::app.auth_channels.settings.telegram.bot_link',
                'type'        => 'text',
                'description' => 'rest-api::app.auth_channels.settings.telegram.bot_link-info',
            ],
            [
                'key'         => 'app_link',
                'title'       => 'rest-api::app.auth_channels.settings.telegram.app_link',
                'type'        => 'text',
                'description' => 'rest-api::app.auth_channels.settings.telegram.app_link-info',
            ],
            [
                'key'         => 'start_message',
                'title'       => 'rest-api::app.auth_channels.settings.telegram.start_message',
                'type'        => 'textarea',
                'default'     => 'Добро пожаловать! Для авторизации, пожалуйста, поделитесь своим контактом, нажав на кнопку ниже.',
                'description' => 'rest-api::app.auth_channels.settings.telegram.start_message-info',
            ],
            [
                'key'         => 'test_phone_numbers',
                'title'       => 'rest-api::app.auth_channels.settings.telegram.test_phone_numbers',
                'type'        => 'textarea',
                'default'     => '',
                'description' => 'rest-api::app.auth_channels.settings.telegram.test_phone_numbers-info',
            ],
        ];
    }

    /**
     * Get WhatsApp (Green API) fields.
     */
    protected function getWhatsAppFields(): array
    {
        return [
            [
                'key'         => 'enabled',
                'title'       => 'rest-api::app.auth_channels.settings.whatsapp.enabled',
                'type'        => 'boolean',
                'default'     => false,
                'description' => 'rest-api::app.auth_channels.settings.whatsapp.enabled-info',
            ],
            [
                'key'         => 'id_instance',
                'title'       => 'rest-api::app.auth_channels.settings.whatsapp.id_instance',
                'type'        => 'text',
                'description' => 'rest-api::app.auth_channels.settings.whatsapp.id_instance-info',
            ],
            [
                'key'         => 'api_token_instance',
                'title'       => 'rest-api::app.auth_channels.settings.whatsapp.api_token_instance',
                'type'        => 'password',
                'description' => 'rest-api::app.auth_channels.settings.whatsapp.api_token_instance-info',
            ],
            [
                'key'         => 'url',
                'title'       => 'rest-api::app.auth_channels.settings.whatsapp.url',
                'type'        => 'text',
                'default'     => 'https://api.green-api.com',
                'description' => 'rest-api::app.auth_channels.settings.whatsapp.url-info',
            ],
            [
                'key'         => 'test_phone_numbers',
                'title'       => 'rest-api::app.auth_channels.settings.whatsapp.test_phone_numbers',
                'type'        => 'textarea',
                'default'     => '',
                'description' => 'rest-api::app.auth_channels.settings.whatsapp.test_phone_numbers-info',
            ],
        ];
    }
}
