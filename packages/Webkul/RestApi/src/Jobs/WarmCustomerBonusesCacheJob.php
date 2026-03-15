<?php

namespace Webkul\RestApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\RestApi\Services\CustomerBonusesCacheWarmer;

class WarmCustomerBonusesCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $customerId
    ) {
    }

    /**
     * Execute the job: warm bonuses cache for the customer.
     */
    public function handle(CustomerBonusesCacheWarmer $warmer): void
    {
        $warmer->warm($this->customerId);
    }
}
