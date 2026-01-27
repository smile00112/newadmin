<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Models\IikoSyncLog;
use Webkul\IikoIntegration\Repositories\IikoPaymentTypeRepository;
use Webkul\IikoIntegration\Repositories\IikoSyncLogRepository;

class IikoPaymentTypeService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected IikoApiService $apiService,
        protected IikoPaymentTypeRepository $paymentTypeRepository,
        protected IikoSyncLogRepository $syncLogRepository
    ) {}

    /**
     * Get payment types from iiko API.
     */
    public function getPaymentTypes(string $organizationId, ?string $channelCode = null): ?array
    {
        try {
            $response = $this->apiService->getPaymentTypes($organizationId, $channelCode);

            return $response;
        } catch (\Exception $e) {
            Log::error('iiko: Exception getting payment types', [
                'organization_id' => $organizationId,
                'message'        => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sync payment types from iiko API.
     */
    public function syncPaymentTypes(string $organizationId, ?string $channelCode = null): bool
    {
        try {
            $response = $this->getPaymentTypes($organizationId, $channelCode);

            // Handle different response formats
            $paymentTypes = $response['paymentTypes'] ?? $response['items'] ?? $response ?? [];

            if (empty($paymentTypes) || !is_array($paymentTypes)) {
                Log::warning('iiko: No payment types data received', [
                    'organization_id' => $organizationId,
                    'response' => $response,
                ]);
                return false;
            }

            $syncedCount = 0;

            foreach ($paymentTypes as $paymentTypeData) {
                $iikoId = $paymentTypeData['id'] ?? null;

                if (!$iikoId) {
                    continue;
                }

                $this->paymentTypeRepository->createOrUpdate(
                    [
                        'name'                => $paymentTypeData['name'] ?? null,
                        'kind'                => $paymentTypeData['kind'] ?? null,
                        'is_active'           => $paymentTypeData['isDeleted'] ? false : true,
                        'payment_type_data'   => $paymentTypeData,
                        'synced_at'           => now(),
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
                    'action'         => 'sync_payment_types',
                ],
                'response_data' => ['synced_count' => $syncedCount],
            ]);

            Log::info('iiko: Payment types synced successfully', [
                'organization_id' => $organizationId,
                'count'          => $syncedCount,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('iiko: Exception syncing payment types', [
                'organization_id' => $organizationId,
                'message'        => $e->getMessage(),
            ]);

            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_ORGANIZATION,
                'entity_id'    => $organizationId,
                'status'       => IikoSyncLog::STATUS_ERROR,
                'request_data' => [
                    'organization_id' => $organizationId,
                    'action'         => 'sync_payment_types',
                ],
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get cached payment types from database.
     */
    public function getCachedPaymentTypes(string $organizationId): ?array
    {
        $paymentTypes = $this->paymentTypeRepository->getByOrganizationId($organizationId);

        if ($paymentTypes->isEmpty()) {
            return null;
        }

        return $paymentTypes->map(function ($paymentType) {
            return [
                'id'        => $paymentType->iiko_id,
                'name'      => $paymentType->name,
                'kind'      => $paymentType->kind,
                'is_active' => $paymentType->is_active,
            ];
        })->toArray();
    }
}
