<?php

namespace Webkul\PushNotification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webkul\PushNotification\Models\PushCampaign;
use Webkul\PushNotification\Models\PushCampaignLog;
use Webkul\PushNotification\Services\PushCampaignService;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 3600;

    public function __construct(
        protected int $campaignId
    ) {}

    public function handle(PushCampaignService $campaignService): void
    {
        $campaign = PushCampaign::find($this->campaignId);

        if (! $campaign || $campaign->status === 'sent') {
            return;
        }

        Log::info("SendCampaignJob: starting campaign #{$this->campaignId}");

        try {
            PushCampaignLog::where('campaign_id', $this->campaignId)
                ->where('status', 'pending')
                ->chunkById(100, function ($logs) use ($campaignService) {
                    foreach ($logs as $log) {
                        $campaignService->sendLogEntry($log);
                        // Small delay to avoid FCM rate limits
                        usleep(10000); // 10ms
                    }
                });

            $campaignService->finalizeCampaign($this->campaignId);

            Log::info("SendCampaignJob: finished campaign #{$this->campaignId}");
        } catch (\Exception $e) {
            Log::error("SendCampaignJob: error for campaign #{$this->campaignId}", [
                'error' => $e->getMessage(),
            ]);

            $campaign->update(['status' => 'failed']);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendCampaignJob failed for campaign #{$this->campaignId}", [
            'error' => $exception->getMessage(),
        ]);

        PushCampaign::where('id', $this->campaignId)->update(['status' => 'failed']);
    }
}
