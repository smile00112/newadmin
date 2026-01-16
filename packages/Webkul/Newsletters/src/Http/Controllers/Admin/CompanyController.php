<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\CompanyRepository;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;
use Webkul\Newsletters\Traits\HasNewsletterRole;
use Webkul\User\Repositories\AdminRepository;
use Webkul\User\Repositories\RoleRepository;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    use HasNewsletterRole;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CompanyRepository $companyRepository,
        protected CompanyAccountRepository $accountRepository,
        protected AdminRepository $adminRepository,
        protected RoleRepository $roleRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->requireNewsletterPermission('newsletters.companies');
        
        $admin = auth()->guard('admin')->user();
        
        $perPage = $request->get('per_page', 15);
        
        // Админ с permission_type = all видит все компании
        if ($admin->role && $admin->role->permission_type === 'all' && !$admin->company_id) {
            $companies = $this->companyRepository->paginate($perPage);
        } 
        // Владелец видит только свою компанию
        elseif ($admin->company_id) {
            $company = $this->companyRepository->find($admin->company_id);
            if ($company) {
                // Создаем пагинацию вручную для одной компании
                $companies = new \Illuminate\Pagination\LengthAwarePaginator(
                    collect([$company]),
                    1,
                    $perPage,
                    1,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            } else {
                $companies = new \Illuminate\Pagination\LengthAwarePaginator(
                    collect(),
                    0,
                    $perPage,
                    1,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            }
        } else {
            $companies = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                $perPage,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        // Load accounts for each company
        foreach ($companies as $company) {
            if ($company) {
                $company->account = $this->accountRepository->getOrCreateForCompany($company->id);
            }
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

        // Load account for the company
        $company->account = $this->accountRepository->getOrCreateForCompany($company->id);

        // Load owners for the company
        $owners = $this->adminRepository
            ->where('company_id', $company->id)
            ->with(['role'])
            ->get();

        // Get roles for creating new owner (excluding admin roles 1 and 2)
        $roles = $this->roleRepository
            ->whereNotIn('id', [1, 2])
            ->get();

        return view('newsletters::admin.companies.edit', compact('company', 'owners', 'roles'));
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
}

