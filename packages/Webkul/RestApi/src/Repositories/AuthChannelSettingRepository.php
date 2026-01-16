<?php

namespace Webkul\RestApi\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;
use Webkul\RestApi\Config\AuthChannelFieldsConfig;
use Webkul\RestApi\Models\AuthChannelSetting;

class AuthChannelSettingRepository extends Repository
{
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
     * Get all settings for a channel as key-value pairs.
     */
    public function getAllSettings(string $channel, ?string $channelCode = null): array
    {
        $query = $this->model->query()->where('channel', $channel);

        if ($channelCode) {
            $query->where('channel_code', $channelCode);
        }

        $settings = $query->get();

        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    /**
     * Get a specific setting value.
     */
    public function getSetting(string $channel, string $key, ?string $channelCode = null): mixed
    {
        $query = $this->model->query()
            ->where('channel', $channel)
            ->where('key', $key);

        if ($channelCode) {
            $query->where('channel_code', $channelCode);
        }

        $setting = $query->first();

        return $setting?->value;
    }

    /**
     * Set a setting value.
     */
    public function setSetting(string $channel, string $key, mixed $value, ?string $channelCode = null): void
    {
        $this->updateOrCreate(
            [
                'channel'      => $channel,
                'key'          => $key,
                'channel_code' => $channelCode,
            ],
            [
                'value' => $value,
            ]
        );
    }

    /**
     * Save multiple settings at once.
     */
    public function saveSettings(string $channel, array $settings, ?string $channelCode = null): void
    {
        foreach ($settings as $key => $value) {
            $this->setSetting($channel, $key, $value, $channelCode);
        }
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
        $enabled = $this->getSetting($channel, 'enabled', $channelCode);

        return (bool) $enabled;
    }
}
