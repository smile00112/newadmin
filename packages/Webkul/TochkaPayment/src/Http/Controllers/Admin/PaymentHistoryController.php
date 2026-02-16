<?php

declare(strict_types=1);

namespace Webkul\TochkaPayment\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\CompanyRepository;
use Webkul\TochkaPayment\Models\TochkaPaymentHistory;

final class PaymentHistoryController extends Controller
{
    /**
     * Company repository instance.
     *
     * @var \Webkul\Newsletters\Repositories\CompanyRepository
     */
    protected $companyRepository;

    /**
     * Create a new controller instance.
     */
    public function __construct(CompanyRepository $companyRepository = null)
    {
        $this->companyRepository = $companyRepository ?? app(CompanyRepository::class);
    }

    /**
     * Display the payment history list.
     */
    public function index(Request $request): View
    {
        $admin = auth()->guard('admin')->user();
        $companies = collect();
        $companyId = null;
        $isSuperAdmin = false;

        if ($admin) {
            if ($admin->role && $admin->role->permission_type === 'all' && !$admin->company_id) {
                $isSuperAdmin = true;
                $companies = $this->companyRepository->all();
                $companyId = $request->integer('company_id') ?: null;
                if (!$companyId && $companies->isNotEmpty()) {
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

        $payments = $companyId
            ? TochkaPaymentHistory::forCompany($companyId)->orderByDesc('created_at')->paginate(20)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);

        return view('tochka-payment::admin.payment-history.index', compact('payments', 'companies', 'companyId', 'isSuperAdmin'));
    }

    /**
     * Display a single payment.
     */
    public function show(int $id): View
    {
        $payment = TochkaPaymentHistory::find($id);

        if (!$payment) {
            throw new NotFoundHttpException();
        }

        $admin = auth()->guard('admin')->user();
        if ($admin) {
            $isSuperAdmin = $admin->role && $admin->role->permission_type === 'all' && !$admin->company_id;
            if (!$isSuperAdmin && (int) $payment->company_id !== (int) $admin->company_id) {
                throw new NotFoundHttpException();
            }
        } else {
            throw new NotFoundHttpException();
        }

        return view('tochka-payment::admin.payment-history.show', compact('payment'));
    }
}
