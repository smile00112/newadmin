<?php

namespace Webkul\RestApi\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Repositories\AbstractSettingRepository;
use Webkul\RestApi\Config\AuthChannelFieldsConfig;
use Webkul\RestApi\Models\AuthChannelSetting;

class AuthChannelSettingRepository extends AbstractSettingRepository
{
    /**
     * Available auth channels for cache clearing.
     */
    protected const AUTH_CHANNELS = ['sms', 'whatsapp', 'telegram'];

    /**
     * Create a new repository instance.
     */
    public function __construct(
        protected AuthChannelFieldsConfig $fieldsConfig,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return AuthChannelSetting::class;
    }

    /**
     * Get cache key prefix for this repository.
     */
    protected function getCachePrefix(): string
    {
        return 'auth_channel_settings';
    }

    /**
     * Get settings with field definitions for admin form.
     */
    public function getSettingsWithFields(string $channel, ?string $channelCode = null): array
    {
        $fields = $this->fieldsConfig->getFields($channel);
        $settings = $this->getAllSettings($channel, $channelCode);

        foreach ($fields as &$field) {
            $field['value'] = $settings[$field['key']] ?? ($field['default'] ?? null);
        }

        return $fields;
    }

    /**
     * Check if channel is enabled.
     */
    public function isChannelEnabled(string $channel, ?string $channelCode = null): bool
    {
        return $this->isEnabled($channel, 'enabled', $channelCode);
    }

    /**
     * Clear all auth channel caches.
     */
    public function clearAllAuthChannelCache(): void
    {
        $this->clearAllCache(self::AUTH_CHANNELS);
    }

    /**
     * Preload all auth channel settings (useful at request start).
     */
    public function preloadAllAuthChannels(?string $channelCode = null): void
    {
        $this->preloadSettings(self::AUTH_CHANNELS, $channelCode);
    }
}
