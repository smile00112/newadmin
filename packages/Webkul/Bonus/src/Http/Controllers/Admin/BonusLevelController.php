<?php

namespace Webkul\Bonus\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Bonus\Repositories\BonusLevelRepository;

class BonusLevelController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected BonusLevelRepository $bonusLevelRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $levels = $this->bonusLevelRepository->all();

        return view('bonus::admin.levels.index', compact('levels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('bonus::admin.levels.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): RedirectResponse
    {
        $validatedData = $this->validate(request(), [
            'name' => 'required|string|max:255',
            'cashback_percent' => 'required|numeric|min:0|max:100',
            'calculation_type' => 'required|in:orders_count,total_spent,cart_value',
            'threshold_value' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $this->bonusLevelRepository->create($validatedData);

        session()->flash('success', trans('bonus::app.admin.levels.create-success'));

        return redirect()->route('admin.bonus.levels.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $level = $this->bonusLevelRepository->findOrFail($id);

        return view('bonus::admin.levels.edit', compact('level'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): RedirectResponse
    {
        $validatedData = $this->validate(request(), [
            'name' => 'required|string|max:255',
            'cashback_percent' => 'required|numeric|min:0|max:100',
            'calculation_type' => 'required|in:orders_count,total_spent,cart_value',
            'threshold_value' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $this->bonusLevelRepository->update($validatedData, $id);

        session()->flash('success', trans('bonus::app.admin.levels.update-success'));

        return redirect()->route('admin.bonus.levels.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->bonusLevelRepository->delete($id);

        session()->flash('success', trans('bonus::app.admin.levels.delete-success'));

        return redirect()->route('admin.bonus.levels.index');
    }
}
