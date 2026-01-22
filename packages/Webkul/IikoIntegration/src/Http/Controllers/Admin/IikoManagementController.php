<?php

namespace Webkul\IikoIntegration\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\IikoIntegration\Services\IikoApiService;
use Webkul\IikoIntegration\Services\IikoMenuService;
use Webkul\IikoIntegration\Services\IikoOrganizationService;

class IikoManagementController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected IikoOrganizationService $organizationService,
        protected IikoApiService $apiService,
        protected IikoMenuService $menuService
    ) {}

    /**
     * Display the management page.
     */
    public function index(): View
    {
        return view('iiko-integration::admin.iiko.management');
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

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.organization-id-required'),
                ], 400);
            }

            $response = $this->apiService->getTerminalGroups($organizationId);

            if ($response === null || !isset($response['terminalGroups'])) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.no-data'),
                ], 400);
            }

            $formattedTerminals = array_map(function ($terminal) {
                return [
                    'id'   => $terminal['id'] ?? '',
                    'name' => $terminal['name'] ?? '',
                ];
            }, $response['terminalGroups']);

            return response()->json([
                'success' => true,
                'data'    => $formattedTerminals,
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

            return response()->json([
                'success' => true,
                'data'    => $menu,
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

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.organization-id-required'),
                ], 400);
            }

            $nomenclature = $this->apiService->getNomenclature($organizationId);

            if ($nomenclature === null) {
                return response()->json([
                    'success' => false,
                    'message' => trans('iiko-integration::app.management.no-data'),
                ], 400);
            }

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
}
