<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\CompanyRepository;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
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
        $companies = $this->companyRepository->all();

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

        $company = $this->companyRepository->create($data);

        session()->flash('success', trans('newsletters::app.admin.companies.create-success'));

        return redirect()->route('admin.newsletters.companies.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $company = $this->companyRepository->findOrFail($id);

        return view('newsletters::admin.companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
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

        $company = $this->companyRepository->update($data, $id);

        session()->flash('success', trans('newsletters::app.admin.companies.update-success'));

        return redirect()->route('admin.newsletters.companies.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->companyRepository->findOrFail($id);

        try {
            $this->companyRepository->delete($id);

            return response()->json([
                'message' => trans('newsletters::app.admin.companies.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('newsletters::app.admin.companies.delete-failed'),
            ], 500);
        }
    }
}

