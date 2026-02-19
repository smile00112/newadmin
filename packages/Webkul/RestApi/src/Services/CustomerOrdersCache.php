<?php

namespace Webkul\RestApi\Services;

use Illuminate\Support\Facades\Cache;

class CustomerOrdersCache
{
    /**
     * Cache TTL in seconds (5 minutes).
     */
    protected const TTL = 300;

    /**
     * Build cache key for customer orders list.
     */
    public static function key(int $customerId, array $params = []): string
    {
        $version = Cache::get(self::versionKey($customerId), 0);

        $paramsHash = md5(serialize($params));

        return sprintf(
            'rest_api:customer_orders:%d:v%d:%s',
            $customerId,
            $version,
            $paramsHash
        );
    }

    /**
     * Get version cache key for customer.
     */
    protected static function versionKey(int $customerId): string
    {
        return "rest_api:customer_orders_version:{$customerId}";
    }

    /**
     * Invalidate cache for customer (bump version so all cached entries become stale).
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
