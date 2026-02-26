<?php

namespace Webkul\Newsletters\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Webkul\User\Repositories\AdminRepository;
use Webkul\User\Repositories\RoleRepository;
use Webkul\Newsletters\Repositories\CompanyRepository;
use Webkul\Newsletters\Models\RegistrationRequest;
use Webkul\Newsletters\Services\RegistrationPaymentService;
use Webkul\CMS\Repositories\PageRepository;

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
        protected CompanyRepository $companyRepository,
        protected PageRepository $pageRepository,
        protected RegistrationPaymentService $registrationPaymentService
    ) {}

    /**
     * Display the landing page.
     */
    public function index()
    {
        $page = $this->pageRepository
            ->whereHas('channels', function ($query) {
                $query->where('id', core()->getCurrentChannel()->id);
            })
            ->whereTranslation('url_key', 'home')
            ->first();

        if (!$page) {
            // Fallback to old view if CMS page doesn't exist
            return view('newsletters::landing.index');
        }

        return view('newsletters::landing.cms-page', compact('page'));
    }

    /**
     * Display payment terms page.
     */
    public function paymentTerms()
    {
        $page = $this->pageRepository
            ->whereHas('channels', function ($query) {
                $query->where('id', core()->getCurrentChannel()->id);
            })
            ->whereTranslation('url_key', 'payment-terms')
            ->first();

        if (!$page) {
            // Fallback to old view if CMS page doesn't exist
            return view('newsletters::landing.payment-terms');
        }

        return view('newsletters::landing.cms-static-page', compact('page'));
    }

    /**
     * Display privacy policy page.
     */
    public function privacyPolicy()
    {
        $page = $this->pageRepository
            ->whereHas('channels', function ($query) {
                $query->where('id', core()->getCurrentChannel()->id);
            })
            ->whereTranslation('url_key', 'privacy-policy')
            ->first();

        if (!$page) {
            // Fallback to old view if CMS page doesn't exist
            return view('newsletters::landing.privacy-policy');
        }

        return view('newsletters::landing.cms-static-page', compact('page'));
    }

    /**
     * Display public function oferta()
     * page.
     */
    public function oferta()
    {
        $page = $this->pageRepository
            ->whereHas('channels', function ($query) {
                $query->where('id', core()->getCurrentChannel()->id);
            })
            ->whereTranslation('url_key', 'oferta')
            ->first();

        if (!$page) {
            // Fallback to old view if CMS page doesn't exist
            return view('newsletters::landing.oferta');
        }

        return view('newsletters::landing.cms-static-page', compact('page'));
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
        Log::channel('single')->info('[Registration] Step 1: Form received', [
            'email' => $request->email,
            'name' => $request->name,
            'plan' => $request->plan ?? null,
        ]);

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
            Log::channel('single')->info('[Registration] Step 2: Validation failed', [
                'email' => $request->email,
                'errors' => $validator->errors()->toArray(),
            ]);
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
            Log::channel('single')->info('[Registration] Step 3: Email already exists', [
                'email' => $request->email,
                'exists_in_requests' => $emailExistsInRequests,
                'exists_in_admins' => (bool) $existingAdmin,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Пользователь с таким email уже зарегистрирован или подал заявку на регистрацию.',
                'errors' => ['email' => ['Пользователь с таким email уже зарегистрирован или подал заявку на регистрацию.']]
            ], 422);
        }

        Log::channel('single')->info('[Registration] Step 4: Creating company and admin...', [
            'email' => $request->email,
        ]);

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

            // Получаем роль с permission_type 'all' (роль для owners)
            $ownerRole = $this->roleRepository->findOneWhere(['permission_type' => 'all']);

            if (!$ownerRole) {
                Log::error('Owner role not found (role with permission_type "all")');
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

            // Создаем admin аккаунт с ролью owner (письмо с данными для входа отправится после оплаты)
            $admin = $this->adminRepository->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($password),
                'role_id' => $ownerRole->id,
                'company_id' => $company->id,
                'status' => 1,
                'api_token' => Str::random(80),
            ]);

            session(['registration_pending' => [
                'admin_id' => $admin->id,
                'created_at' => time(),
            ]]);

            $redirectUrl = route('newsletters.landing.register-payment');

            Log::channel('single')->info('[Registration] Step 5: Registration success, redirect to payment', [
                'admin_id' => $admin->id,
                'company_id' => $company->id,
                'redirect_url' => $redirectUrl,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Регистрация успешна. Пополните счёт для начала работы.',
                'redirect_url' => $redirectUrl,
            ]);
        } catch (\Exception $e) {
            Log::channel('single')->error('[Registration] Step 5 FAILED: Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->except(['password']),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при отправке заявки. Пожалуйста, попробуйте позже.'
            ], 500);
        }
    }

    /**
     * Display registration payment page (topup to start).
     */
    public function registerPayment(): View|RedirectResponse
    {
        Log::channel('single')->info('[Registration Payment] Step 6: Page view requested', [
            'has_session' => session()->has('registration_pending'),
            'session_data' => session('registration_pending'),
        ]);

        $pending = session('registration_pending');
        if (! $pending || empty($pending['admin_id'])) {
            Log::channel('single')->info('[Registration Payment] Step 6 FAILED: No pending session, redirect to index');
            return redirect()->route('newsletters.landing.index');
        }

        $ttl = 30 * 60;
        if (isset($pending['created_at']) && (time() - $pending['created_at']) > $ttl) {
            Log::channel('single')->info('[Registration Payment] Step 6 FAILED: Session expired', [
                'admin_id' => $pending['admin_id'] ?? null,
                'age_sec' => isset($pending['created_at']) ? time() - $pending['created_at'] : null,
            ]);
            session()->forget('registration_pending');
            return redirect()->route('newsletters.landing.index')
                ->with('info', 'Время сессии истекло. Пожалуйста, зарегистрируйтесь снова.');
        }

        Log::channel('single')->info('[Registration Payment] Step 6 OK: Showing payment form', [
            'admin_id' => $pending['admin_id'],
        ]);

        return view('newsletters::landing.register-payment');
    }

    /**
     * Create payment and redirect to bank.
     */
    public function createRegistrationPayment(Request $request): RedirectResponse
    {
        Log::channel('single')->info('[Registration Payment] Step 7: Create payment form submitted', [
            'amount' => $request->input('amount'),
            'has_session' => session()->has('registration_pending'),
        ]);

        $pending = session('registration_pending');
        if (! $pending || empty($pending['admin_id'])) {
            Log::channel('single')->info('[Registration Payment] Step 7 FAILED: No pending session');
            return redirect()->route('newsletters.landing.index')
                ->with('error', 'Сессия истекла. Пожалуйста, зарегистрируйтесь снова.');
        }

        $minAmount = (float) config('tochka-payment.min_amount', 1);
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric', 'min:' . $minAmount],
        ], [
            'amount.required' => 'Введите сумму пополнения.',
            'amount.min' => sprintf('Минимальная сумма: %s ₽', number_format($minAmount, 0, ',', ' ')),
        ]);

        if ($validator->fails()) {
            Log::channel('single')->info('[Registration Payment] Step 7: Validation failed', [
                'admin_id' => $pending['admin_id'],
                'errors' => $validator->errors()->toArray(),
            ]);
            return redirect()->route('newsletters.landing.register-payment')
                ->withErrors($validator)
                ->withInput();
        }

        $admin = $this->adminRepository->find($pending['admin_id']);
        if (! $admin) {
            Log::channel('single')->error('[Registration Payment] Step 7 FAILED: Admin not found', [
                'admin_id' => $pending['admin_id'],
            ]);
            session()->forget('registration_pending');
            return redirect()->route('newsletters.landing.index')
                ->with('error', 'Ошибка: пользователь не найден.');
        }

        Log::channel('single')->info('[Registration Payment] Step 8: Creating payment via Tochka...', [
            'admin_id' => $admin->id,
            'amount' => (float) $request->input('amount'),
        ]);

        try {
            $result = $this->registrationPaymentService->createPayment(
                $admin,
                (float) $request->input('amount')
            );

            Log::channel('single')->info('[Registration Payment] Step 9: Redirect to bank', [
                'admin_id' => $admin->id,
                'payment_url_length' => strlen($result['payment_url'] ?? ''),
            ]);

            return redirect()->away($result['payment_url']);
        } catch (\Throwable $e) {
            Log::channel('single')->error('[Registration Payment] Step 8 FAILED: Payment creation failed', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('newsletters.landing.register-payment')
                ->with('error', 'Не удалось создать платёж. Попробуйте позже.')
                ->withInput();
        }
    }

    /**
     * Display success page after payment.
     */
    public function registerPaymentSuccess(): View
    {
        Log::channel('single')->info('[Registration Payment] Step 10: Payment success page displayed');

        return view('newsletters::landing.register-payment-success');
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

