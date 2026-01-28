<?php

namespace App\Http\Controllers\Admin;

use App\Models\BonusLevel;
use App\Repositories\BonusLevelRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;

class BonusLevelController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected BonusLevelRepository $bonusLevelRepository
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $levels = $this->bonusLevelRepository->all();

        return view('admin::bonus-levels.index', compact('levels'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('admin::bonus-levels.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'cashback_percent' => 'required|numeric|min:0|max:100',
            'min_orders' => 'nullable|integer|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'min_cart_value' => 'nullable|numeric|min:0',
        ]);

        $this->bonusLevelRepository->create($validated);

        session()->flash('success', trans('admin::app.bonus-levels.create-success'));

        return redirect()->route('admin.bonus-levels.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit(int $id): View
    {
        $level = $this->bonusLevelRepository->findOrFail($id);

        return view('admin::bonus-levels.edit', compact('level'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'cashback_percent' => 'required|numeric|min:0|max:100',
            'min_orders' => 'nullable|integer|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'min_cart_value' => 'nullable|numeric|min:0',
        ]);

        $this->bonusLevelRepository->update($validated, $id);

        session()->flash('success', trans('admin::app.bonus-levels.update-success'));

        return redirect()->route('admin.bonus-levels.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $level = $this->bonusLevelRepository->findOrFail($id);

        // Check if level has customers
        if ($level->customers()->count() > 0) {
            session()->flash('error', trans('admin::app.bonus-levels.delete-error'));

            return redirect()->route('admin.bonus-levels.index');
        }

        $this->bonusLevelRepository->delete($id);

        session()->flash('success', trans('admin::app.bonus-levels.delete-success'));

        return redirect()->route('admin.bonus-levels.index');
    }
}
