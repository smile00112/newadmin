<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Models\IikoSyncLog;
use Webkul\IikoIntegration\Repositories\IikoPromotionRepository;
use Webkul\IikoIntegration\Repositories\IikoSyncLogRepository;

class IikoPromotionService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected IikoApiService $apiService,
        protected IikoPromotionRepository $promotionRepository,
        protected IikoSyncLogRepository $syncLogRepository
    ) {}

    /**
     * Get promotions from iiko API.
     */
    public function getPromotions(string $organizationId, ?string $channelCode = null): ?array
    {
        try {
            $response = $this->apiService->getDiscounts($organizationId, $channelCode);

            return $response;
        } catch (\Exception $e) {
            Log::error('iiko: Exception getting promotions', [
                'organization_id' => $organizationId,
                'message'        => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sync promotions from iiko API.
     */
    public function syncPromotions(string $organizationId, ?string $channelCode = null): bool
    {
        try {
            $response = $this->getPromotions($organizationId, $channelCode);

            // Handle different response formats
            $discounts = $response['discounts'] ?? $response['items'] ?? $response ?? [];

            if (empty($discounts) || !is_array($discounts)) {
                Log::warning('iiko: No promotions data received', [
                    'organization_id' => $organizationId,
                    'response' => $response,
                ]);
                return false;
            }

            $syncedCount = 0;

            foreach ($discounts as $promotionData) {
                $iikoId = $promotionData['id'] ?? null;

                if (!$iikoId) {
                    continue;
                }

                $this->promotionRepository->createOrUpdate(
                    [
                        'name'            => $promotionData['name'] ?? null,
                        'description'     => $promotionData['description'] ?? null,
                        'is_active'       => $promotionData['isActive'] ?? true,
                        'promotion_data'  => $promotionData,
                        'synced_at'       => now(),
                    ],
                    $organizationId,
                    $iikoId
                );

                $syncedCount++;
            }

            // Log sync
            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_ORGANIZATION,
                'entity_id'    => $organizationId,
                'status'       => IikoSyncLog::STATUS_SUCCESS,
                'request_data' => [
                    'organization_id' => $organizationId,
                    'action'         => 'sync_promotions',
                ],
                'response_data' => ['synced_count' => $syncedCount],
            ]);

            Log::info('iiko: Promotions synced successfully', [
                'organization_id' => $organizationId,
                'count'          => $syncedCount,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('iiko: Exception syncing promotions', [
                'organization_id' => $organizationId,
                'message'        => $e->getMessage(),
            ]);

            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_ORGANIZATION,
                'entity_id'    => $organizationId,
                'status'       => IikoSyncLog::STATUS_ERROR,
                'request_data' => [
                    'organization_id' => $organizationId,
                    'action'         => 'sync_promotions',
                ],
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get cached promotions from database.
     */
    public function getCachedPromotions(string $organizationId): ?array
    {
        $promotions = $this->promotionRepository->getByOrganizationId($organizationId);

        if ($promotions->isEmpty()) {
            return null;
        }

        return $promotions->map(function ($promotion) {
            return [
                'id'          => $promotion->iiko_id,
                'name'        => $promotion->name,
                'description' => $promotion->description,
                'is_active'   => $promotion->is_active,
            ];
        })->toArray();
    }
}
