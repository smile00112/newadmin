<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Traits\HasNewsletterRole;
use Webkul\Newsletters\Mail\WelcomeAdminNotification;
use Webkul\Newsletters\Mail\NewUserNotification;
use Webkul\User\Repositories\AdminRepository;
use Webkul\User\Repositories\RoleRepository;
use Webkul\Newsletters\Repositories\CompanyRepository;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;
use Webkul\Newsletters\Repositories\AccountTopupRepository;

class OwnersController extends Controller
{
    use HasNewsletterRole;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AdminRepository $adminRepository,
        protected RoleRepository $roleRepository,
        protected CompanyRepository $companyRepository,
        protected CompanyAccountRepository $accountRepository,
        protected AccountTopupRepository $topupRepository
    ) {}

    /**
     * Get context for current user.
     */
    protected function getContext(): array
    {
        $admin = auth()->guard('admin')->user();

        // Супер-админ (без company_id)
        if (!$admin->company_id && $admin->role && $admin->role->permission_type === 'all') {
            return [
                'type' => 'super_admin',
                'can_create_companies' => true,
                'can_assign_owner_role' => true,
                'can_topup' => true,
                'can_resend_email' => true,
            ];
        }

        // Владелец компании
        if ($admin->company_id && $this->isCompanyOwner()) {
            return [
                'type' => 'company_owner',
                'company_id' => $admin->company_id,
                'can_create_companies' => false,
                'can_assign_owner_role' => false,
                'can_topup' => false,
                'can_resend_email' => false,
            ];
        }

        abort(403);
    }

    /**
     * Display a listing of company owners or managers.
     */
    public function index()
    {
        $context = $this->getContext();
        $admin = auth()->guard('admin')->user();

        if ($context['type'] === 'super_admin') {
            // Для супер-админов показываем всех пользователей компаний (владельцев и менеджеров)
            $this->requireNewsletterPermission('newsletters.owners.view');

            $users = $this->adminRepository
                ->whereNotNull('company_id')
                ->with(['role', 'company'])
                ->get();
        } else {
            // Для владельцев компаний показываем менеджеров
            $this->requireNewsletterPermission('newsletters.managers');

            $users = $this->adminRepository
                ->where('company_id', $admin->company_id)
                ->where('id', '!=', $admin->id) // Исключить текущего админа
                ->whereHas('role', function($query) {
                    $query->where('name', '!=', 'Владелец компании')
                          ->where('permission_type', '!=', 'all');
                })
                ->with('role')
                ->get();
        }

        return view('newsletters::admin.owners.index', compact('users', 'context'));
    }

    /**
     * Show the form for creating a new owner or manager.
     */
    public function create()
    {
        $context = $this->getContext();

        if ($context['type'] === 'super_admin') {
            $this->requireNewsletterPermission('newsletters.owners.create');

            // Получить все компании для выбора
            $companies = $this->companyRepository->all();

            // Получить все роли (включая владельцев и менеджеров), исключая роли администраторов (ID 1 и 2)
            $roles = $this->roleRepository
                ->whereNotIn('id', [1, 2])
                ->get();

            $defaultRole = $roles->firstWhere('name', 'Владелец компании')
                        ?? $roles->firstWhere('permission_type', 'all');
        } else {
            $this->requireNewsletterPermission('newsletters.managers.create');

            $companies = null;

            // Получить роли для менеджеров (исключая "Владелец компании")
            $roles = $this->roleRepository
                ->where('permission_type', 'custom')
                ->where('name', '!=', 'Владелец компании')
                ->get();

            $defaultRole = $roles->firstWhere('name', 'Менеджер рассылок');
        }

        return view('newsletters::admin.owners.create', compact('companies', 'roles', 'defaultRole', 'context'));
    }

    /**
     * Store a newly created owner or manager.
     */
    public function store(Request $request)
    {
        $context = $this->getContext();
        $admin = auth()->guard('admin')->user();

        if ($context['type'] === 'super_admin') {
            $this->requireNewsletterPermission('newsletters.owners.create');

            // Для супер-админов разрешены все роли
            $role = $this->roleRepository->findOrFail($request->input('role_id'));
            $isOwnerRole = $role->name === 'Владелец компании' || $role->permission_type === 'all';

            // Валидация в зависимости от роли
            if ($isOwnerRole) {
                $data = $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:admins,email',
                    'password' => 'required|min:6|confirmed',
                    'role_id' => 'required|exists:roles,id',
                    'company_option' => 'required|in:existing,new',
                    'company_id' => 'required_if:company_option,existing|exists:companies,id',
                    'company_name' => 'required_if:company_option,new|string|max:255',
                    'company_description' => 'nullable|string',
                    'status' => 'boolean',
                ]);
            } else {
                // Для менеджеров только существующая компания
                $data = $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:admins,email',
                    'password' => 'required|min:6|confirmed',
                    'role_id' => 'required|exists:roles,id',
                    'company_id' => 'required|exists:companies,id',
                    'status' => 'boolean',
                ]);
            }

            // Определяем компанию
            if ($isOwnerRole && isset($data['company_option']) && $data['company_option'] === 'new') {
                // Создаем новую компанию для владельца
                $companySlug = Str::slug($data['company_name']);
                $slugCounter = 1;
                $originalSlug = $companySlug;
                while ($this->companyRepository->findOneWhere(['slug' => $companySlug])) {
                    $companySlug = $originalSlug . '-' . $slugCounter;
                    $slugCounter++;
                }

                $company = $this->companyRepository->create([
                    'name' => $data['company_name'],
                    'slug' => $companySlug,
                    'description' => $data['company_description'] ?? 'Company for ' . $data['name'],
                    'is_active' => true,
                ]);
                $companyId = $company->id;
            } else {
                // Используем существующую компанию
                $company = $this->companyRepository->findOrFail($data['company_id']);
                $companyId = $company->id;
            }

            $sendEmail = $isOwnerRole;
        } else {
            $this->requireNewsletterPermission('newsletters.managers.create');

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:admins,email',
                'password' => 'required|min:6|confirmed',
                'role_id' => 'required|exists:roles,id',
            ]);

            // Проверяем, что роль не является ролью владельца
            $role = $this->roleRepository->findOrFail($data['role_id']);
            if ($role->permission_type === 'all' || $role->name === 'Владелец компании') {
                abort(403, trans('newsletters::app.admin.errors.cannot-assign-owner-role'));
            }

            $companyId = $admin->company_id;
            $sendEmail = false;
        }

        // Создаем admin аккаунт
        $newAdmin = $this->adminRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'company_id' => $companyId,
            'status' => $request->has('status') ? (bool) $request->input('status') : 1,
            'api_token' => Str::random(80),
        ]);

        // Отправляем приветственное письмо только для владельцев
        if ($sendEmail) {
            try {
                Mail::send(new WelcomeAdminNotification($newAdmin, $data['password']));
                Log::info('Welcome email sent for admin: ' . $newAdmin->email);
            } catch (\Exception $mailException) {
                Log::error('Failed to send welcome email: ' . $mailException->getMessage());
            }
        }

        // Отправляем уведомление администраторам о новом пользователе
        try {
            // Получаем название компании
            $company = $this->companyRepository->find($companyId);
            $companyName = $company ? $company->name : 'Не указана';

            // Получаем всех супер-администраторов (с permission_type 'all' и без company_id)
            $superAdmins = $this->adminRepository
                ->getModel()
                ->leftJoin('roles', 'admins.role_id', '=', 'roles.id')
                ->where('roles.permission_type', 'all')
                ->whereNull('admins.company_id')
                ->where('admins.status', 1)
                ->select('admins.*')
                ->get();

            foreach ($superAdmins as $superAdmin) {
                try {
                    Mail::to($superAdmin->email)->sendNow(new NewUserNotification($newAdmin, $companyName, '', $data['password']));
                    Log::info('New user notification sent to admin: ' . $superAdmin->email);
                } catch (\Exception $notificationException) {
                    Log::error('Failed to send new user notification to admin: ' . $superAdmin->email, [
                        'trace' => $notificationException->getTraceAsString(),
                        'admin_id' => $superAdmin->id,
                        'new_user_id' => $newAdmin->id
                    ]);
                    // Продолжаем отправку остальным администраторам
                }
            }
        } catch (\Exception $notificationException) {
            Log::error('Failed to send new user notifications: ' . $notificationException->getMessage(), [
                'trace' => $notificationException->getTraceAsString(),
                'new_user_id' => $newAdmin->id
            ]);
            // Продолжаем выполнение, даже если уведомления не отправились
        }

        $message = $context['type'] === 'super_admin'
            ? trans('newsletters::app.admin.owners.create-success')
            : trans('newsletters::app.admin.managers.create-success');

        session()->flash('success', $message);

        // Если создание происходит со страницы редактирования компании, возвращаемся туда
        if ($request->has('redirect_to_company') && $request->input('redirect_to_company')) {
            return redirect()->route('admin.newsletters.companies.edit', $companyId);
        }

        return redirect()->route('admin.newsletters.owners.index');
    }

    /**
     * Show the form for editing the specified owner or manager.
     */
    public function edit(int $id)
    {
        $context = $this->getContext();
        $admin = auth()->guard('admin')->user();
        $user = $this->adminRepository->findOrFail($id);
        $user->load(['role', 'company']);

        if ($context['type'] === 'super_admin') {
            $this->requireNewsletterPermission('newsletters.owners.edit');

            // Для супер-админов можно редактировать любых пользователей с company_id
            if (!$user->company_id) {
                abort(404, trans('newsletters::app.admin.owners.not-found'));
            }

            // Показываем все роли для супер-админов, исключая роли администраторов (ID 1 и 2)
            $roles = $this->roleRepository
                ->whereNotIn('id', [1, 2])
                ->get();
        } else {
            $this->requireNewsletterPermission('newsletters.managers.edit');

            // Проверка, что пользователь принадлежит той же компании
            if ($user->company_id !== $admin->company_id) {
                abort(403, trans('newsletters::app.admin.errors.different-company'));
            }

            // Нельзя редактировать владельца
            if ($user->role->permission_type === 'all' || $user->role->name === 'Владелец компании') {
                abort(403, trans('newsletters::app.admin.errors.cannot-edit-owner'));
            }

            $roles = $this->roleRepository
                ->where('permission_type', 'custom')
                ->where('name', '!=', 'Владелец компании')
                ->get();
        }

        return view('newsletters::admin.owners.edit', compact('user', 'roles', 'context'));
    }

    /**
     * Update the specified owner or manager.
     */
    public function update(Request $request, int $id)
    {
        $context = $this->getContext();
        $admin = auth()->guard('admin')->user();
        $user = $this->adminRepository->findOrFail($id);

        if ($context['type'] === 'super_admin') {
            $this->requireNewsletterPermission('newsletters.owners.edit');

            // Для супер-админов можно редактировать любых пользователей с company_id
            if (!$user->company_id) {
                abort(404, trans('newsletters::app.admin.owners.not-found'));
            }

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:admins,email,' . $id,
                'role_id' => 'required|exists:roles,id',
                'status' => 'boolean',
            ]);

            // Для супер-админов разрешены все роли
            $role = $this->roleRepository->findOrFail($data['role_id']);

            $successMessage = trans('newsletters::app.admin.owners.update-success');
        } else {
            $this->requireNewsletterPermission('newsletters.managers.edit');

            // Проверка, что пользователь принадлежит той же компании
            if ($user->company_id !== $admin->company_id) {
                abort(403, trans('newsletters::app.admin.errors.different-company'));
            }

            // Нельзя редактировать владельца
            if ($user->role->permission_type === 'all' || $user->role->name === 'Владелец компании') {
                abort(403, trans('newsletters::app.admin.errors.cannot-edit-owner'));
            }

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:admins,email,' . $id,
                'password' => 'nullable|min:6|confirmed',
                'role_id' => 'required|exists:roles,id',
                'status' => 'boolean',
            ]);

            // Проверяем роль
            $role = $this->roleRepository->findOrFail($data['role_id']);
            if ($role->permission_type === 'all' || $role->name === 'Владелец компании') {
                abort(403, trans('newsletters::app.admin.errors.cannot-assign-owner-role'));
            }

            // Хешировать пароль, если он указан
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $successMessage = trans('newsletters::app.admin.managers.update-success');
        }

        $this->adminRepository->update($data, $id);

        session()->flash('success', $successMessage);

        return redirect()->route('admin.newsletters.owners.index');
    }

    /**
     * Toggle user status (enable/disable).
     */
    public function toggleStatus(int $id)
    {
        $context = $this->getContext();
        $user = $this->adminRepository->findOrFail($id);

        if ($context['type'] === 'super_admin') {
            $this->requireNewsletterPermission('newsletters.owners.toggle-status');

            // Для супер-админов можно изменять статус любых пользователей с company_id
            if (!$user->company_id) {
                abort(404, trans('newsletters::app.admin.owners.not-found'));
            }
        } else {
            $admin = auth()->guard('admin')->user();

            // Проверка, что пользователь принадлежит той же компании
            if ($user->company_id !== $admin->company_id) {
                abort(403, trans('newsletters::app.admin.errors.different-company'));
            }
        }

        $user->status = !$user->status;
        $user->save();

        $message = $user->status
            ? trans('newsletters::app.admin.owners.enabled-success')
            : trans('newsletters::app.admin.owners.disabled-success');

        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $user->status,
        ]);
    }

    /**
     * Top up account for owner's company (super admin only).
     */
    public function topup(Request $request, int $id)
    {
        $context = $this->getContext();

        if ($context['type'] !== 'super_admin') {
            abort(403);
        }

        $this->requireNewsletterPermission('newsletters.owners.topup');

        $owner = $this->adminRepository->findOrFail($id);

        // Проверка, что это владелец компании
        if (!$owner->role ||
            ($owner->role->name !== 'Владелец компании' && $owner->role->permission_type !== 'all') ||
            !$owner->company_id) {
            abort(404, trans('newsletters::app.admin.owners.not-found'));
        }

        $this->validate($request, [
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $admin = auth()->guard('admin')->user();
        $account = $this->accountRepository->getOrCreateForCompany($owner->company_id);

        // Create topup record
        $topup = $this->topupRepository->create([
            'account_id' => $account->id,
            'amount' => $request->amount,
            'admin_id' => $admin->id,
            'notes' => $request->notes ?? 'Top up by admin',
        ]);

        // Add balance to account
        $this->accountRepository->addBalance($account->id, $request->amount);

        session()->flash('success', trans('newsletters::app.admin.owners.topup-success'));

        return redirect()->route('admin.newsletters.owners.index');
    }

    /**
     * Resend registration email notification to owner (super admin only).
     */
    public function resendRegistrationEmail(int $id)
    {
        $context = $this->getContext();

        if ($context['type'] !== 'super_admin') {
            abort(403);
        }

        $this->requireNewsletterPermission('newsletters.owners.edit');

        $owner = $this->adminRepository->findOrFail($id);

        // Проверка, что это владелец компании
        if (!$owner->role ||
            ($owner->role->name !== 'Владелец компании' && $owner->role->permission_type !== 'all') ||
            !$owner->company_id) {
            abort(404, trans('newsletters::app.admin.owners.not-found'));
        }

        try {
            Log::error('resend registration email: ' , [
                'owner' => $owner,
            ]);
            // Отправляем копию приветственного письма
            // Используем placeholder для пароля, так как оригинальный пароль недоступен

            Mail::sendNow(new WelcomeAdminNotification($owner, 'Используйте ваш существующий пароль'));
            Log::info('Registration email resent for admin: ' . $owner->email . ' (Company: ' . ($owner->company ? $owner->company->name : 'N/A') . ')');

            session()->flash('success', trans('newsletters::app.admin.owners.email-resent-success'));
        } catch (\Exception $mailException) {
            Log::error('Failed to resend registration email: ' . $mailException->getMessage(), [
                'trace' => $mailException->getTraceAsString(),
                'admin_id' => $owner->id,
                'admin_email' => $owner->email
            ]);

            session()->flash('error', trans('newsletters::app.admin.owners.email-resent-failed'));
        }

        return redirect()->route('admin.newsletters.owners.index');
    }

    /**
     * Remove the specified owner or manager.
     */
    public function destroy(int $id)
    {
        $context = $this->getContext();
        $admin = auth()->guard('admin')->user();
        $user = $this->adminRepository->findOrFail($id);

        if ($context['type'] === 'super_admin') {
            $this->requireNewsletterPermission('newsletters.owners.delete');

            // Для супер-админов можно удалять любых пользователей с company_id
            if (!$user->company_id) {
                abort(404, trans('newsletters::app.admin.owners.not-found'));
            }

            // Нельзя удалить самого себя
            if ($user->id === $admin->id) {
                session()->flash('error', trans('newsletters::app.admin.owners.cannot-delete-self'));
                return redirect()->route('admin.newsletters.owners.index');
            }

            try {
                // Вместо удаления отключаем
                $user->status = 0;
                $user->save();

                session()->flash('success', trans('newsletters::app.admin.owners.delete-success'));
            } catch (\Exception $e) {
                session()->flash('error', trans('newsletters::app.admin.owners.delete-failed'));
            }
        } else {
            $this->requireNewsletterPermission('newsletters.managers.delete');

            // Проверка, что пользователь принадлежит той же компании
            if ($user->company_id !== $admin->company_id) {
                abort(403, trans('newsletters::app.admin.errors.different-company'));
            }

            // Нельзя удалить владельца
            if ($user->role->permission_type === 'all' || $user->role->name === 'Владелец компании') {
                abort(403, trans('newsletters::app.admin.errors.cannot-delete-owner'));
            }

            // Нельзя удалить самого себя
            if ($user->id === $admin->id) {
                abort(403, trans('newsletters::app.admin.errors.cannot-delete-self'));
            }

            try {
                $this->adminRepository->delete($id);

                session()->flash('success', trans('newsletters::app.admin.managers.delete-success'));
            } catch (\Exception $e) {
                session()->flash('error', trans('newsletters::app.admin.managers.delete-failed'));
            }
        }

        return redirect()->route('admin.newsletters.owners.index');
    }

    /**
     * Update user permissions (for managers only).
     */
    public function updatePermissions(Request $request, int $id)
    {
        $context = $this->getContext();

        if ($context['type'] !== 'company_owner') {
            abort(403);
        }

        $this->requireNewsletterPermission('newsletters.managers.edit');

        $admin = auth()->guard('admin')->user();
        $user = $this->adminRepository->findOrFail($id);

        // Проверка, что пользователь принадлежит той же компании
        if ($user->company_id !== $admin->company_id) {
            abort(403, trans('newsletters::app.admin.errors.different-company'));
        }

        // Обновить разрешения роли
        $role = $user->role;
        $permissions = $request->input('permissions', []);

        // Фильтровать только разрешения newsletters
        $newsletterPermissions = array_filter($permissions, function($permission) {
            return strpos($permission, 'newsletters.') === 0;
        });

        $role->permissions = array_values($newsletterPermissions);
        $role->save();

        session()->flash('success', trans('newsletters::app.admin.managers.permissions-updated'));

        return redirect()->route('admin.newsletters.owners.index');
    }

    /**
     * Impersonate (login as) another user (super admin only).
     */
    public function impersonate(int $id)
    {
        $context = $this->getContext();

        if ($context['type'] !== 'super_admin') {
            abort(403);
        }

        $this->requireNewsletterPermission('newsletters.owners.view');

        $targetUser = $this->adminRepository->findOrFail($id);

        // Проверка, что это пользователь компании
        if (!$targetUser->company_id) {
            abort(404, trans('newsletters::app.admin.owners.not-found'));
        }

        // Проверка, что пользователь активен
        if (!$targetUser->status) {
            session()->flash('error', trans('newsletters::app.admin.owners.cannot-impersonate-inactive'));
            return redirect()->route('admin.newsletters.owners.index');
        }

        $currentAdmin = auth()->guard('admin')->user();

        // Сохраняем ID оригинального администратора в сессии
        session()->put('impersonator_id', $currentAdmin->id);
        session()->put('impersonator_name', $currentAdmin->name);

        // Входим под пользователем
        auth()->guard('admin')->login($targetUser);

        session()->flash('success', trans('newsletters::app.admin.owners.impersonate-success', ['name' => $targetUser->name]));

        return redirect()->route('admin.newsletters.mailing-lists.index');
    }

    /**
     * Stop impersonation and return to admin account.
     */
    public function stopImpersonate()
    {
        $impersonatorId = session()->get('impersonator_id');

        if (!$impersonatorId) {
            // Если нет информации об impersonator, просто выходим
            auth()->guard('admin')->logout();
            return redirect()->route('admin.session.create');
        }

        $impersonator = $this->adminRepository->findOrFail($impersonatorId);

        // Очищаем сессию impersonation
        session()->forget('impersonator_id');
        session()->forget('impersonator_name');

        // Возвращаемся к оригинальному администратору
        auth()->guard('admin')->login($impersonator);

        session()->flash('success', trans('newsletters::app.admin.owners.stop-impersonate-success'));

        return redirect()->route('admin.newsletters.owners.index');
    }
}

