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

            return response()->json([
                'success' => true,
                'message' => 'Регистрация успешна. Пополните счёт для начала работы.',
                'redirect_url' => route('newsletters.landing.register-payment'),
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
     * Display registration payment page (topup to start).
     */
    public function registerPayment(): View|RedirectResponse
    {
        $pending = session('registration_pending');
        if (! $pending || empty($pending['admin_id'])) {
            return redirect()->route('newsletters.landing.index');
        }

        $ttl = 30 * 60;
        if (isset($pending['created_at']) && (time() - $pending['created_at']) > $ttl) {
            session()->forget('registration_pending');
            return redirect()->route('newsletters.landing.index')
                ->with('info', 'Время сессии истекло. Пожалуйста, зарегистрируйтесь снова.');
        }

        return view('newsletters::landing.register-payment');
    }

    /**
     * Create payment and redirect to bank.
     */
    public function createRegistrationPayment(Request $request): RedirectResponse
    {
        $pending = session('registration_pending');
        if (! $pending || empty($pending['admin_id'])) {
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
            return redirect()->route('newsletters.landing.register-payment')
                ->withErrors($validator)
                ->withInput();
        }

        $admin = $this->adminRepository->find($pending['admin_id']);
        if (! $admin) {
            session()->forget('registration_pending');
            return redirect()->route('newsletters.landing.index')
                ->with('error', 'Ошибка: пользователь не найден.');
        }

        try {
            $result = $this->registrationPaymentService->createPayment(
                $admin,
                (float) $request->input('amount')
            );
            return redirect()->away($result['payment_url']);
        } catch (\Throwable $e) {
            Log::error('Registration payment creation failed', [
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

