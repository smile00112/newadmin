<?php

declare(strict_types=1);

namespace Webkul\TochkaPayment\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\CompanyRepository;
use Webkul\TochkaPayment\Models\TochkaPaymentBuyer;

final class BuyersController extends Controller
{
    protected $companyRepository;

    public function __construct(?CompanyRepository $companyRepository = null)
    {
        $this->companyRepository = $companyRepository ?? app(CompanyRepository::class);
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
            ? TochkaPaymentBuyer::forCompany($companyId)->with('company')->orderByDesc('created_at')->paginate(20)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);

        return view('tochka-payment::admin.buyers.index', compact('buyers', 'companies', 'companyId', 'isSuperAdmin'));
    }
}
