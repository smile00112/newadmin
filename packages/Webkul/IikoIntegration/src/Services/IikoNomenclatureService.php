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
    public function getNomenclature(array $organizationIds, string $externalMenuId, ?string $channelCode = null): ?array
    {
        try {
            $response = $this->apiService->getNomenclature($organizationIds, $externalMenuId, $channelCode);

            if ($response) {
                // Normalize new API structure to old format for compatibility
                $response = $this->normalizeNomenclatureData($response);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('iiko: Exception getting nomenclature', [
                'organization_ids' => $organizationIds,
                'external_menu_id' => $externalMenuId,
                'message'        => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Normalize nomenclature data from new API format to old format.
     * New API uses itemCategories with nested items, old format uses flat groups and items.
     *
     * @param  array  $data
     * @return array
     */
    protected function normalizeNomenclatureData(array $data): array
    {
        // If data already has 'groups' or 'items', assume it's already in old format
        if (isset($data['groups']) || isset($data['items'])) {
            return $data;
        }

        // New API format: itemCategories contains categories with nested items
        if (!isset($data['itemCategories']) || !is_array($data['itemCategories'])) {
            return $data;
        }

        $normalized = $data;
        $groups = [];
        $items = [];

        // Extract categories and items from itemCategories
        foreach ($data['itemCategories'] as $category) {
            $categoryId = $category['id'] ?? null;
            if (!$categoryId) {
                continue;
            }

            // Add category to groups
            $groups[] = [
                'id' => $categoryId,
                'name' => $category['name'] ?? 'Unnamed Category',
                'description' => $category['description'] ?? null,
                'parentGroup' => null, // New API doesn't have parentGroup in itemCategories
            ];

            // Extract items from category and add groupId
            if (isset($category['items']) && is_array($category['items'])) {
                foreach ($category['items'] as $item) {
                    $itemId = $item['itemId'] ?? $item['id'] ?? null;
                    if (!$itemId) {
                        continue;
                    }

                    // Handle itemSizes - convert to sizePrices format for compatibility
                    $item['id'] = $itemId;
                    $item['groupId'] = $categoryId;
                    
                    // Convert itemSizes to sizePrices format if present
                    if (isset($item['itemSizes']) && is_array($item['itemSizes']) && count($item['itemSizes']) > 0) {
                        $sizePrices = [];
                        $itemModifierGroupsMerged = [];
                        foreach ($item['itemSizes'] as $size) {
                            $sizePrice = 0;
                            if (isset($size['prices']) && is_array($size['prices']) && count($size['prices']) > 0) {
                                $sizePrice = $size['prices'][0]['price'] ?? 0;
                            }
                            $sizePrices[] = [
                                'sizeId' => $size['sizeId'] ?? null,
                                'sizeName' => $size['sizeName'] ?? null,
                                'sizeCode' => $size['sizeCode'] ?? null,
                                'price' => $sizePrice,
                            ];
                            // Preserve itemModifierGroups from itemSizes for constructor product import
                            if (isset($size['itemModifierGroups']) && is_array($size['itemModifierGroups']) && count($size['itemModifierGroups']) > 0) {
                                foreach ($size['itemModifierGroups'] as $modGroup) {
                                    $groupName = $modGroup['name'] ?? 'Unnamed Group';
                                    if (!isset($itemModifierGroupsMerged[$groupName])) {
                                        $itemModifierGroupsMerged[$groupName] = $modGroup;
                                    }
                                }
                            }
                        }
                        // Store sizePrices for compatibility with existing import logic
                        $item['sizePrices'] = $sizePrices;
                        
                        // Store itemModifierGroups for constructor product import (from first size or merged)
                        if (!empty($itemModifierGroupsMerged)) {
                            $item['itemModifierGroups'] = array_values($itemModifierGroupsMerged);
                        }
                        
                        // Set price from first size for single-price fallback
                        if (count($sizePrices) > 0) {
                            $item['price'] = $sizePrices[0]['price'] ?? 0;
                        }
                    }

                    $items[] = $item;
                }
            }
        }

        // Add normalized groups and items to response
        $normalized['groups'] = $groups;
        $normalized['items'] = $items;

        return $normalized;
    }

    /**
     * Sync nomenclature from iiko API.
     */
    public function syncNomenclature(array $organizationIds, string $externalMenuId, ?string $channelCode = null): bool
    {
        try {
            $nomenclatureData = $this->getNomenclature($organizationIds, $externalMenuId, $channelCode);

            if (!$nomenclatureData) {
                Log::warning('iiko: No nomenclature data received', [
                    'organization_ids' => $organizationIds,
                    'external_menu_id' => $externalMenuId,
                ]);
                return false;
            }

            // Use first organization ID for database storage (repository expects single ID)
            $organizationId = $organizationIds[0] ?? null;
            if (!$organizationId) {
                Log::error('iiko: Empty organizationIds array');
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
                    'organization_ids' => $organizationIds,
                    'action'         => 'sync_nomenclature',
                ],
                'response_data' => ['synced' => true],
            ]);

            Log::info('iiko: Nomenclature synced successfully', [
                'organization_ids' => $organizationIds,
                'external_menu_id' => $externalMenuId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('iiko: Exception syncing nomenclature', [
                'organization_ids' => $organizationIds,
                'external_menu_id' => $externalMenuId,
                'message'        => $e->getMessage(),
            ]);

            $organizationId = $organizationIds[0] ?? null;
            $this->syncLogRepository->create([
                'sync_type'    => IikoSyncLog::TYPE_ORGANIZATION,
                'entity_id'    => $organizationId,
                'status'       => IikoSyncLog::STATUS_ERROR,
                'request_data' => [
                    'organization_ids' => $organizationIds,
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
