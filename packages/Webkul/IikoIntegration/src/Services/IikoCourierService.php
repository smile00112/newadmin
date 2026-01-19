<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\Log;

class IikoCourierService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected IikoApiService $apiService
    ) {}

    /**
     * Get couriers for organization.
     */
    public function getCouriers(string $organizationId, ?string $channelCode = null): ?array
    {
        try {
            $response = $this->apiService->makeRequest(
                '/api/1/couriers',
                'POST',
                ['organizationId' => $organizationId],
                $channelCode
            );

            return $response;
        } catch (\Exception $e) {
            Log::error('iiko: Exception getting couriers', [
                'organization_id' => $organizationId,
                'message'        => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get courier info by ID.
     */
    public function getCourierInfo(string $courierId, string $organizationId, ?string $channelCode = null): ?array
    {
        try {
            $couriers = $this->getCouriers($organizationId, $channelCode);

            if (!$couriers) {
                return null;
            }

            // Find courier by ID in the list
            foreach ($couriers as $courier) {
                if (($courier['id'] ?? null) === $courierId) {
                    return $courier;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('iiko: Exception getting courier info', [
                'courier_id'      => $courierId,
                'organization_id' => $organizationId,
                'message'        => $e->getMessage(),
            ]);

            return null;
        }
    }
}
