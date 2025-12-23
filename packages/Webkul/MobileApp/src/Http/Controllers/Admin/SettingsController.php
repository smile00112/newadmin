<?php

namespace Webkul\MobileApp\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\MobileApp\Config\FieldsConfig;
use Webkul\MobileApp\Repositories\MobileAppSettingRepository;

class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected MobileAppSettingRepository $settingRepository,
        protected FieldsConfig $fieldsConfig
    ) {}

    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $channelCode = request('channel', core()->getDefaultChannelCode());
        $fields = $this->settingRepository->getSettingsWithFields($channelCode);

        return view('mobile_app::admin.settings.index', compact('fields', 'channelCode'));
    }

    /**
     * Store settings.
     */
    public function store(): RedirectResponse
    {
        $channelCode = request('channel_code');
        $settings = request('settings', []);

        $this->settingRepository->saveSettings($settings, $channelCode);

        session()->flash('success', trans('mobile_app::app.settings.save-success'));

        return redirect()->back();
    }

    /**
     * Get field options via AJAX.
     */
    public function getFieldOptions(string $source)
    {
        $options = $this->fieldsConfig->getOptionsForSource($source);

        return response()->json(['options' => $options]);
    }
}


