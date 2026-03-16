 <?php

namespace Webkul\RestApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\RestApi\Services\CustomerOrdersCacheWarmer;

class WarmCustomerOrdersCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $customerId,
        protected string $channelCode
    ) {
    }

    /**
     * Execute the job: warm active and completed orders cache for the customer.
     */
    public function handle(CustomerOrdersCacheWarmer $warmer): void
    {
        $warmer->warm($this->customerId, $this->channelCode);
    }
}
