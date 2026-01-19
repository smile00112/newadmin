<?php

namespace Webkul\Core\Repositories;

use Illuminate\Support\Facades\Cache;
use Webkul\Core\Eloquent\Repository;

/**
 * Abstract repository for settings with built-in caching.
 * 
 * Provides a standardized way to manage key-value settings with:
 * - In-memory caching (per-request)
 * - Persistent caching (Redis/file)
 * - Automatic cache invalidation on updates
 */
abstract class AbstractSettingRepository extends Repository
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
     * Get cache key prefix for this repository.
     */
    abstract protected function getCachePrefix(): string;

    /**
     * Get the group/channel column name used for filtering.
     * Override in child class if different column name is used.
     */
    protected function getGroupColumn(): string
    {
        return 'channel';
    }

    /**
     * Get all settings for a group as key-value pairs with caching.
     */
    public function getAllSettings(string $group, ?string $channelCode = null): array
    {
        $cacheKey = $this->buildCacheKey($group, $channelCode);

        // Check in-memory cache first (fastest)
        if (isset($this->memoryCache[$cacheKey])) {
            return $this->memoryCache[$cacheKey];
        }

        // Then check persistent cache
        $settings = Cache::remember($cacheKey, $this->cacheTtl, function () use ($group, $channelCode) {
            return $this->fetchAllSettings($group, $channelCode);
        });

        // Store in in-memory cache
        $this->memoryCache[$cacheKey] = $settings;

        return $settings;
    }

    /**
     * Fetch all settings from database (no cache).
     */
    protected function fetchAllSettings(string $group, ?string $channelCode = null): array
    {
        $query = $this->model->query()->where($this->getGroupColumn(), $group);

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
    public function getSetting(string $group, string $key, ?string $channelCode = null): mixed
    {
        $settings = $this->getAllSettings($group, $channelCode);

        return $settings[$key] ?? null;
    }

    /**
     * Set a setting value and clear cache.
     */
    public function setSetting(string $group, string $key, mixed $value, ?string $channelCode = null): void
    {
        $this->updateOrCreate(
            [
                $this->getGroupColumn() => $group,
                'key'                   => $key,
                'channel_code'          => $channelCode,
            ],
            [
                'value' => $value,
            ]
        );

        $this->clearCache($group, $channelCode);
    }

    /**
     * Save multiple settings at once.
     */
    public function saveSettings(string $group, array $settings, ?string $channelCode = null): void
    {
        foreach ($settings as $key => $value) {
            $this->updateOrCreate(
                [
                    $this->getGroupColumn() => $group,
                    'key'                   => $key,
                    'channel_code'          => $channelCode,
                ],
                [
                    'value' => $value,
                ]
            );
        }

        $this->clearCache($group, $channelCode);
    }

    /**
     * Check if a boolean setting is enabled.
     */
    public function isEnabled(string $group, string $key = 'enabled', ?string $channelCode = null): bool
    {
        return (bool) $this->getSetting($group, $key, $channelCode);
    }

    /**
     * Build cache key.
     */
    protected function buildCacheKey(string $group, ?string $channelCode): string
    {
        $prefix = $this->getCachePrefix();

        return "{$prefix}:{$group}:" . ($channelCode ?? 'default');
    }

    /**
     * Clear cache for a specific group.
     */
    public function clearCache(string $group, ?string $channelCode = null): void
    {
        $cacheKey = $this->buildCacheKey($group, $channelCode);
        Cache::forget($cacheKey);
        unset($this->memoryCache[$cacheKey]);
    }

    /**
     * Clear all caches for specified groups.
     */
    public function clearAllCache(array $groups): void
    {
        $channelCode = core()->getCurrentChannelCode();

        foreach ($groups as $group) {
            $this->clearCache($group, null);
            $this->clearCache($group, $channelCode);
        }

        $this->memoryCache = [];
    }

    /**
     * Set cache TTL in seconds.
     */
    public function setCacheTtl(int $seconds): static
    {
        $this->cacheTtl = $seconds;

        return $this;
    }

    /**
     * Get cache TTL.
     */
    public function getCacheTtl(): int
    {
        return $this->cacheTtl;
    }

    /**
     * Preload settings for multiple groups (useful for batch operations).
     */
    public function preloadSettings(array $groups, ?string $channelCode = null): void
    {
        foreach ($groups as $group) {
            $this->getAllSettings($group, $channelCode);
        }
    }
}
