<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Models\IikoMenuSync;
use Webkul\IikoIntegration\Models\IikoSyncLog;
use Webkul\IikoIntegration\Repositories\IikoMenuSyncRepository;
use Webkul\IikoIntegration\Repositories\IikoSyncLogRepository;

class IikoMenuService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected IikoApiService $apiService,
        protected IikoMenuSyncRepository $menuSyncRepository,
        protected IikoSyncLogRepository $syncLogRepository
    ) {}

    /**
     * Get menu from iiko API.
     */
    public function getMenu(string $organizationId, ?string $externalMenuId = null, ?string $channelCode = null): ?array
    {
        try {
            $endpoint = '/api/1/menu';
            $data = ['organizationId' => $organizationId];

            if ($externalMenuId) {
                $endpoint = "/api/1/menu/{$externalMenuId}";
            }

            $response = $this->apiService->makeRequest(
                $endpoint,
                'POST',
                $data,
                $channelCode
            );

            return $response;
        } catch (\Exception $e) {
            Log::error('iiko: Exception getting menu', [
                'organization_id' => $organizationId,
                'external_menu_id' => $externalMenuId,
                'message'        => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sync menu from iiko API.
     */
    public function syncMenu(string $organizationId, ?string $externalMenuId = null, ?string $channelCode = null): bool
    {
        try {
            $menuData = $this->getMenu($organizationId, $externalMenuId, $channelCode);

            if (!$menuData) {
                Log::warning('iiko: No menu data received', [
                    'organization_id' => $organizationId,
                    'external_menu_id' => $externalMenuId,
                ]);
                return false;
            }

            // Save menu to database
            $this->menuSyncRepository->updateOrCreate(
                [
                    'organization_id'  => $organizationId,
                    'external_menu_id' => $externalMenuId,
                ],
                [
                    'menu_data' => $menuData,
                    'synced_at' => now(),
                ]
            );

            // Log sync
            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_MENU,
                'entity_id'    => $organizationId,
                'status'       => IikoSyncLog::STATUS_SUCCESS,
                'request_data' => [
                    'organization_id'  => $organizationId,
                    'external_menu_id' => $externalMenuId,
                ],
                'response_data' => ['synced' => true],
            ]);

            Log::info('iiko: Menu synced successfully', [
                'organization_id'  => $organizationId,
                'external_menu_id' => $externalMenuId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('iiko: Exception syncing menu', [
                'organization_id'  => $organizationId,
                'external_menu_id' => $externalMenuId,
                'message'          => $e->getMessage(),
            ]);

            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_MENU,
                'entity_id'    => $organizationId,
                'status'       => IikoSyncLog::STATUS_ERROR,
                'request_data' => [
                    'organization_id'  => $organizationId,
                    'external_menu_id' => $externalMenuId,
                ],
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get cached menu from database.
     */
    public function getCachedMenu(string $organizationId, ?string $externalMenuId = null): ?array
    {
        $menuSync = $this->menuSyncRepository->findWhere([
            'organization_id'  => $organizationId,
            'external_menu_id' => $externalMenuId,
        ])->first();

        return $menuSync ? $menuSync->menu_data : null;
    }
}
