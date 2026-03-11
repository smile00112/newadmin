<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Traits\HasNewsletterRole;
use Webkul\User\Repositories\AdminRepository;
use Webkul\User\Repositories\RoleRepository;

class ManagerController extends Controller
{
    use HasNewsletterRole;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AdminRepository $adminRepository,
        protected RoleRepository $roleRepository
    ) {}

    /**
     * Display a listing of managers for current company.
     */
    public function index()
    {
        $this->requireNewsletterPermission('newsletters.managers');
        
        $admin = auth()->guard('admin')->user();
        
        $managers = $this->adminRepository
            ->where('company_id', $admin->company_id)
            ->where('id', '!=', $admin->id) // Исключить текущего админа
            ->with('role')
            ->get();

        return view('newsletters::admin.managers.index', compact('managers'));
    }

    /**
     * Show the form for creating a new manager.
     */
    public function create()
    {
        $this->requireNewsletterPermission('newsletters.managers.create');
        
        // Получить все роли с permission_type 'custom' (не owner роли)
        $roles = $this->roleRepository
            ->where('permission_type', 'custom')
            ->get();

        return view('newsletters::admin.managers.create', compact('roles'));
    }

    /**
     * Store a newly created manager.
     */
    public function store(Request $request)
    {
        $this->requireNewsletterPermission('newsletters.managers.create');
        
        $admin = auth()->guard('admin')->user();
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Убедиться, что роль имеет permission_type 'custom'
        $role = $this->roleRepository->findOrFail($data['role_id']);
        if ($role->permission_type === 'all') {
            abort(403, trans('newsletters::app.admin.errors.cannot-assign-owner-role'));
        }

        // Привязать менеджера к компании владельца
        $data['company_id'] = $admin->company_id;
        $data['status'] = 1;
        $data['password'] = Hash::make($data['password']);

        $this->adminRepository->create($data);

        session()->flash('success', trans('newsletters::app.admin.managers.create-success'));

        return redirect()->route('admin.newsletters.managers.index');
    }

    /**
     * Show the form for editing the specified manager.
     */
    public function edit(int $id)
    {
        $this->requireNewsletterPermission('newsletters.managers.edit');
        
        $admin = auth()->guard('admin')->user();
        $manager = $this->adminRepository->findOrFail($id);

        // Super admin может редактировать любого менеджера
        if (! $this->isSuperAdmin($admin)) {
            if ($manager->company_id !== $admin->company_id) {
                abort(403, trans('newsletters::app.admin.errors.different-company'));
            }
        }

        // Нельзя редактировать владельца
        if ($manager->role->permission_type === 'all') {
            abort(403, trans('newsletters::app.admin.errors.cannot-edit-owner'));
        }

        $roles = $this->roleRepository
            ->where('permission_type', 'custom')
            ->get();

        return view('newsletters::admin.managers.edit', compact('manager', 'roles'));
    }

    /**
     * Update the specified manager.
     */
    public function update(Request $request, int $id)
    {
        $this->requireNewsletterPermission('newsletters.managers.edit');
        
        $admin = auth()->guard('admin')->user();
        $manager = $this->adminRepository->findOrFail($id);

        // Super admin может обновлять любого менеджера
        if (! $this->isSuperAdmin($admin)) {
            if ($manager->company_id !== $admin->company_id) {
                abort(403, trans('newsletters::app.admin.errors.different-company'));
            }
        }

        // Нельзя редактировать владельца
        if ($manager->role->permission_type === 'all') {
            abort(403, trans('newsletters::app.admin.errors.cannot-edit-owner'));
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $id,
            'password' => 'nullable|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'status' => 'boolean',
        ]);

        // Убедиться, что роль имеет permission_type 'custom'
        $role = $this->roleRepository->findOrFail($data['role_id']);
        if ($role->permission_type === 'all') {
            abort(403, trans('newsletters::app.admin.errors.cannot-assign-owner-role'));
        }

        // Хешировать пароль, если он указан
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $this->adminRepository->update($data, $id);

        session()->flash('success', trans('newsletters::app.admin.managers.update-success'));

        return redirect()->route('admin.newsletters.managers.index');
    }

    /**
     * Remove the specified manager.
     */
    public function destroy(int $id)
    {
        $this->requireNewsletterPermission('newsletters.managers.delete');
        
        $admin = auth()->guard('admin')->user();
        $manager = $this->adminRepository->findOrFail($id);

        // Super admin может удалять любого менеджера
        if (! $this->isSuperAdmin($admin)) {
            if ($manager->company_id !== $admin->company_id) {
                abort(403, trans('newsletters::app.admin.errors.different-company'));
            }
        }

        // Нельзя удалить владельца
        if ($manager->role->permission_type === 'all') {
            abort(403, trans('newsletters::app.admin.errors.cannot-delete-owner'));
        }

        // Нельзя удалить самого себя
        if ($manager->id === $admin->id) {
            abort(403, trans('newsletters::app.admin.errors.cannot-delete-self'));
        }

        try {
            $this->adminRepository->delete($id);

            return response()->json([
                'message' => trans('newsletters::app.admin.managers.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('newsletters::app.admin.managers.delete-failed'),
            ], 500);
        }
    }

    /**
     * Update manager permissions.
     */
    public function updatePermissions(Request $request, int $id)
    {
        $this->requireNewsletterPermission('newsletters.managers.edit');
        
        $admin = auth()->guard('admin')->user();
        $manager = $this->adminRepository->findOrFail($id);

        // Super admin может обновлять разрешения любого менеджера
        if (! $this->isSuperAdmin($admin)) {
            if ($manager->company_id !== $admin->company_id) {
                abort(403, trans('newsletters::app.admin.errors.different-company'));
            }
        }

        // Обновить разрешения роли менеджера
        $role = $manager->role;
        $permissions = $request->input('permissions', []);
        
        // Фильтровать только разрешения newsletters
        $newsletterPermissions = array_filter($permissions, function($permission) {
            return strpos($permission, 'newsletters.') === 0;
        });
        
        $role->permissions = array_values($newsletterPermissions);
        $role->save();

        session()->flash('success', trans('newsletters::app.admin.managers.permissions-updated'));

        return redirect()->route('admin.newsletters.managers.index');
    }

    /**
     * Determine whether admin is super admin.
     */
    protected function isSuperAdmin($admin): bool
    {
        return $admin
            && $admin->role
            && $admin->role->permission_type === 'all'
            && ! $admin->company_id;
    }
}

