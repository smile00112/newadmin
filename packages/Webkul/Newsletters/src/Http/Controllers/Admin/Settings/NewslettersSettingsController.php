<?php

namespace Webkul\Newsletters\Http\Controllers\Admin\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Newsletters\Traits\HasNewsletterRole;
use Webkul\Core\Models\CoreConfig;

class NewslettersSettingsController extends Controller
{
    use HasNewsletterRole;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CoreConfigRepository $coreConfigRepository
    ) {}

    /**
     * Display the settings form.
     */
    public function index(): View
    {
        // Проверка прав доступа через систему разрешений
        if (!bouncer()->hasPermission('settings.newsletters')) {
            abort(403, trans('newsletters::app.admin.errors.permission-denied', [
                'permission' => 'settings.newsletters'
            ]));
        }

        // Получаем текущее значение часового пояса напрямую из базы данных
        // Ищем запись без учета channel_code и locale_code (они null для глобальных настроек)
        $config = CoreConfig::where('code', 'newsletters.settings.timezone')
            ->whereNull('channel_code')
            ->whereNull('locale_code')
            ->first();
        $timezone = $config ? $config->value : 'UTC';

        return view('newsletters::admin.settings.newsletters', [
            'timezone' => $timezone,
        ]);
    }

    /**
     * Store the settings.
     */
    public function store(): RedirectResponse
    {
        // Проверка прав доступа через систему разрешений
        if (!bouncer()->hasPermission('settings.newsletters')) {
            abort(403, trans('newsletters::app.admin.errors.permission-denied', [
                'permission' => 'settings.newsletters'
            ]));
        }

        $validated = request()->validate([
            'timezone' => 'required|string',
        ]);

        // Сохраняем настройки напрямую через модель CoreConfig
        // Настройки newsletters не привязаны к locale и channel
        $configCode = 'newsletters.settings.timezone';
        
        // Ищем запись без учета channel_code и locale_code (они null для глобальных настроек)
        $config = CoreConfig::where('code', $configCode)
            ->whereNull('channel_code')
            ->whereNull('locale_code')
            ->first();
        
        if ($config) {
            $config->update(['value' => $validated['timezone']]);
        } else {
            CoreConfig::create([
                'code' => $configCode,
                'value' => $validated['timezone'],
                'locale_code' => null,
                'channel_code' => null,
            ]);
        }

        session()->flash('success', trans('newsletters::app.admin.settings.newsletters.save-success'));

        return redirect()->route('admin.settings.newsletters.index');
    }
}

