<?php

namespace Webkul\PushNotification\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\PushNotification\Services\FirebasePushService;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $customerId,
        protected string $title,
        protected string $body,
        protected array $data = [],
    ) {}

    /**
     * Execute the job.
     */
    public function handle(FirebasePushService $pushService): void
    {
        $pushService->sendToCustomer($this->customerId, $this->title, $this->body, $this->data);
    }
}
