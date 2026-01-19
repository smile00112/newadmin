<?php

namespace Webkul\IikoIntegration\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Repositories\AbstractSettingRepository;
use Webkul\IikoIntegration\Config\IikoFieldsConfig;
use Webkul\IikoIntegration\Models\IikoSetting;

class IikoSettingRepository extends AbstractSettingRepository
{
    /**
     * Create a new repository instance.
     */
    public function __construct(
        protected IikoFieldsConfig $fieldsConfig,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return IikoSetting::class;
    }

    /**
     * Get cache key prefix for this repository.
     */
    protected function getCachePrefix(): string
    {
        return 'iiko_settings';
    }

    /**
     * Get settings with field definitions for admin form.
     */
    public function getSettingsWithFields(?string $channelCode = null): array
    {
        $fields = $this->fieldsConfig->getFields();
        $settings = $this->getAllSettings(IikoSetting::CHANNEL, $channelCode);

        foreach ($fields as &$field) {
            $field['value'] = $settings[$field['key']] ?? ($field['default'] ?? null);
        }

        return $fields;
    }

    /**
     * Check if integration is enabled.
     */
    public function isIntegrationEnabled(?string $channelCode = null): bool
    {
        return parent::isEnabled(IikoSetting::CHANNEL, 'enabled', $channelCode);
    }

    /**
     * Get setting value with fallback to config.
     */
    public function getSettingWithFallback(string $key, ?string $channelCode = null): mixed
    {
        $value = $this->getSetting(IikoSetting::CHANNEL, $key, $channelCode);

        if ($value === null) {
            return config("services.iiko.{$key}");
        }

        return $value;
    }

    /**
     * Clear iiko settings cache.
     */
    public function clearCache(?string $channelCode = null): void
    {
        $cacheKey = $this->buildCacheKey(IikoSetting::CHANNEL, $channelCode);
        \Illuminate\Support\Facades\Cache::forget($cacheKey);
        unset($this->memoryCache[$cacheKey]);
    }
}
