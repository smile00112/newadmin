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
     * Available tabs.
     */
    protected array $tabs = ['settings', 'levels', 'manage'];

    /**
     * Tab labels translation keys.
     */
    protected array $tabLabels = [
        'settings' => 'bonus::app.admin.settings.general.title',
        'levels'   => 'bonus::app.admin.settings.levels.title',
        'manage'   => 'bonus::app.admin.settings.manage.title',
    ];

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
    public function index(?string $tab = null): View
    {
        $tab = in_array($tab, $this->tabs) ? $tab : 'settings';

        $channelCode = request('channel', core()->getDefaultChannelCode());

        $settings = [
            'enabled' => $this->bonusSettingRepository->isBonusEnabled($channelCode),
            'max_usage_percent' => $this->bonusSettingRepository->getMaxUsagePercent($channelCode),
            'expiry_days' => $this->bonusSettingRepository->getExpiryDays($channelCode),
            'participating_product_ids' => $this->bonusSettingRepository->getParticipatingProductIds($channelCode),
            'excluded_product_ids' => $this->bonusSettingRepository->getExcludedProductIds($channelCode),
        ];

        $tabs = $this->tabs;
        $tabLabels = array_map(fn($key) => trans($key), $this->tabLabels);

        return view('bonus::admin.settings.index', compact('settings', 'channelCode', 'tab', 'tabs', 'tabLabels'));
    }

    /**
     * Store settings.
     */
    public function store(?string $tab = null): RedirectResponse
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
