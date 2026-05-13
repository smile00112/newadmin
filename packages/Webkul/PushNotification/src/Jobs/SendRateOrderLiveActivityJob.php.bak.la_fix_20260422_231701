<?php

namespace Webkul\PushNotification\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webkul\PushNotification\Models\OrderLiveActivityToken;
use Webkul\PushNotification\Services\ApnsLiveActivityService;
use Webkul\Sales\Models\Order;

class SendRateOrderLiveActivityJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $orderId,
        protected int $tokenRecordId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ApnsLiveActivityService $apnsService): void
    {
        $order = Order::find($this->orderId);

        if (! $order) {
            Log::warning('SendRateOrderLiveActivityJob: order not found', ['order_id' => $this->orderId]);

            return;
        }

        $tokenRecord = OrderLiveActivityToken::find($this->tokenRecordId);

        if (! $tokenRecord) {
            Log::debug('SendRateOrderLiveActivityJob: token record not found (order likely completed/closed)', [
                'order_id'        => $this->orderId,
                'token_record_id' => $this->tokenRecordId,
            ]);

            return;
        }

        $apnsService->sendCustomStatus($tokenRecord, $order, 'rateOrder', 'Как всё прошло');
    }
}
