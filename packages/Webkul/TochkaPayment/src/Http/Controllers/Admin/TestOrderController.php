<?php

declare(strict_types=1);

namespace Webkul\TochkaPayment\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\CompanyRepository;
use Webkul\TochkaPayment\Services\SettingsService;
use Webkul\TochkaPayment\Services\TestPaymentService;

final class TestOrderController extends Controller
{
    /**
     * Company repository instance.
     *
     * @var \Webkul\Newsletters\Repositories\CompanyRepository
     */
    protected $companyRepository;

    /**
     * Settings service instance.
     *
     * @var \Webkul\TochkaPayment\Services\SettingsService
     */
    protected $settingsService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        CompanyRepository $companyRepository = null,
        SettingsService $settingsService = null
    ) {
        $this->companyRepository = $companyRepository ?? app(CompanyRepository::class);
        $this->settingsService = $settingsService ?? new SettingsService();
    }

    /**
     * Display the test order form.
     */
    public function index(): View
    {
        $payment = session('payment');
        $success = session('success');

        // Get companies for dropdown
        $admin = auth()->guard('admin')->user();
        $companies = collect();

        if ($admin) {
            // Admin with permission_type = all sees all companies
            if ($admin->role && $admin->role->permission_type === 'all' && !$admin->company_id) {
                $companies = $this->companyRepository->all();
            }
            // Owner sees only their company
            elseif ($admin->company_id) {
                $company = $this->companyRepository->find($admin->company_id);
                if ($company) {
                    $companies = collect([$company]);
                }
            }
        }

        $defaults = [
            'name' => trans('tochka-payment::app.admin.test-order.index.name-placeholder'),
            'email' => trans('tochka-payment::app.admin.test-order.index.email-placeholder'),
            'phone' => trans('tochka-payment::app.admin.test-order.index.phone-placeholder'),
            'purpose' => trans('tochka-payment::app.admin.test-order.index.purpose-placeholder'),
            'amount' => trans('tochka-payment::app.admin.test-order.index.amount-placeholder'),
            'external_order_id' => trans('tochka-payment::app.admin.test-order.index.external-order-id-placeholder'),
        ];

        return view('tochka-payment::admin.test-order.index', compact('payment', 'success', 'companies', 'defaults'));
    }

    /**
     * Create a test payment and redirect back with result.
     */
    public function store(Request $request): RedirectResponse
    {
        // Get minimum amount from settings
        $companyId = $request->input('company_id');
        $settings = $this->settingsService->getSettings($companyId);
        $minAmount = $settings['min_amount'] ?? config('tochka-payment.min_amount', 1.00);

        $validated = $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'amount' => 'required|numeric|min:' . $minAmount,
            'external_order_id' => 'nullable|string|max:255',
        ]);

        try {
            $testPaymentService = new TestPaymentService();
            $result = $testPaymentService->processTestPayment($validated);

            return redirect()
                ->route('admin.tochka-payment.test-order.index')
                ->with('payment', $result['payment'])
                ->with('success', trans('tochka-payment::app.admin.test-order.index.created'));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.tochka-payment.test-order.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
