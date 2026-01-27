<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Models\IikoSyncLog;
use Webkul\IikoIntegration\Repositories\IikoSyncLogRepository;
use Webkul\IikoIntegration\Repositories\IikoTerminalGroupRepository;

class IikoTerminalGroupService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected IikoApiService $apiService,
        protected IikoTerminalGroupRepository $terminalGroupRepository,
        protected IikoSyncLogRepository $syncLogRepository
    ) {}

    /**
     * Get terminal groups from iiko API.
     */
    public function getTerminalGroups(string $organizationId, ?string $channelCode = null): ?array
    {
        try {
            $response = $this->apiService->getTerminalGroups($organizationId, $channelCode);

            return $response;
        } catch (\Exception $e) {
            Log::error('iiko: Exception getting terminal groups', [
                'organization_id' => $organizationId,
                'message'        => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sync terminal groups from iiko API.
     */
    public function syncTerminalGroups(string $organizationId, ?string $channelCode = null): bool
    {
        try {
            $response = $this->getTerminalGroups($organizationId, $channelCode);

            if (!$response || !isset($response['terminalGroups'])) {
                Log::warning('iiko: No terminal groups data received', [
                    'organization_id' => $organizationId,
                ]);
                return false;
            }

            $syncedCount = 0;

            foreach ($response['terminalGroups'] as $item) {
                foreach ($item['items'] as $terminalData){
                    $iikoId = $terminalData['id'] ?? null;

                    if (!$iikoId) {
                        continue;
                    }

                    $this->terminalGroupRepository->createOrUpdate(
                        [
                            'name'                => $terminalData['name'] ?? null,
                            'terminal_group_data' => $terminalData,
                            'synced_at'           => now(),
                        ],
                        $organizationId,
                        $iikoId
                    );

                    $syncedCount++;
                }
            }

            // Log sync
            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_ORGANIZATION,
                'entity_id'    => $organizationId,
                'status'       => IikoSyncLog::STATUS_SUCCESS,
                'request_data' => [
                    'organization_id' => $organizationId,
                    'action'         => 'sync_terminal_groups',
                ],
                'response_data' => ['synced_count' => $syncedCount],
            ]);

            Log::info('iiko: Terminal groups synced successfully', [
                'organization_id' => $organizationId,
                'count'          => $syncedCount,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('iiko: Exception syncing terminal groups', [
                'organization_id' => $organizationId,
                'message'        => $e->getMessage(),
            ]);

            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_ORGANIZATION,
                'entity_id'    => $organizationId,
                'status'       => IikoSyncLog::STATUS_ERROR,
                'request_data' => [
                    'organization_id' => $organizationId,
                    'action'         => 'sync_terminal_groups',
                ],
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get cached terminal groups from database.
     */
    public function getCachedTerminalGroups(string $organizationId): ?array
    {
        $terminalGroups = $this->terminalGroupRepository->findByOrganizationId($organizationId);

        if ($terminalGroups->isEmpty()) {
            return null;
        }

        return $terminalGroups->map(function ($terminal) {
            return [
                'id'   => $terminal->iiko_id,
                'name' => $terminal->name,
            ];
        })->toArray();
    }
}
