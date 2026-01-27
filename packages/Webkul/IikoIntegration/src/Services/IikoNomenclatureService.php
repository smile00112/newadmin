<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Models\IikoSyncLog;
use Webkul\IikoIntegration\Repositories\IikoNomenclatureRepository;
use Webkul\IikoIntegration\Repositories\IikoSyncLogRepository;

class IikoNomenclatureService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected IikoApiService $apiService,
        protected IikoNomenclatureRepository $nomenclatureRepository,
        protected IikoSyncLogRepository $syncLogRepository
    ) {}

    /**
     * Get nomenclature from iiko API.
     */
    public function getNomenclature(string $organizationId, ?string $channelCode = null, ?string $externalMenuId = null): ?array
    {
        try {
            $response = $this->apiService->getNomenclature($organizationId, $channelCode, $externalMenuId);

            return $response;
        } catch (\Exception $e) {
            Log::error('iiko: Exception getting nomenclature', [
                'organization_id' => $organizationId,
                'external_menu_id' => $externalMenuId,
                'message'        => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sync nomenclature from iiko API.
     */
    public function syncNomenclature(string $organizationId, ?string $channelCode = null, ?string $externalMenuId = null): bool
    {
        try {
            $nomenclatureData = $this->getNomenclature($organizationId, $channelCode, $externalMenuId);

            if (!$nomenclatureData) {
                Log::warning('iiko: No nomenclature data received', [
                    'organization_id' => $organizationId,
                    'external_menu_id' => $externalMenuId,
                ]);
                return false;
            }

            // Save nomenclature to database
            $this->nomenclatureRepository->createOrUpdate(
                [
                    'nomenclature_data' => $nomenclatureData,
                    'synced_at'         => now(),
                ],
                $organizationId
            );

            // Log sync
            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_ORGANIZATION,
                'entity_id'    => $organizationId,
                'status'       => IikoSyncLog::STATUS_SUCCESS,
                'request_data' => [
                    'organization_id' => $organizationId,
                    'action'         => 'sync_nomenclature',
                ],
                'response_data' => ['synced' => true],
            ]);

            Log::info('iiko: Nomenclature synced successfully', [
                'organization_id' => $organizationId,
                'external_menu_id' => $externalMenuId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('iiko: Exception syncing nomenclature', [
                'organization_id' => $organizationId,
                'external_menu_id' => $externalMenuId,
                'message'        => $e->getMessage(),
            ]);

            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_ORGANIZATION,
                'entity_id'    => $organizationId,
                'status'       => IikoSyncLog::STATUS_ERROR,
                'request_data' => [
                    'organization_id' => $organizationId,
                    'action'         => 'sync_nomenclature',
                ],
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get cached nomenclature from database.
     */
    public function getCachedNomenclature(string $organizationId): ?array
    {
        $nomenclature = $this->nomenclatureRepository->findByOrganizationId($organizationId);

        return $nomenclature ? $nomenclature->nomenclature_data : null;
    }
}
