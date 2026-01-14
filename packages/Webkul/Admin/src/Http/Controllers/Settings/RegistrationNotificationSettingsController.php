<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Repositories\CoreConfigRepository;

class RegistrationNotificationSettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CoreConfigRepository $coreConfigRepository
    ) {
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
        $emails = core()->getConfigData('registration.notifications.emails') ?? '';

        return view('admin::settings.registration-notifications.index', compact('emails'));
    }

    /**
     * Update the registration notification settings.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'emails' => 'nullable|string',
        ]);

        $emails = $request->input('emails', '');

        // Validate email addresses if provided
        if (!empty($emails)) {
            $emailArray = array_map('trim', explode(',', $emails));
            $emailArray = array_filter($emailArray);

            foreach ($emailArray as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return new JsonResponse([
                        'message' => trans('admin::app.settings.registration-notifications.invalid-email', ['email' => $email]),
                    ], 422);
                }
            }

            $emails = implode(',', $emailArray);
        }

        // Save to core_config
        $existingConfig = $this->coreConfigRepository->findOneWhere([
            'code' => 'registration.notifications.emails',
        ]);

        if ($existingConfig) {
            $this->coreConfigRepository->update([
                'value' => $emails,
            ], $existingConfig->id);
        } else {
            $this->coreConfigRepository->create([
                'code' => 'registration.notifications.emails',
                'value' => $emails,
            ]);
        }

        return new JsonResponse([
            'message' => trans('admin::app.settings.registration-notifications.update-success'),
        ]);
    }
}
