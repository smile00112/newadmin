<?php

declare(strict_types=1);

namespace Webkul\TochkaPayment\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Models\CoreConfig;
use Webkul\Newsletters\Mail\WelcomeAdminNotification;
use Webkul\Newsletters\Models\AccountTopup;
use Webkul\Newsletters\Repositories\AccountTopupRepository;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;
use Webkul\Newsletters\Repositories\CompanyRepository;
use Webkul\TochkaPayment\Models\TochkaPaymentHistory;
use Webkul\TochkaPayment\Models\TochkaPaymentBuyer;
use Webkul\User\Models\Admin;
use Webkul\User\Repositories\AdminRepository;
use Webkul\User\Repositories\RoleRepository;

final class BuyersController extends Controller
{
    protected $companyRepository;

    public function __construct(
        ?CompanyRepository $companyRepository = null,
        protected ?AdminRepository $adminRepository = null,
        protected ?RoleRepository $roleRepository = null,
        protected ?CompanyAccountRepository $accountRepository = null,
        protected ?AccountTopupRepository $topupRepository = null
    ) {
        $this->companyRepository = $companyRepository ?? app(CompanyRepository::class);
        $this->adminRepository = $adminRepository ?? app(AdminRepository::class);
        $this->roleRepository = $roleRepository ?? app(RoleRepository::class);
        $this->accountRepository = $accountRepository ?? app(CompanyAccountRepository::class);
        $this->topupRepository = $topupRepository ?? app(AccountTopupRepository::class);
    }

    /**
     * Display the buyers list.
     */
    public function index(Request $request): View
    {
        $admin = auth()->guard('admin')->user();
        $companies = collect();
        $companyId = null;
        $isSuperAdmin = false;

        if ($admin) {
            if ($admin->role && $admin->role->permission_type === 'all' && ! $admin->company_id) {
                $isSuperAdmin = true;
                $companies = $this->companyRepository->all();
                $companyId = $request->integer('company_id') ?: null;
                if (! $companyId && $companies->isNotEmpty()) {
                    $companyId = (int) $companies->first()->id;
                }
            } elseif ($admin->company_id) {
                $company = $this->companyRepository->find($admin->company_id);
                if ($company) {
                    $companies = collect([$company]);
                    $companyId = (int) $admin->company_id;
                }
            }
        }

        $buyers = $companyId
            ? TochkaPaymentBuyer::forCompany($companyId)->with(['company', 'owner'])->orderByDesc('created_at')->paginate(20)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);

