<?php

namespace Webkul\IikoIntegration\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\IikoIntegration\Repositories\IikoOrganizationRepository;
use Webkul\IikoIntegration\Repositories\IikoTerminalGroupRepository;
use Webkul\IikoIntegration\Services\IikoApiService;
use Webkul\IikoIntegration\Services\IikoMenuService;
use Webkul\IikoIntegration\Services\IikoNomenclatureService;
use Webkul\IikoIntegration\Services\IikoOrganizationService;
use Webkul\IikoIntegration\Services\IikoTerminalGroupService;
use Webkul\IikoIntegration\Services\IikoPromotionService;
use Webkul\IikoIntegration\Services\IikoPaymentTypeService;
use Webkul\Inventory\Repositories\InventorySourceRepository;
use Webkul\Inventory\Repositories\PickupPointRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Jobs\ImportNomenclatureJob;

class IikoManagementController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected IikoOrganizationService $organizationService,
        protected IikoApiService $apiService,
        protected IikoMenuService $menuService,
        protected IikoTerminalGroupService $terminalGroupService,
        protected IikoNomenclatureService $nomenclatureService,
        protected IikoOrganizationRepository $organizationRepository,
        protected IikoPromotionService $promotionService,
        protected IikoPaymentTypeService $paymentTypeService,
        protected InventorySourceRepository $inventorySourceRepository,
        protected PickupPointRepository $pickupPointRepository,
        protected IikoTerminalGroupRepository $terminalGroupRepository
    ) {}

    /**
     * Display the management page.
     */
    public function index(): View
    {
        // Load saved organizations from database
        $savedOrganizations = $this->organizationRepository->all()->map(function ($org) {
            return [
                'id'   => $org->iiko_id,
                'name' => $org->name,
            ];
        })->toArray();

        return view('iiko-integration::admin.iiko.management', [
            'savedOrganizations' => $savedOrganizations,
        ]);
    }

    /**
     * Get organizations from iiko API.
     */
    public function getOrganizations(): JsonResponse
    {
        try {
            // Sync organizations to database
            $syncSuccess = $this->organizationService->syncOrganizations();

            if (!$syncSuccess) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.error'),
                ], 400);
            }

            // Get synced organizations from database
            $savedOrganizations = $this->organizationRepository->all()->map(function ($org) {
                return [
                    'id'   => $org->iiko_id,
                    'name' => $org->name,
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'data'    => $savedOrganizations,
                'message' => trans('iiko-integration::app.management.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.management.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get terminal groups for organization.
     */
    public function getTerminals(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->input('organization_id');
            $forceRefresh = $request->input('force_refresh', false);

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.organization-id-required'),
                ], 400);
            }

            // Check cached terminal groups first (unless force refresh)
            if (!$forceRefresh) {
                $cachedTerminals = $this->terminalGroupService->getCachedTerminalGroups($organizationId);

                if ($cachedTerminals !== null && count($cachedTerminals) > 0) {
                    return response()->json([
                        'success' => true,
                        'data'    => $cachedTerminals,
                        'message' => trans('iiko-integration::app.management.success'),
                        'cached'  => true,
                    ]);
                }
            }

            // Sync from API if no cached data or force refresh
            $syncSuccess = $this->terminalGroupService->syncTerminalGroups($organizationId);

            if (!$syncSuccess) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.no-data'),
                ], 400);
            }

            // Get synced terminals
            $syncedTerminals = $this->terminalGroupService->getCachedTerminalGroups($organizationId);

            return response()->json([
                'success' => true,
                'data'    => $syncedTerminals ?? [],
                'message' => trans('iiko-integration::app.management.success'),
                'cached'  => false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.management.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get menu for organization.
     */
    public function getMenu(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->input('organization_id');

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.organization-id-required'),
                ], 400);
            }

            $menu = $this->menuService->getMenu($organizationId);

            if ($menu === null) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.no-data'),
                ], 400);
            }

            // Format menu data for select dropdown
            $menus = [];
            if (isset($menu['externalMenus']) && is_array($menu['externalMenus'])) {
                // If response contains 'menus' array
                foreach ($menu['externalMenus'] as $menuItem) {
                    $menus[] = [
                        'id'   => $menuItem['id'] ?? $menuItem['externalId'] ?? '',
                        'name' => $menuItem['name'] ?? '',
                    ];
                }
            } elseif (isset($menu['id']) || isset($menu['externalId'])) {
                // If response is a single menu object
                $menus[] = [
                    'id'   => $menu['id'] ?? $menu['externalId'] ?? '',
                    'name' => $menu['name'] ?? '',
                ];
            } elseif (is_array($menu) && isset($menu[0])) {
                // If response is an array of menus
                foreach ($menu as $menuItem) {
                    $menus[] = [
                        'id'   => $menuItem['id'] ?? $menuItem['externalId'] ?? '',
                        'name' => $menuItem['name'] ?? '',
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data'    => $menus,
                'raw'     => $menu, // Keep raw data for debugging
                'message' => trans('iiko-integration::app.management.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.management.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get nomenclature for organization.
     */
    public function getNomenclature(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->input('organization_id');
            $externalMenuId = $request->input('external_menu_id');

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.organization-id-required'),
                ], 400);
            }

            if (!$externalMenuId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.external-menu-id-required'),
                ], 400);
            }

            // Convert organizationId to array for API request
            $organizationIds = [$organizationId];

            // Sync nomenclature from API and save
            $syncSuccess = $this->nomenclatureService->syncNomenclature($organizationIds, $externalMenuId, null);

            if (!$syncSuccess) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.no-data'),
                ], 400);
            }

            // Get synced nomenclature
            $nomenclature = $this->nomenclatureService->getCachedNomenclature($organizationId);

            // Extract groups from nomenclature data
            // Support both old format (groups) and new format (itemCategories)
            $groups = [];
            if ($nomenclature) {
                // Check for normalized groups (from new API format)
                if (isset($nomenclature['groups']) && is_array($nomenclature['groups'])) {
                    foreach ($nomenclature['groups'] as $group) {
                        $groups[] = [
                            'id'         => $group['id'] ?? null,
                            'name'       => $group['name'] ?? 'Unnamed Group',
                            'parentGroup' => $group['parentGroup'] ?? null,
                        ];
                    }
                }
                // Fallback to itemCategories (new API format before normalization)
                elseif (isset($nomenclature['itemCategories']) && is_array($nomenclature['itemCategories'])) {
                    foreach ($nomenclature['itemCategories'] as $category) {
                        $groups[] = [
                            'id'         => $category['id'] ?? null,
                            'name'       => $category['name'] ?? 'Unnamed Category',
                            'parentGroup' => null,
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data'    => $nomenclature,
                'groups'  => $groups,
                'message' => trans('iiko-integration::app.management.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.management.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import nomenclature data (categories and products) from iiko.
     */
    public function importNomenclature(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->input('organization_id');
            $groupIds = $request->input('group_ids', []);

            Log::debug('iiko[import-nomenclature]: STEP 1 — request received', [
                'organization_id' => $organizationId,
                'group_ids_count' => count((array) $groupIds),
                'group_ids' => $groupIds,
            ]);

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.organization-id-required'),
                ], 400);
            }

            // Check if nomenclature exists
            Log::debug('iiko[import-nomenclature]: STEP 2 — loading cached nomenclature', [
                'organization_id' => $organizationId,
            ]);
            $nomenclature = $this->nomenclatureService->getCachedNomenclature($organizationId);
            Log::debug('iiko[import-nomenclature]: STEP 3 — cached nomenclature loaded', [
                'is_null' => is_null($nomenclature),
                'keys' => is_array($nomenclature) ? array_keys($nomenclature) : null,
                'groups_count' => is_array($nomenclature['groups'] ?? null) ? count($nomenclature['groups']) : 0,
                'items_count' => is_array($nomenclature['items'] ?? null) ? count($nomenclature['items']) : 0,
            ]);

            if (!$nomenclature) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.no-nomenclature-data'),
                ], 400);
            }

            // Validate group_ids if provided
            Log::debug('iiko[import-nomenclature]: STEP 4 — validating group_ids', [
                'group_ids' => $groupIds,
            ]);
            if (!empty($groupIds) && is_array($groupIds)) {
                $availableGroupIds = [];
                if (isset($nomenclature['groups']) && is_array($nomenclature['groups'])) {
                    foreach ($nomenclature['groups'] as $group) {
                        if (isset($group['id'])) {
                            $availableGroupIds[] = $group['id'];
                        }
                    }
                }

                // Filter to only include valid group IDs
                $groupIds = array_intersect($groupIds, $availableGroupIds);
                Log::debug('iiko[import-nomenclature]: STEP 5 — group_ids after intersect', [
                    'available_count' => count($availableGroupIds),
                    'filtered_count' => count($groupIds),
                    'filtered_group_ids' => array_values($groupIds),
                ]);

                if (empty($groupIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => trans('iiko-integration::app.management.groups-required'),
                    ], 400);
                }
            }

            // Dispatch import job — runs async to avoid HTTP timeout
            $statusKey = 'iiko_import_' . md5($organizationId . microtime());
            Log::debug('iiko[import-nomenclature]: STEP 6 — dispatching ImportNomenclatureJob', [
                'organization_id' => $organizationId,
                'group_ids' => !empty($groupIds) ? array_values($groupIds) : null,
                'status_key' => $statusKey,
            ]);

            Cache::put($statusKey, [
                'status'    => 'queued',
                'queued_at' => now()->toISOString(),
            ], 7200);

            ImportNomenclatureJob::dispatch(
                $organizationId,
                !empty($groupIds) ? array_values($groupIds) : null,
                $statusKey
            );

            return response()->json([
                'success'    => true,
                'queued'     => true,
                'status_key' => $statusKey,
                'message'    => trans('iiko-integration::app.management.import-nomenclature-queued'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.management.import-error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer by phone number.
     */
    public function getCustomerByPhone(Request $request): JsonResponse
    {
        try {
            $phone = $request->input('phone');
            $organizationId = $request->input('organization_id');

            if (!$phone) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.phone-required'),
                ], 400);
            }

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.organization-id-required'),
                ], 400);
            }

            $customer = $this->apiService->getCustomerByPhone($phone, $organizationId);

            if ($customer === null) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.no-data'),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data'    => $customer,
                'message' => trans('iiko-integration::app.management.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.management.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get promotions for organization.
     */
    public function getPromotions(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->input('organization_id');
            $forceRefresh = $request->input('force_refresh', false);

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.organization-id-required'),
                ], 400);
            }

            // Check cached promotions first (unless force refresh)
            if (!$forceRefresh) {
                $cachedPromotions = $this->promotionService->getCachedPromotions($organizationId);

                if ($cachedPromotions !== null && count($cachedPromotions) > 0) {
                    return response()->json([
                        'success' => true,
                        'data'    => $cachedPromotions,
                        'message' => trans('iiko-integration::app.management.success'),
                        'cached'  => true,
                    ]);
                }
            }

            // Sync from API if no cached data or force refresh
            $syncSuccess = $this->promotionService->syncPromotions($organizationId);

            if (!$syncSuccess) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.no-data'),
                ], 400);
            }

            // Get synced promotions
            $syncedPromotions = $this->promotionService->getCachedPromotions($organizationId);

            return response()->json([
                'success' => true,
                'data'    => $syncedPromotions ?? [],
                'message' => trans('iiko-integration::app.management.success'),
                'cached'  => false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.management.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment types for organization.
     */
    public function getPaymentTypes(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->input('organization_id');
            $forceRefresh = $request->input('force_refresh', false);

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.organization-id-required'),
                ], 400);
            }

            // Check cached payment types first (unless force refresh)
            if (!$forceRefresh) {
                $cachedPaymentTypes = $this->paymentTypeService->getCachedPaymentTypes($organizationId);

                if ($cachedPaymentTypes !== null && count($cachedPaymentTypes) > 0) {
                    return response()->json([
                        'success' => true,
                        'data'    => $cachedPaymentTypes,
                        'message' => trans('iiko-integration::app.management.success'),
                        'cached'  => true,
                    ]);
                }
            }

            // Sync from API if no cached data or force refresh
            $syncSuccess = $this->paymentTypeService->syncPaymentTypes($organizationId);

            if (!$syncSuccess) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.no-data'),
                ], 400);
            }

            // Get synced payment types
            $syncedPaymentTypes = $this->paymentTypeService->getCachedPaymentTypes($organizationId);

            return response()->json([
                'success' => true,
                'data'    => $syncedPaymentTypes ?? [],
                'message' => trans('iiko-integration::app.management.success'),
                'cached'  => false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.management.error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Poll import job status.
     */
    public function importStatus(Request $request): JsonResponse
    {
        $key = $request->input('key');

        if (!$key) {
            return response()->json(['success' => false, 'message' => 'Missing key'], 400);
        }

        $status = Cache::get($key);

        if (!$status) {
            return response()->json(['success' => false, 'status' => 'not_found'], 404);
        }

        return response()->json(array_merge(['success' => true], $status));
    }

    /**
     * Import terminal as inventory source and pickup point.
     */
    public function importTerminal(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->input('organization_id');
            $terminalId = $request->input('terminal_id');

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.organization-id-required'),
                ], 400);
            }

            if (!$terminalId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.terminal-id-required'),
                ], 400);
            }

            // Find terminal in database
            $terminal = $this->terminalGroupRepository->findWhere([
                'organization_id' => $organizationId,
                'iiko_id' => $terminalId,
            ])->first();

            if (!$terminal) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.no-data'),
                ], 404);
            }

            // Check if inventory source already exists for this terminal
            $existingInventorySource = $this->inventorySourceRepository->findWhere([
                'iiko_terminal_id' => $terminalId,
            ])->first();

            if ($existingInventorySource) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.import-skipped'),
                    'skipped' => true,
                ], 200);
            }

            // Get terminal data
            $terminalData = $terminal->terminal_group_data ?? [];
            $terminalName = $terminal->name ?? 'Terminal ' . $terminalId;

            // Generate unique code for inventory source
            $code = 'iiko_terminal_' . Str::slug($terminalName, '_') . '_' . substr($terminalId, 0, 8);
            $code = Str::limit($code, 50, '');

            // Ensure code is unique
            $counter = 1;
            $originalCode = $code;
            while ($this->inventorySourceRepository->findWhere(['code' => $code])->first()) {
                $code = $originalCode . '_' . $counter;
                $counter++;
            }

            // Extract address data from terminal_group_data
            $address = $terminalData['address'] ?? [];
            $coordinates = $terminalData['coordinates'] ?? [];

            // Prepare inventory source data
            // Ensure all required fields have non-empty values
            $inventorySourceData = [
                'code' => $code,
                'name' => $terminalName,
                'description' => $terminalData['description'] ?? null,
                'iiko_organization_id' => $organizationId,
                'iiko_terminal_id' => $terminalId,
                'contact_name' => !empty($terminalData['contactName']) ? $terminalData['contactName'] : $terminalName,
                'contact_email' => !empty($terminalData['contactEmail']) ? $terminalData['contactEmail'] : 'warehouse@example.com',
                'contact_number' => !empty($terminalData['contactPhone']) ? $terminalData['contactPhone'] : '1234567890',
                'contact_fax' => $terminalData['contactFax'] ?? null,
                'country' => !empty($address['country']) ? $address['country'] : (!empty($terminalData['country']) ? $terminalData['country'] : 'RU'),
                'state' => !empty($address['region']) ? $address['region'] : (!empty($address['state']) ? $address['state'] : (!empty($terminalData['state']) ? $terminalData['state'] : '')),
                'city' => !empty($address['city']) ? $address['city'] : (!empty($terminalData['city']) ? $terminalData['city'] : ''),
                'street' => !empty($address['street']) ? $address['street'] : (!empty($address['address']) ? $address['address'] : (!empty($terminalData['street']) ? $terminalData['street'] : '')),
                'postcode' => !empty($address['postcode']) ? $address['postcode'] : (!empty($address['postalCode']) ? $address['postalCode'] : (!empty($terminalData['postcode']) ? $terminalData['postcode'] : '')),
                'latitude' => $coordinates['latitude'] ?? $terminalData['latitude'] ?? null,
                'longitude' => $coordinates['longitude'] ?? $terminalData['longitude'] ?? null,
                'priority' => $terminalData['priority'] ?? 0,
                'status' => 1,
            ];

            // Create inventory source
            Event::dispatch('inventory.inventory_source.create.before');

            $inventorySource = $this->inventorySourceRepository->create($inventorySourceData);

            Event::dispatch('inventory.inventory_source.create.after', $inventorySource);

            // Prepare pickup point data
            $pickupPointData = [
                'name' => $terminalName,
                'inventory_source_id' => $inventorySource->id,
                'latitude' => $inventorySourceData['latitude'],
                'longitude' => $inventorySourceData['longitude'],
                'address' => trim(implode(', ', array_filter([
                    $inventorySourceData['street'],
                    $inventorySourceData['city'],
                    $inventorySourceData['state'],
                    $inventorySourceData['postcode'],
                ]))),
                'working_hours' => $terminalData['workingHours'] ?? $terminalData['working_hours'] ?? null,
            ];

            // Create pickup point
            Event::dispatch('inventory.pickup_point.create.before');

            $pickupPoint = $this->pickupPointRepository->create($pickupPointData);

            Event::dispatch('inventory.pickup_point.create.after', $pickupPoint);

            return response()->json([
                'success' => true,
                'message' => trans('iiko-integration::app.management.import-success'),
                'data' => [
                    'inventory_source_id' => $inventorySource->id,
                    'pickup_point_id' => $pickupPoint->id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('iiko-integration::app.management.import-error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }
}
