<?php

namespace Webkul\IikoIntegration\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\IikoIntegration\Repositories\IikoOrganizationRepository;
use Webkul\IikoIntegration\Services\IikoApiService;
use Webkul\IikoIntegration\Services\IikoMenuService;
use Webkul\IikoIntegration\Services\IikoNomenclatureService;
use Webkul\IikoIntegration\Services\IikoOrganizationService;
use Webkul\IikoIntegration\Services\IikoTerminalGroupService;
use Webkul\IikoIntegration\Services\IikoPromotionService;
use Webkul\IikoIntegration\Services\IikoPaymentTypeService;

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
        protected IikoPaymentTypeService $paymentTypeService
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
            $organizations = $this->organizationService->getOrganizations();

            if ($organizations === null) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.error'),
                ], 400);
            }

            $formattedOrganizations = array_map(function ($org) {
                return [
                    'id'   => $org['id'] ?? '',
                    'name' => $org['name'] ?? '',
                ];
            }, $organizations);

            return response()->json([
                'success' => true,
                'data'    => $formattedOrganizations,
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

            // Sync nomenclature from API and save
            $syncSuccess = $this->nomenclatureService->syncNomenclature($organizationId, null, $externalMenuId);

            if (!$syncSuccess) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.no-data'),
                ], 400);
            }

            // Get synced nomenclature
            $nomenclature = $this->nomenclatureService->getCachedNomenclature($organizationId);

            return response()->json([
                'success' => true,
                'data'    => $nomenclature,
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
}
