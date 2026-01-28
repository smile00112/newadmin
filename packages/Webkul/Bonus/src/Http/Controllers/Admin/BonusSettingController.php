<?php

namespace Webkul\Bonus\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Bonus\Repositories\BonusSettingRepository;
use Webkul\Product\Repositories\ProductRepository;

class BonusSettingController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected BonusSettingRepository $bonusSettingRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $channelCode = request('channel', core()->getDefaultChannelCode());

        $settings = [
            'enabled' => $this->bonusSettingRepository->isBonusEnabled($channelCode),
            'max_usage_percent' => $this->bonusSettingRepository->getMaxUsagePercent($channelCode),
            'expiry_days' => $this->bonusSettingRepository->getExpiryDays($channelCode),
            'participating_product_ids' => $this->bonusSettingRepository->getParticipatingProductIds($channelCode),
            'excluded_product_ids' => $this->bonusSettingRepository->getExcludedProductIds($channelCode),
        ];

        return view('bonus::admin.settings.index', compact('settings', 'channelCode'));
    }

    /**
     * Store settings.
     */
    public function store(): RedirectResponse
    {
        $channelCode = request('channel_code');
        $settings = request('settings', []);

        $this->bonusSettingRepository->saveSettings('bonus', [
            'enabled' => $settings['enabled'] ?? 0,
            'max_usage_percent' => $settings['max_usage_percent'] ?? 100,
            'expiry_days' => $settings['expiry_days'] ?? 365,
            'participating_product_ids' => json_encode($settings['participating_product_ids'] ?? []),
            'excluded_product_ids' => json_encode($settings['excluded_product_ids'] ?? []),
        ], $channelCode);

        session()->flash('success', trans('bonus::app.admin.settings.save-success'));

        return redirect()->back();
    }
}
