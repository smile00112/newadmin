<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\DataGrids\Settings\RolesDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\User\Repositories\AdminRepository;
use Webkul\User\Repositories\RoleRepository;

class RoleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected RoleRepository $roleRepository,
        protected AdminRepository $adminRepository
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(RolesDataGrid::class)->process();
        }

        return view('admin::settings.roles.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin::settings.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'name'            => 'required',
            'permission_type' => 'required|in:all,custom',
            'description'     => 'required',
        ]);

        if (request('permission_type') == 'custom') {
            $this->validate(request(), [
                'permissions' => 'required',
            ]);
        }

        Event::dispatch('user.role.create.before');

        $data = request()->only([
            'name',
            'description',
            'permission_type',
            'permissions',
        ]);

        $role = $this->roleRepository->create($data);

        Event::dispatch('user.role.create.after', $role);

        return new JsonResponse([
            'message' => trans('admin::app.settings.roles.create-success'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        $role = $this->roleRepository->findOrFail($id);

        return new JsonResponse([
            'data' => $role,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'name'            => 'required',
            'permission_type' => 'required|in:all,custom',
            'description'     => 'required',
        ]);

        /**
         * Check for other admins if the role has been changed from all to custom.
         */
        $isChangedFromAll = request('permission_type') == 'custom' && $this->roleRepository->find($id)->permission_type == 'all';

        if (
            $isChangedFromAll
            && $this->adminRepository->countAdminsWithAllAccess() === 1
        ) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.roles.being-used'),
            ], 400);
        }

        $data = array_merge(request()->only([
            'name',
            'description',
            'permission_type',
        ]), [
            'permissions' => request()->has('permissions') ? request('permissions') : [],
        ]);

        Event::dispatch('user.role.update.before', $id);

        $role = $this->roleRepository->update($data, $id);

        Event::dispatch('user.role.update.after', $role);

        return new JsonResponse([
            'message' => trans('admin::app.settings.roles.update-success'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $role = $this->roleRepository->findOrFail($id);

        if ($role->admins->count() >= 1) {
            return new JsonResponse(['message' => trans('admin::app.settings.roles.being-used', [
                'name'   => 'admin::app.settings.roles.index.title',
                'source' => 'admin::app.settings.roles.index.admin-user',
            ])], 400);
        }

        if ($this->roleRepository->count() == 1) {
            return new JsonResponse([
                'message' => trans(
                    'admin::app.settings.roles.last-delete-error'
                ),
            ], 400);
        }

        try {
            Event::dispatch('user.role.delete.before', $id);

            $this->roleRepository->delete($id);

            Event::dispatch('user.role.delete.after', $id);

            return new JsonResponse(['message' => trans('admin::app.settings.roles.delete-success')]);
        } catch (\Exception $e) {
        }

        return new JsonResponse([
            'message' => trans(
                'admin::app.settings.roles.delete-failed'
            ),
        ], 500);
    }
}
