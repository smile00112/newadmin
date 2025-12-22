<?php

namespace Webkul\MobileApp\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\MobileApp\Repositories\MobileAppSettingRepository;

class MobileSettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected MobileAppSettingRepository $settingRepository,
        protected AttributeRepository $attributeRepository
    ) {}

    /**
     * Get all mobile app settings.
     */
    public function index(): JsonResponse
    {
        $channelCode = request('channel', core()->getDefaultChannelCode());
        
        $settings = $this->settingRepository->getAllSettings($channelCode);

        // Add core config values
        $settings['app_name'] = $settings['app_name'] 
            ?? core()->getConfigData('mobile_app.general.app_info.app_name');
        $settings['app_version'] = $settings['app_version'] 
            ?? core()->getConfigData('mobile_app.general.app_info.app_version');
        $settings['min_app_version'] = $settings['min_app_version'] 
            ?? core()->getConfigData('mobile_app.general.app_info.min_app_version');
        $settings['force_update'] = (bool) ($settings['force_update'] 
            ?? core()->getConfigData('mobile_app.general.app_info.force_update'));
        $settings['maintenance_mode'] = (bool) ($settings['maintenance_mode'] 
            ?? core()->getConfigData('mobile_app.general.app_info.maintenance_mode'));
        $settings['custom_data'] = $settings['custom_data'] 
            ?? core()->getConfigData('mobile_app.general.custom.custom_data');

        // Expand home_filters with attribute options
        if (!empty($settings['home_filters'])) {
            $settings['home_filters'] = $this->expandHomeFilters($settings['home_filters']);
        }

        return response()->json([
            'data' => $settings,
        ]);
    }

    /**
     * Expand home filters with attribute details and options.
     */
    protected function expandHomeFilters(array $attributeCodes): array
    {
        $filters = [];

        foreach ($attributeCodes as $code) {
            $attribute = $this->attributeRepository->findOneByField('code', $code);

            if (!$attribute) {
                continue;
            }

            $filter = [
                'code'       => $attribute->code,
                'name'       => $attribute->admin_name ?? $attribute->code,
                'type'       => $attribute->type,
                'options'    => [],
            ];

            // Add options if attribute has them
            if ($attribute->options && $attribute->options->count() > 0) {
                $filter['options'] = $attribute->options->map(function ($option) {
                    return [
                        'id'    => $option->id,
                        'code'  => $option->admin_name ?? $option->id,
                        'label' => $option->label ?? $option->admin_name,
                    ];
                })->toArray();
            }

            $filters[] = $filter;
        }

        return $filters;
    }
}