        return view('tochka-payment::admin.buyers.index', compact('buyers', 'companies', 'companyId', 'isSuperAdmin'));
    }

    /**
     * Create owner account from buyer data.
     */
    public function createOwner(Request $request, int $id): RedirectResponse
    {
        $currentAdmin = auth()->guard('admin')->user();

        if (! $currentAdmin) {
            abort(403, __('admin::app.error.403.message'));
        }

        $buyer = TochkaPaymentBuyer::query()->findOrFail($id);

        if (! $this->canManageBuyer($currentAdmin, $buyer)) {
            abort(403, __('admin::app.error.403.message'));
        }

        if ($buyer->owner_id) {
            session()->flash('warning', trans('tochka-payment::app.admin.buyers.create-owner.already-linked'));

            return redirect()->back();
        }

        if (empty($buyer->client_email) || ! filter_var($buyer->client_email, FILTER_VALIDATE_EMAIL)) {
            session()->flash('error', trans('tochka-payment::app.admin.buyers.create-owner.invalid-email'));

            return redirect()->back();
        }

        $ownerRole = $this->roleRepository->findOneWhere(['permission_type' => 'all']);

        if (! $ownerRole) {
            session()->flash('error', trans('tochka-payment::app.admin.buyers.create-owner.role-not-found'));

            return redirect()->back();
        }

        if ($this->adminRepository->findOneWhere(['email' => $buyer->client_email])) {
            session()->flash('error', trans('tochka-payment::app.admin.buyers.create-owner.email-exists'));

            return redirect()->back();
        }

        $generatedPassword = Str::random(12);
        $owner = null;

        DB::beginTransaction();

        try {
            $owner = $this->adminRepository->create([
                'name' => $buyer->client_name ?: $buyer->client_email,
                'email' => $buyer->client_email,
                'password' => Hash::make($generatedPassword),
                'role_id' => $ownerRole->id,
                'company_id' => $buyer->company_id,
                'status' => 1,
                'api_token' => Str::random(80),
            ]);

            $buyer->owner_id = $owner->id;
            $buyer->save();

            $this->importBuyerPayments($buyer, (int) $owner->company_id, (int) $currentAdmin->id);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to create owner from buyer', [
                'buyer_id' => $buyer->id,
                'buyer_email' => $buyer->client_email,
                'company_id' => $buyer->company_id,
                'error' => $exception->getMessage(),
            ]);

            session()->flash('error', trans('tochka-payment::app.admin.buyers.create-owner.failed'));

            return redirect()->back();
        }

        $this->sendCredentialsToRegistrationNotificationEmails($owner, $generatedPassword);

        session()->flash('success', trans('tochka-payment::app.admin.buyers.create-owner.success', [
            'email' => $owner->email,
        ]));

        return redirect()->route('admin.tochka-payment.buyers.index', [
            'company_id' => $buyer->company_id,
        ]);
    }

    private function canManageBuyer(Admin $admin, TochkaPaymentBuyer $buyer): bool
    {
        if ($admin->role && $admin->role->permission_type === 'all' && ! $admin->company_id) {
            return true;
        }

        return (int) $admin->company_id === (int) $buyer->company_id;
    }

    private function importBuyerPayments(TochkaPaymentBuyer $buyer, int $companyId, int $adminId): void
    {
        $account = $this->accountRepository->getOrCreateForCompany($companyId);

        $payments = TochkaPaymentHistory::query()
            ->where('company_id', $companyId)
            ->where('status', TochkaPaymentHistory::STATUS_PAID)
            ->where(function ($query) use ($buyer) {
                if (! empty($buyer->consumer_id)) {
                    $query->where('consumer_id', $buyer->consumer_id);

                    return;
                }

                $query->where('client_email', $buyer->client_email);
            })
            ->orderBy('created_at')
            ->get();

        foreach ($payments as $payment) {
            $paymentAmount = (float) $payment->amount;

            if ($paymentAmount <= 0) {
                continue;
            }

            $paymentDate = $payment->updated_at ?? $payment->created_at ?? now();

            $this->topupRepository->create([
                'account_id' => $account->id,
                'type' => AccountTopup::TYPE_TOPUP,
                'amount' => $paymentAmount,
                'transaction_date' => $paymentDate,
                'admin_id' => $adminId,
                'notes' => 'Перенос оплаты из Tochka Payment ID=' . $payment->id,
            ]);

            $this->accountRepository->addBalance($account->id, $paymentAmount);

            $deductionPercent = mt_rand(500, 1000) / 100;
            $deductionAmount = round($paymentAmount * $deductionPercent / 100, 2);

            if ($deductionAmount <= 0) {
                continue;
            }

            $this->topupRepository->create([
                'account_id' => $account->id,
                'type' => AccountTopup::TYPE_DEDUCTION,
                'amount' => $deductionAmount,
                'transaction_date' => $paymentDate->copy()->addDay(),
                'admin_id' => $adminId,
                'notes' => 'Списание ' . number_format($deductionPercent, 2, '.', '') . '% от оплаты Tochka Payment ID=' . $payment->id,
            ]);

            $this->accountRepository->deductBalance($account->id, $deductionAmount);
        }
    }

    private function sendCredentialsToRegistrationNotificationEmails(Admin $owner, string $password): void
    {
        $config = CoreConfig::query()
            ->where('code', 'registration.notifications.emails')
            ->whereNull('channel_code')
            ->whereNull('locale_code')
            ->first();

        $emails = [];

        if ($config && ! empty($config->value)) {
            $emails = array_map('trim', explode(',', $config->value));
            $emails = array_filter($emails, static function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });
        }

        $emails = array_unique($emails);

        foreach ($emails as $email) {
            try {
                Mail::to($email)->sendNow(new WelcomeAdminNotification($owner, $password));
            } catch (\Throwable $exception) {
                Log::error('Failed to send owner credentials notification', [
                    'recipient' => $email,
                    'owner_id' => $owner->id,
                    'buyer_owner_email' => $owner->email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}
