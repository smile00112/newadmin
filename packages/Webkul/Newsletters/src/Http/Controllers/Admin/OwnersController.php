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
     * Display a listing of all company owners.
     */
    public function index()
    {
        $this->requireNewsletterPermission('newsletters.owners.view');

        // Получить всех админов с ролью permission_type 'all' и company_id (owners)
        $owners = $this->adminRepository
            ->getModel()
            ->leftJoin('roles', 'admins.role_id', '=', 'roles.id')
            ->where('roles.permission_type', 'all')
            ->whereNotNull('admins.company_id')
            ->select('admins.*')
            ->get()
            ->load(['role', 'company', 'company.account']);

        return view('newsletters::admin.owners.index', compact('owners'));
    }

    /**
     * Show the form for creating a new owner.
     */
    public function create()
    {
        $this->requireNewsletterPermission('newsletters.owners.create');

        // Получить все компании для выбора
        $companies = $this->companyRepository->all();

        return view('newsletters::admin.owners.create', compact('companies'));
    }

    /**
     * Store a newly created owner.
     */
    public function store(Request $request)
    {
        $this->requireNewsletterPermission('newsletters.owners.create');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|min:6|confirmed',
            'company_option' => 'required|in:existing,new',
            'company_id' => 'required_if:company_option,existing|exists:companies,id',
            'company_name' => 'required_if:company_option,new|string|max:255',
            'company_description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        // Получаем роль с permission_type 'all' (роль для owners)
        $ownerRole = $this->roleRepository->findOneWhere(['permission_type' => 'all']);

        if (!$ownerRole) {
            session()->flash('error', trans('newsletters::app.admin.owners.role-not-found'));
            return redirect()->back()->withInput();
        }

        // Определяем компанию
        if ($data['company_option'] === 'new') {
            // Создаем новую компанию
            $companySlug = Str::slug($data['company_name']);

            // Проверяем уникальность slug
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
        } else {
            // Используем существующую компанию
            $company = $this->companyRepository->findOrFail($data['company_id']);
        }

        // Создаем admin аккаунт с ролью owner
        $admin = $this->adminRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $ownerRole->id,
            'company_id' => $company->id,
            'status' => $request->has('status') ? (bool) $request->input('status') : 1,
            'api_token' => Str::random(80),
        ]);

        // Отправляем приветственное письмо с данными для входа
        try {
            Log::info('Sending welcome email notification (admin created)', [
                'recipient' => $admin->email,
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'company_id' => $company->id,
                'company_name' => $company->name,
                'created_by' => auth()->guard('admin')->user()->id ?? null,
            ]);

            Mail::to($admin->email)
                ->send(new WelcomeAdminNotification($admin, $data['password']));

            Log::info('Welcome email notification sent successfully (admin created)', [
                'recipient' => $admin->email,
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'company_name' => $company->name,
            ]);
        } catch (\Exception $mailException) {
            Log::error('Failed to send welcome email notification (admin created)', [
                'recipient' => $admin->email ?? null,
                'error' => $mailException->getMessage(),
                'trace' => $mailException->getTraceAsString(),
                'admin_id' => $admin->id ?? null,
                'admin_email' => $admin->email ?? null,
                'company_id' => $company->id ?? null,
                'company_name' => $company->name ?? null,
            ]);
            // Продолжаем выполнение, даже если письмо не отправилось
        }

        session()->flash('success', trans('newsletters::app.admin.owners.create-success'));

        return redirect()->route('admin.newsletters.owners.index');
    }

    /**
     * Show the form for editing the specified owner.
     */
    public function edit(int $id)
    {
        $this->requireNewsletterPermission('newsletters.owners.edit');

        $owner = $this->adminRepository->findOrFail($id);

        // Проверка, что это owner (имеет permission_type 'all' и company_id)
        if (!$owner->role || $owner->role->permission_type !== 'all' || !$owner->company_id) {
            abort(404, trans('newsletters::app.admin.owners.not-found'));
        }

        $owner->load(['role', 'company', 'company.account']);

        // Load transaction history for the account
        $transactions = collect();
        if ($owner->company && $owner->company->account) {
            $transactions = $this->topupRepository
                ->where('account_id', $owner->company->account->id)
                ->with('admin')
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('newsletters::admin.owners.edit', compact('owner', 'transactions'));
    }

    /**
     * Update the specified owner.
     */
    public function update(Request $request, int $id)
    {
        $this->requireNewsletterPermission('newsletters.owners.edit');

        $owner = $this->adminRepository->findOrFail($id);

        // Проверка, что это owner
        if (!$owner->role || $owner->role->permission_type !== 'all' || !$owner->company_id) {
            abort(404, trans('newsletters::app.admin.owners.not-found'));
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $id,
            'status' => 'boolean',
        ]);

        $this->adminRepository->update($data, $id);

        session()->flash('success', trans('newsletters::app.admin.owners.update-success'));

        return redirect()->route('admin.newsletters.owners.index');
    }

    /**
     * Toggle owner status (enable/disable).
     */
    public function toggleStatus(int $id)
    {
        $this->requireNewsletterPermission('newsletters.owners.toggle-status');

        $owner = $this->adminRepository->findOrFail($id);

        // Проверка, что это owner
        if (!$owner->role || $owner->role->permission_type !== 'all' || !$owner->company_id) {
            abort(404, trans('newsletters::app.admin.owners.not-found'));
        }

        $owner->status = !$owner->status;
        $owner->save();

        $message = $owner->status
            ? trans('newsletters::app.admin.owners.enabled-success')
            : trans('newsletters::app.admin.owners.disabled-success');

        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $owner->status,
        ]);
    }

    /**
     * Top up account for owner's company.
     */
    public function topup(Request $request, int $id)
    {
        $this->requireNewsletterPermission('newsletters.owners.topup');

        $owner = $this->adminRepository->findOrFail($id);

        // Проверка, что это owner
        if (!$owner->role || $owner->role->permission_type !== 'all' || !$owner->company_id) {
            abort(404, trans('newsletters::app.admin.owners.not-found'));
        }

        $this->validate($request, [
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'create_deductions' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $admin = auth()->guard('admin')->user();
        $account = $this->accountRepository->getOrCreateForCompany($owner->company_id);

        // Parse transaction date (datetime-local format: Y-m-d\TH:i)
        $transactionDate = \Carbon\Carbon::parse($request->transaction_date);
        
        $createDeductions = $request->boolean('create_deductions');

        // If create_deductions is checked, create deduction records before the transaction date
        if ($createDeductions) {
            $messagesCount = floor($request->amount / 2);
            
            if ($messagesCount > 0) {
                // Randomly choose number of groups (mailing lists) from 1 to 3
                $groupsCount = rand(1, min(3, $messagesCount));
                
                // Distribute messages randomly between groups
                $groups = array_fill(0, $groupsCount, 0);
                $remainingMessages = $messagesCount;
                
                // Distribute messages randomly
                for ($i = 0; $i < $remainingMessages; $i++) {
                    $randomGroup = rand(0, $groupsCount - 1);
                    $groups[$randomGroup]++;
                }
                
                // Create deduction records for each group
                foreach ($groups as $index => $groupMessagesCount) {
                    if ($groupMessagesCount > 0) {
                        // Deduction date is 1 day before transaction date
                        $deductionDate = $transactionDate->copy()->subDay();
                        // Add random time between 00:00 and 23:59, different for each group
                        $deductionDate->setTime(rand(0, 23), rand(0, 59), rand(0, 59));
                        
                        // Random mailing list ID between 15 and 100
                        $mailingListId = rand(15, 100);
                        
                        // Calculate total amount for this group
                        $groupAmount = $groupMessagesCount * 2.00;
                        
                        // Create deduction record
                        $this->topupRepository->create([
                            'account_id' => $account->id,
                            'type' => \Webkul\Newsletters\Models\AccountTopup::TYPE_DEDUCTION,
                            'amount' => $groupAmount,
                            'transaction_date' => $deductionDate,
                            'admin_id' => $admin->id,
                            'notes' => "Списание за рассылку ID={$mailingListId}. Отправлено {$groupMessagesCount} сообщений",
                        ]);
                        
                        // Deduct balance
                        $this->accountRepository->deductBalance($account->id, $groupAmount);
                    }
                }
            }
        }

        // Create topup record
        $topup = $this->topupRepository->create([
            'account_id' => $account->id,
            'type' => \Webkul\Newsletters\Models\AccountTopup::TYPE_TOPUP,
            'amount' => $request->amount,
            'transaction_date' => $transactionDate,
            'admin_id' => $admin->id,
            'notes' => $request->notes ?? 'Top up by admin',
        ]);

        // Add balance to account
        $this->accountRepository->addBalance($account->id, $request->amount);

        session()->flash('success', trans('newsletters::app.admin.owners.topup-success'));

        return redirect()->route('admin.newsletters.owners.edit', $id);
    }

    /**
     * Resend registration email notification to owner.
     */
    public function resendRegistrationEmail(int $id)
    {
        $this->requireNewsletterPermission('newsletters.owners.edit');

        $owner = $this->adminRepository->findOrFail($id);

        // Проверка, что это owner
        if (!$owner->role || $owner->role->permission_type !== 'all' || !$owner->company_id) {
            abort(404, trans('newsletters::app.admin.owners.not-found'));
        }

        try {
            // Отправляем копию приветственного письма
            // Используем placeholder для пароля, так как оригинальный пароль недоступен
            Log::info('Resending registration email notification', [
                'recipient' => $owner->email,
                'admin_id' => $owner->id,
                'admin_name' => $owner->name,
                'company_id' => $owner->company_id,
                'company_name' => $owner->company ? $owner->company->name : 'N/A',
                'resend_by' => auth()->guard('admin')->user()->id ?? null,
            ]);

            Mail::to($owner->email)
                ->sendNow(new WelcomeAdminNotification($owner, 'Используйте ваш существующий пароль'));

            Log::info('Registration email notification resent successfully', [
                'recipient' => $owner->email,
                'admin_id' => $owner->id,
                'admin_email' => $owner->email,
                'company_name' => $owner->company ? $owner->company->name : 'N/A',
            ]);

            session()->flash('success', trans('newsletters::app.admin.owners.email-resent-success'));
        } catch (\Exception $mailException) {
            Log::error('Failed to resend registration email notification', [
                'recipient' => $owner->email ?? null,
                'error' => $mailException->getMessage(),
                'trace' => $mailException->getTraceAsString(),
                'admin_id' => $owner->id ?? null,
                'admin_email' => $owner->email ?? null,
                'company_id' => $owner->company_id ?? null,
                'company_name' => $owner->company ? $owner->company->name : 'N/A',
            ]);

            session()->flash('error', trans('newsletters::app.admin.owners.email-resent-failed'));
        }

        return redirect()->route('admin.newsletters.owners.index');
    }

    /**
     * Remove the specified owner (soft delete or disable).
     */
    public function destroy(int $id)
    {
        $this->requireNewsletterPermission('newsletters.owners.delete');

        $owner = $this->adminRepository->findOrFail($id);

        // Проверка, что это owner
        if (!$owner->role || $owner->role->permission_type !== 'all' || !$owner->company_id) {
            abort(404, trans('newsletters::app.admin.owners.not-found'));
        }

        // Нельзя удалить самого себя
        $currentAdmin = auth()->guard('admin')->user();
        if ($owner->id === $currentAdmin->id) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.owners.cannot-delete-self'),
            ], 403);
        }

        try {
            // Вместо удаления отключаем owner
            $owner->status = 0;
            $owner->save();

            return response()->json([
                'success' => true,
                'message' => trans('newsletters::app.admin.owners.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.owners.delete-failed'),
            ], 500);
        }
    }
}

