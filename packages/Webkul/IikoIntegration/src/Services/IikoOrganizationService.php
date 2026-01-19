<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Models\IikoSyncLog;
use Webkul\IikoIntegration\Repositories\IikoOrganizationRepository;
use Webkul\IikoIntegration\Repositories\IikoSyncLogRepository;

class IikoOrganizationService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected IikoApiService $apiService,
        protected IikoOrganizationRepository $organizationRepository,
        protected IikoSyncLogRepository $syncLogRepository
    ) {}

    /**
     * Get organizations from iiko API.
     */
    public function getOrganizations(?string $channelCode = null): ?array
    {
        try {
            $response = $this->apiService->makeRequest(
                '/api/1/organizations',
                'GET',
                [],
                $channelCode
            );

            if ($response && isset($response['organizations'])) {
                return $response['organizations'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('iiko: Exception getting organizations', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sync organizations from iiko API.
     */
    public function syncOrganizations(?string $channelCode = null): bool
    {
        try {
            $organizations = $this->getOrganizations($channelCode);

            if (!$organizations) {
                Log::warning('iiko: No organizations received from API');
                return false;
            }

            $syncedCount = 0;

            foreach ($organizations as $orgData) {
                $iikoId = $orgData['id'] ?? null;

                if (!$iikoId) {
                    continue;
                }

                $this->organizationRepository->createOrUpdate([
                    'iiko_id'          => $iikoId,
                    'name'             => $orgData['name'] ?? null,
                    'organization_data' => $orgData,
                    'synced_at'        => now(),
                ], $iikoId);

                $syncedCount++;
            }

            // Log sync
            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_ORGANIZATION,
                'entity_id'    => null,
                'status'       => IikoSyncLog::STATUS_SUCCESS,
                'request_data' => ['action' => 'sync_organizations'],
                'response_data' => ['synced_count' => $syncedCount],
            ]);

            Log::info('iiko: Organizations synced successfully', ['count' => $syncedCount]);

            return true;
        } catch (\Exception $e) {
            Log::error('iiko: Exception syncing organizations', [
                'message' => $e->getMessage(),
            ]);

            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_ORGANIZATION,
                'entity_id'    => null,
                'status'       => IikoSyncLog::STATUS_ERROR,
                'request_data' => ['action' => 'sync_organizations'],
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get organization settings.
     */
    public function getOrganizationSettings(string $organizationId, ?string $channelCode = null): ?array
    {
        try {
            $response = $this->apiService->makeRequest(
                '/api/1/organizations/settings',
                'POST',
                ['organizationId' => $organizationId],
                $channelCode
            );

            return $response;
        } catch (\Exception $e) {
            Log::error('iiko: Exception getting organization settings', [
                'organization_id' => $organizationId,
                'message'        => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Find organization by iiko ID.
     */
    public function findByIikoId(string $iikoId): ?\Webkul\IikoIntegration\Models\IikoOrganization
    {
        return $this->organizationRepository->findByIikoId($iikoId);
    }
}
