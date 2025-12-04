<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;
use Webkul\Newsletters\Repositories\AccountTopupRepository;
use Webkul\Newsletters\Traits\HasNewsletterRole;

class AccountController extends Controller
{
    use HasNewsletterRole;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CompanyAccountRepository $accountRepository,
        protected AccountTopupRepository $topupRepository
    ) {}

    /**
     * Display the account page.
     */
    public function index()
    {
        $this->requireNewsletterPermission('newsletters.account.view');
        
        $admin = auth()->guard('admin')->user();
        
        if (!$admin->company_id) {
            abort(403, trans('newsletters::app.admin.account.no-company'));
        }

        $account = $this->accountRepository->getOrCreateForCompany($admin->company_id);
        
        $topups = $this->topupRepository
            ->where('account_id', $account->id)
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('newsletters::admin.account.index', compact('account', 'topups'));
    }

    /**
     * Handle account topup.
     */
    public function topup(Request $request)
    {
        $this->requireNewsletterPermission('newsletters.account.topup');
        
        $admin = auth()->guard('admin')->user();
        
        if (!$admin->company_id) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.account.no-company'),
            ], 403);
        }

        $this->validate($request, [
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $account = $this->accountRepository->getOrCreateForCompany($admin->company_id);

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

        return redirect()->route('admin.newsletters.account.index');
    }
}

