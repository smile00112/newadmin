<?php

namespace Webkul\PushNotification\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Webkul\PushNotification\Services\PushCampaignService;

class PushCampaignOpenController extends Controller
{
    public function __construct(
        protected PushCampaignService $campaignService
    ) {}

    /**
     * Track push notification open event.
     * Called by mobile app when user taps on a campaign push notification.
     *
     * POST /api/v1/push/campaign/{campaignId}/open
     */
    public function __invoke(int $campaignId): JsonResponse
    {
        $customer = auth('sanctum')->user();

        if (! $customer) {
            return new JsonResponse(['message' => 'Unauthenticated.'], 401);
        }

        $this->campaignService->recordOpen($campaignId, $customer->id);

        return new JsonResponse(['success' => true]);
    }
}
