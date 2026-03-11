<?php

namespace Webkul\FPC\Listeners;

use Webkul\FPC\Jobs\InvalidateOrderProductCacheJob;

class Order extends Product
{
    /**
     * After order is created or cancelled — dispatch cache invalidation to queue.
     *
     * @param  \Webkul\Sale\Contracts\Order  $order
     * @return void
     */
    public function afterCancelOrCreate($order)
    {
        InvalidateOrderProductCacheJob::dispatch($order->id);
    }
}
