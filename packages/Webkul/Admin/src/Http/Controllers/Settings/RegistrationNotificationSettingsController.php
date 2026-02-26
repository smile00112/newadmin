<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Models\CoreConfig;

class RegistrationNotificationSettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $admin = auth()->guard('admin')->user();
            
            if (!$admin || $admin->role_id != 2) {
                abort(403, 'This action is unauthorized.');
            }
            
            return $next($request);
        });
    }

    /**
     * Display the registration notification settings page.
     */
    public function index(): View
    {
        $emails = $this->getConfigValue('registration.notifications.emails');
        $newRegistrationEmails = $this->getConfigValue('registration.notifications.new_registration_emails');

        return view('admin::settings.registration-notifications.index', compact('emails', 'newRegistrationEmails'));
    }

    protected function getConfigValue(string $code): string
    {
        $config = CoreConfig::where('code', $code)
            ->whereNull('channel_code')
            ->whereNull('locale_code')
            ->first();

        return $config ? (string) $config->value : '';
    }

    /**
     * Update the registration notification settings.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'emails' => 'nullable|string',
            'new_registration_emails' => 'nullable|string',
        ]);

        $emailsResult = $this->validateAndNormalizeEmails($request->input('emails', ''));
        if ($emailsResult['invalid'] !== null) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.registration-notifications.invalid-email', [
                    'email' => $emailsResult['invalid'],
                ]),
            ], 422);
        }

        $newResult = $this->validateAndNormalizeEmails($request->input('new_registration_emails', ''));
        if ($newResult['invalid'] !== null) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.registration-notifications.invalid-email', [
                    'email' => $newResult['invalid'],
                ]),
            ], 422);
        }

        $this->saveConfig('registration.notifications.emails', $emailsResult['value']);
        $this->saveConfig('registration.notifications.new_registration_emails', $newResult['value']);

        return new JsonResponse([
            'message' => trans('admin::app.settings.registration-notifications.update-success'),
            'emails' => $emailsResult['value'],
            'new_registration_emails' => $newResult['value'],
        ]);
    }

    /**
     * @return array{value: string, invalid: string|null}
     */
    protected function validateAndNormalizeEmails(?string $value): array
    {
        $value = $value ?? '';
        if ($value === '') {
            return ['value' => '', 'invalid' => null];
        }

        $emailArray = array_map('trim', explode(',', $value));
        $emailArray = array_filter($emailArray);

        foreach ($emailArray as $email) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['value' => '', 'invalid' => $email];
            }
        }

        return ['value' => implode(',', $emailArray), 'invalid' => null];
    }

    protected function saveConfig(string $code, string $value): void
    {
        $existing = CoreConfig::where('code', $code)
            ->whereNull('channel_code')
            ->whereNull('locale_code')
            ->first();

        if ($existing) {
            $existing->update(['value' => $value]);
        } else {
            CoreConfig::create([
                'code' => $code,
                'value' => $value,
                'channel_code' => null,
                'locale_code' => null,
            ]);
        }
    }
}
