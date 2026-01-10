<?php

namespace Webkul\Newsletters\Listeners;

use Illuminate\Support\Facades\Cache;
use Webkul\Newsletters\Events\ContactCacheInvalidated;

class ClearContactFilterCache
{
    /**
     * Handle the event.
     *
     * @param ContactCacheInvalidated $event
     * @return void
     */
    public function handle(ContactCacheInvalidated $event): void
    {
        $groupId = $event->contactGroupId;
        
        // Try to use cache tags if available (Redis, Memcached)
        if (method_exists(Cache::getStore(), 'tags')) {
            try {
                Cache::tags(["contact_filter_count_{$groupId}"])->flush();
            } catch (\Exception $e) {
                // If tags are not supported, fall back to pattern matching
                $this->clearCacheByPattern($groupId);
            }
        } else {
            // For file cache and other drivers that don't support tags
            $this->clearCacheByPattern($groupId);
        }
    }

    /**
     * Clear cache by pattern matching.
     *
     * @param int $groupId
     * @return void
     */
    protected function clearCacheByPattern(int $groupId): void
    {
        // Note: This method works only with Redis or Memcached
        // For file cache, we can't easily find keys by pattern
        // In that case, cache will expire naturally after 1 hour
        
        $cacheStore = Cache::getStore();
        
        // If using Redis
        if (method_exists($cacheStore, 'connection') && method_exists($cacheStore->connection(), 'keys')) {
            $pattern = "contact_filter_count_{$groupId}_*";
            $keys = $cacheStore->connection()->keys($pattern);
            
            if (!empty($keys)) {
                foreach ($keys as $key) {
                    // Remove prefix if Redis adds one
                    $key = str_replace(config('cache.prefix', '') . ':', '', $key);
                    Cache::forget($key);
                }
            }
        }
    }
}




