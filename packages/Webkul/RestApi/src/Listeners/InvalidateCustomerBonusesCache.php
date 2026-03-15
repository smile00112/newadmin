<?php

namespace Webkul\RestApi\Listeners;

use Webkul\RestApi\Jobs\WarmCustomerBonusesCacheJob;
use Webkul\RestApi\Services\CustomerBonusesCache;

class InvalidateCustomerBonusesCache
{
    /**
     * Handle bonus.balance.changed - bonus balance changed for a customer.
     *
     * @param  int  $customerId
     * @return void
     */
    public function onBalanceChanged(int $customerId): void
    {
        CustomerBonusesCache::invalidate($customerId);
        $this->dispatchWarmJob($customerId);
    }

    /**
     * Dispatch job to warm customer bonuses cache via queue.
     */
    protected function dispatchWarmJob(int $customerId): void
    {
        WarmCustomerBonusesCacheJob::dispatch($customerId)
            ->delay(now()->addSeconds(2));
    }
}
