<?php

namespace Webkul\RestApi\Services;

use Illuminate\Support\Facades\Cache;

class CustomerBonusesCache
{
    /**
     * Cache TTL in seconds (5 minutes).
     */
    protected const TTL = 300;

    /**
     * Build cache key for customer bonuses (GET /api/v1/customer/bonuses).
     */
    public static function key(int $customerId): string
    {
        $version = Cache::get(self::versionKey($customerId), 0);

        return sprintf('rest_api:customer_bonuses:%d:v%d', $customerId, $version);
    }

    /**
     * Get version cache key for customer.
     */
    protected static function versionKey(int $customerId): string
    {
        return "rest_api:customer_bonuses_version:{$customerId}";
    }

    /**
     * Invalidate cache for customer (bump version).
     */
    public static function invalidate(int $customerId): void
    {
        $key = self::versionKey($customerId);
        $version = (int) Cache::get($key, 0);

        Cache::put($key, $version + 1, now()->addYear());
    }

    /**
     * Get cache TTL.
     */
    public static function ttl(): int
    {
        return self::TTL;
    }
}
