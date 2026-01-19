<?php

namespace Webkul\RestApi\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Trait for API response caching.
 *
 * Provides caching functionality for API controllers with static reference data.
 */
trait ProvideApiCache
{
    /**
     * Default cache TTL in seconds (24 hours for static data).
     */
    protected int $cacheTtl = 86400;

    /**
     * Get cache key prefix for this controller.
     */
    protected function getCachePrefix(): string
    {
        return 'api_' . strtolower(class_basename(static::class));
    }

    /**
     * Build cache key with optional parameters.
     */
    protected function buildCacheKey(string $suffix = '', array $params = []): string
    {
        $key = $this->getCachePrefix();

        if ($suffix) {
            $key .= ':' . $suffix;
        }

        if (!empty($params)) {
            $key .= ':' . md5(serialize($params));
        }

        return $key;
    }

    /**
     * Get cached data or execute callback and cache result.
     */
    protected function cachedResponse(string $suffix, callable $callback, ?int $ttl = null): mixed
    {
        $cacheKey = $this->buildCacheKey($suffix);

        return Cache::remember($cacheKey, $ttl ?? $this->cacheTtl, $callback);
    }

    /**
     * Get cached data with request parameters in cache key.
     */
    protected function cachedResponseWithParams(string $suffix, array $params, callable $callback, ?int $ttl = null): mixed
    {
        $cacheKey = $this->buildCacheKey($suffix, $params);

        return Cache::remember($cacheKey, $ttl ?? $this->cacheTtl, $callback);
    }

    /**
     * Clear cache for this controller.
     */
    public function clearCache(?string $suffix = null): void
    {
        if ($suffix) {
            Cache::forget($this->buildCacheKey($suffix));
        }
    }

    /**
     * Clear cache by pattern (requires cache driver support).
     */
    public static function clearAllCache(): void
    {
        $prefix = 'api_' . strtolower(class_basename(static::class));
        
        // For drivers that support tags or pattern deletion
        // This is a fallback that clears specific known keys
        Cache::forget($prefix . ':all');
        Cache::forget($prefix . ':list');
    }
}
