<?php

namespace Webkul\Newsletters\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Webkul\User\Repositories\AdminRepository;
use Webkul\User\Repositories\RoleRepository;
use Webkul\Newsletters\Repositories\CompanyRepository;
use Webkul\Newsletters\Mail\WelcomeAdminNotification;
use Webkul\Newsletters\Mail\NewUserNotification;
use Webkul\Newsletters\Models\RegistrationRequest;

class LandingPageController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AdminRepository $adminRepository,
        protected RoleRepository $roleRepository,
        protected CompanyRepository $companyRepository
    ) {}

    /**
     * Display the landing page.
     */
    public function index()
    {
        return view('newsletters::landing.index');
    }

    /**
     * Display payment terms page.
     */
    public function paymentTerms()
    {
        return view('newsletters::landing.payment-terms');
    }

    /**
     * Display privacy policy page.
     */
    public function privacyPolicy()
    {
        return view('newsletters::landing.privacy-policy');
    }

    /**
     * Display offer page.
     */
    public function offer()
    {
        return view('newsletters::landing.offer');
    }

    /**
     * Store registration request.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'plan' => 'nullable|string|in:start,pro,corporate',
            'privacy_policy_accepted' => 'required|accepted',
        ], [
            'privacy_policy_accepted.required' => 'Необходимо принять политику конфиденциальности.',
            'privacy_policy_accepted.accepted' => 'Необходимо принять политику конфиденциальности.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Пожалуйста, заполните все обязательные поля корректно.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Проверяем уникальность email в таблицах registration_requests и admins
        $emailExistsInRequests = DB::table('registration_requests')
            ->where('email', $request->email)
            ->exists();

        $existingAdmin = $this->adminRepository->findOneByField('email', $request->email);

        if ($emailExistsInRequests || $existingAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь с таким email уже зарегистрирован или подал заявку на регистрацию.',
                'errors' => ['email' => ['Пользователь с таким email уже зарегистрирован или подал заявку на регистрацию.']]
            ], 422);
        }

        try {
            // Сохраняем заявку на регистрацию
            RegistrationRequest::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'plan' => $request->plan,
                'status' => 'pending',
            ]);

            // Генерируем случайный пароль
            $password = Str::random(12);

            // Получаем роль "Владелец компании"
            $ownerRole = $this->roleRepository->findOneWhere(['name' => 'Владелец компании']);

            if (!$ownerRole) {
                Log::error('Owner role not found (role with name "Владелец компании")');
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка конфигурации системы. Пожалуйста, обратитесь к администратору.'
                ], 500);
            }

            // Создаем компанию для owner
            $companyName = $request->name . ' Company';
            $companySlug = Str::slug($companyName);

            // Проверяем уникальность slug
            $slugCounter = 1;
            $originalSlug = $companySlug;
            while ($this->companyRepository->findOneWhere(['slug' => $companySlug])) {
                $companySlug = $originalSlug . '-' . $slugCounter;
                $slugCounter++;
            }

            $company = $this->companyRepository->create([
                'name' => $companyName,
                'slug' => $companySlug,
                'description' => 'Company for ' . $request->name,
                'is_active' => true,
            ]);

            // Создаем admin аккаунт с ролью owner
            $admin = $this->adminRepository->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($password),
                'role_id' => $ownerRole->id,
                'company_id' => $company->id,
                'status' => 1,
                'api_token' => Str::random(80),
            ]);

            // Отправляем приветственное письмо с данными для входа
            try {
                //Mail::queue(new WelcomeAdminNotification($admin, $password));
              //  Mail::send(new WelcomeAdminNotification($admin, $password));
                Mail::to('gorely.aleksei@yandex.ru')
                    ->sendNow( new WelcomeAdminNotification($admin, $password) );

               Mail::to($admin->email)
                    ->sendNow( new WelcomeAdminNotification($admin, $password) );



                Log::info('Welcome email queued for admin: ' . $admin->email . ' (Company: ' . $company->name . '   ('.$password.'))');
            } catch (\Exception $mailException) {
                Log::error('Failed to send welcome email: ' . $mailException->getMessage(), [
                    'trace' => $mailException->getTraceAsString(),
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email
                ]);
                // Продолжаем выполнение, даже если письмо не отправилось
            }

            // Отправляем уведомление администраторам о новом пользователе
            try {
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
                        Mail::to($superAdmin->email)->sendNow(new NewUserNotification($admin, $company->name, $request->plan ?? '', $password));
                        Log::info('New user notification sent to admin: ' . $superAdmin->email);
                    } catch (\Exception $notificationException) {
                        Log::error('Failed to send new user notification to admin: ' . $superAdmin->email, [
                            'trace' => $notificationException->getTraceAsString(),
                            'admin_id' => $superAdmin->id,
                            'new_user_id' => $admin->id
                        ]);
                        // Продолжаем отправку остальным администраторам
                    }
                }
            } catch (\Exception $notificationException) {
                Log::error('Failed to send new user notifications: ' . $notificationException->getMessage(), [
                    'trace' => $notificationException->getTraceAsString(),
                    'new_user_id' => $admin->id
                ]);
                // Продолжаем выполнение, даже если уведомления не отправились
            }

            return response()->json([
                'success' => true,
                'message' => 'Спасибо за регистрацию! Ваш аккаунт создан. Вы можете войти в админ панель используя ваш email и пароль, который был отправлен на вашу почту.'
            ]);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->except(['password'])
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при отправке заявки. Пожалуйста, попробуйте позже.'
            ], 500);
        }
    }

    /**
     * Activate admin account by token.
     * Note: Для админов активация не требуется, так как они создаются со status = 1
     * Этот метод оставлен для обратной совместимости, но может быть удален
     *
     * @param  string  $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activateAccount($token)
    {
        // Админы создаются сразу активными, поэтому просто редиректим на страницу входа в админ панель
        session()->flash('info', 'Ваш аккаунт уже активен. Пожалуйста, войдите в систему.');

        return redirect()->route('admin.session.create');
    }
}

