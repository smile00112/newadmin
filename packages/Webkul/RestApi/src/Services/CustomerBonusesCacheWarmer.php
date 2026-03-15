<?php

namespace Webkul\RestApi\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Webkul\Bonus\Services\BonusService;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\RestApi\Http\Controllers\V1\Shop\Customer\BonusController;

class CustomerBonusesCacheWarmer
{
    /**
     * Warm cache for GET /api/v1/customer/bonuses for the given customer.
     */
    public function warm(int $customerId): void
    {
        try {
            $customer = CustomerProxy::find($customerId);

            if (! $customer) {
                return;
            }

            if (! app(BonusService::class)->isEnabled()) {
                return;
            }

            $controller = app(BonusController::class);

            $cacheKey = CustomerBonusesCache::key($customerId);
            $data = $controller->buildBonusData($customer);

            Cache::put($cacheKey, $data, CustomerBonusesCache::ttl());
        } catch (\Throwable $e) {
            Log::warning('RestApi: failed to warm customer bonuses cache', [
                'customer_id' => $customerId,
                'message'     => $e->getMessage(),
            ]);
        }
    }
}
