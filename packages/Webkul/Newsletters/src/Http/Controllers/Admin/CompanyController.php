<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\CompanyRepository;
use Webkul\Newsletters\Traits\HasNewsletterRole;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    use HasNewsletterRole;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CompanyRepository $companyRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->requireNewsletterPermission('newsletters.companies');
        
        $admin = auth()->guard('admin')->user();
        
        // Админ с permission_type = all видит все компании
        if ($admin->role && $admin->role->permission_type === 'all' && !$admin->company_id) {
            $companies = $this->companyRepository->all();
        } 
        // Владелец видит только свою компанию
        elseif ($admin->company_id) {
            $companies = collect([$this->companyRepository->find($admin->company_id)])->filter();
        } else {
            $companies = collect();
        }

        return view('newsletters::admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('newsletters::admin.companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:companies,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Handle checkbox: if not present in request, set to false
        $data['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : false;

        $company = $this->companyRepository->create($data);

        session()->flash('success', trans('newsletters::app.admin.companies.create-success'));

        return redirect()->route('admin.newsletters.companies.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $this->requireNewsletterPermission('newsletters.companies.edit');
        
        $admin = auth()->guard('admin')->user();
        $company = $this->companyRepository->findOrFail($id);

        // Админ с permission_type = all может редактировать любую компанию
        if (!($admin->role && $admin->role->permission_type === 'all' && !$admin->company_id)) {
            // Проверка, что компания принадлежит текущему админу
            $this->ensureSameCompany($company->id);
        }

        $company->load(['admins' => function ($query) {
            $query->with('role')->orderBy('name');
        }]);

        return view('newsletters::admin.companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $this->requireNewsletterPermission('newsletters.companies.edit');
        
        $admin = auth()->guard('admin')->user();
        $company = $this->companyRepository->findOrFail($id);

        // Админ с permission_type = all может редактировать любую компанию
        if (!($admin->role && $admin->role->permission_type === 'all' && !$admin->company_id)) {
            // Проверка, что компания принадлежит текущему админу
            $this->ensureSameCompany($company->id);
        }

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:companies,slug,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Handle checkbox: if not present in request, set to false
        $data['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : false;

        $company = $this->companyRepository->update($data, $id);

        session()->flash('success', trans('newsletters::app.admin.companies.update-success'));

        return redirect()->route('admin.newsletters.companies.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->requireNewsletterPermission('newsletters.companies.delete');
        
        $admin = auth()->guard('admin')->user();
        $company = $this->companyRepository->findOrFail($id);

        // Админ с permission_type = all может удалять любую компанию
        if (!($admin->role && $admin->role->permission_type === 'all' && !$admin->company_id)) {
            // Проверка, что компания принадлежит текущему админу
            $this->ensureSameCompany($company->id);
        }

        try {
            $this->companyRepository->delete($id);

            session()->flash('success', trans('newsletters::app.admin.companies.delete-success'));

            return redirect()->route('admin.newsletters.companies.index');
        } catch (\Exception $e) {
            session()->flash('error', trans('newsletters::app.admin.companies.delete-failed'));

            return redirect()->route('admin.newsletters.companies.index');
        }
    }

    /**
     * Remove selected companies from storage.
     */
    public function massDestroy(Request $request)
    {
        $this->requireNewsletterPermission('newsletters.companies.delete');

        $admin = auth()->guard('admin')->user();

        if (! $this->isSuperAdmin($admin)) {
            abort(403, __('admin::app.error.403.message'));
        }

        $validated = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:companies,id',
        ]);

        $deletedCount = 0;

        foreach (array_unique($validated['ids']) as $companyId) {
            try {
                $this->companyRepository->delete((int) $companyId);
                $deletedCount++;
            } catch (\Exception $exception) {
                // Continue to delete other selected companies.
            }
        }

        if ($deletedCount === 0) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.companies.mass-delete-failed'),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => trans('newsletters::app.admin.companies.mass-delete-success', ['count' => $deletedCount]),
        ]);
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

