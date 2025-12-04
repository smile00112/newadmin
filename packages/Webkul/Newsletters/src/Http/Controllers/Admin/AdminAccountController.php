<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\CompanyRepository;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;
use Webkul\Newsletters\Repositories\AccountTopupRepository;

class AdminAccountController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CompanyRepository $companyRepository,
        protected CompanyAccountRepository $accountRepository,
        protected AccountTopupRepository $topupRepository
    ) {}

    /**
     * Display a listing of all company accounts.
     */
    public function index()
    {
        $admin = auth()->guard('admin')->user();
        
        // Only super admins (without company_id) can access
        if ($admin->company_id) {
            abort(403, trans('newsletters::app.admin.account.access-denied'));
        }

        $companies = $this->companyRepository->all();
        
        // Load accounts for each company
        foreach ($companies as $company) {
            $company->account = $this->accountRepository->getOrCreateForCompany($company->id);
        }

        return view('newsletters::admin.admin-accounts.index', compact('companies'));
    }

    /**
     * Handle account topup for a specific company.
     */
    public function topup(Request $request, int $companyId)
    {
        $admin = auth()->guard('admin')->user();
        
        // Only super admins (without company_id) can access
        if ($admin->company_id) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.account.access-denied'),
            ], 403);
        }

        $this->validate($request, [
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $company = $this->companyRepository->findOrFail($companyId);
        $account = $this->accountRepository->getOrCreateForCompany($company->id);

        // Create topup record
        $topup = $this->topupRepository->create([
            'account_id' => $account->id,
            'amount' => $request->amount,
            'admin_id' => $admin->id,
            'notes' => $request->notes,
        ]);

        // Add balance to account
        $this->accountRepository->addBalance($account->id, $request->amount);

        session()->flash('success', trans('newsletters::app.admin.account.topup-success'));

        return redirect()->route('admin.newsletters.admin-accounts.index');
    }
}

