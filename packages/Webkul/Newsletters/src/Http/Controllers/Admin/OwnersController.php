<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Traits\HasNewsletterRole;
use Webkul\User\Repositories\AdminRepository;
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

        return view('newsletters::admin.owners.edit', compact('owner'));
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
            'notes' => 'nullable|string|max:500',
        ]);

        $admin = auth()->guard('admin')->user();
        $account = $this->accountRepository->getOrCreateForCompany($owner->company_id);

        // Create topup record
        $topup = $this->topupRepository->create([
            'account_id' => $account->id,
            'amount' => $request->amount,
            'admin_id' => $admin->id,
            'notes' => $request->notes ?? 'Top up by admin',
        ]);

        // Add balance to account
        $this->accountRepository->addBalance($account->id, $request->amount);

        session()->flash('success', trans('newsletters::app.admin.owners.topup-success'));

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

