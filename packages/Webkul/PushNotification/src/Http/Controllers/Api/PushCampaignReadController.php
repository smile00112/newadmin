<?php

namespace Webkul\PushNotification\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\PushNotification\Services\PushCampaignService;

class PushCampaignReadController extends Controller
{
    public function __construct(
        protected PushCampaignService $campaignService
    ) {}

    /**
     * Track push notification read/unread state.
     *
     * POST /api/v1/push/campaign/{campaignId}/read
     * Body: { "is_read": true|false }
     */
    public function __invoke(Request $request, int $campaignId): JsonResponse
    {
        $customer = auth('sanctum')->user();

        if (! $customer) {
            return new JsonResponse(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'is_read' => 'required|boolean',
        ]);

        $isRead = filter_var($validated['is_read'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $this->campaignService->recordReadStatus($campaignId, $customer->id, (bool) $isRead);

        return new JsonResponse([
            'success' => true,
            'campaign_id' => $campaignId,
            'is_read' => (bool) $isRead,
        ]);
    }
}