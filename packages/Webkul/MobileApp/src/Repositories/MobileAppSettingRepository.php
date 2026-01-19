<?php

namespace Webkul\MobileApp\Repositories;

use Illuminate\Support\Facades\Cache;
use Webkul\Core\Eloquent\Repository;
use Webkul\MobileApp\Config\FieldsConfig;
use Webkul\MobileApp\Contracts\MobileAppSetting;

/**
 * Repository for mobile app settings with caching.
 * 
 * Note: This repository uses a flat structure (no group column),
 * so it implements caching directly instead of extending AbstractSettingRepository.
 */
class MobileAppSettingRepository extends Repository
{
    /**
     * Cache TTL in seconds (default: 10 minutes).
     */
    protected int $cacheTtl = 600;

    /**
     * In-memory cache for current request.
     */
    protected array $memoryCache = [];

    /**
     * Cache key prefix.
     */
    protected const CACHE_PREFIX = 'mobile_app_settings';

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return MobileAppSetting::class;
    }

    /**
     * Get all settings as key-value pairs with caching.
     */
    public function getAllSettings(?string $channelCode = null): array
    {
        $cacheKey = $this->buildCacheKey($channelCode);

        // Check in-memory cache first (fastest)
        if (isset($this->memoryCache[$cacheKey])) {
            return $this->memoryCache[$cacheKey];
        }

        // Then check persistent cache
        $settings = Cache::remember($cacheKey, $this->cacheTtl, function () use ($channelCode) {
            return $this->fetchAllSettings($channelCode);
        });

        // Store in in-memory cache
        $this->memoryCache[$cacheKey] = $settings;

        return $settings;
    }

    /**
     * Fetch all settings from database (no cache).
     */
    protected function fetchAllSettings(?string $channelCode = null): array
    {
        $query = $this->model->query();

        if ($channelCode) {
            $query->where('channel_code', $channelCode);
        }

        $result = [];

        foreach ($query->get() as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    /**
     * Get a specific setting value (from cached settings).
     */
    public function getSetting(string $key, ?string $channelCode = null): mixed
    {
        $settings = $this->getAllSettings($channelCode);

        return $settings[$key] ?? null;
    }

    /**
     * Set a setting value and clear cache.
     */
    public function setSetting(string $key, mixed $value, ?string $channelCode = null): void
    {
        $this->updateOrCreate(
            [
                'key'          => $key,
                'channel_code' => $channelCode,
            ],
            [
                'value' => $value,
            ]
        );

        $this->clearCache($channelCode);
    }

    /**
     * Save multiple settings at once.
     */
    public function saveSettings(array $settings, ?string $channelCode = null): void
    {
        foreach ($settings as $key => $value) {
            $this->updateOrCreate(
                [
                    'key'          => $key,
                    'channel_code' => $channelCode,
                ],
                [
                    'value' => $value,
                ]
            );
        }

        $this->clearCache($channelCode);
    }

    /**
     * Get settings with field definitions for admin form.
     */
    public function getSettingsWithFields(?string $channelCode = null): array
    {
        $fieldsConfig = app(FieldsConfig::class);
        $fields = $fieldsConfig->getFields();
        $settings = $this->getAllSettings($channelCode);

        foreach ($fields as &$field) {
            $field['value'] = $settings[$field['key']] ?? ($field['default'] ?? null);

            if (isset($field['source'])) {
                $field['options'] = $fieldsConfig->getOptionsForSource($field['source']);
            }
        }

        return $fields;
    }

    /**
     * Build cache key.
     */
    protected function buildCacheKey(?string $channelCode): string
    {
        return self::CACHE_PREFIX . ':' . ($channelCode ?? 'default');
    }

    /**
     * Clear cache for a specific channel.
     */
    public function clearCache(?string $channelCode = null): void
    {
        $cacheKey = $this->buildCacheKey($channelCode);
        Cache::forget($cacheKey);
        unset($this->memoryCache[$cacheKey]);
    }

    /**
     * Clear all caches.
     */
    public function clearAllCache(): void
    {
        $this->clearCache(null);
        $this->clearCache(core()->getCurrentChannelCode());
        $this->memoryCache = [];
    }
}
