<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\StopListRepository;

class StopListController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected StopListRepository $stopListRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('newsletters::admin.stop-list.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('newsletters::admin.stop-list.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'phone_number' => 'required|string|max:20|unique:newsletters_stop_list,phone_number',
        ]);

        $stopList = $this->stopListRepository->create($request->all());

        session()->flash('success', trans('newsletters::app.admin.stop-list.create-success'));

        return redirect()->route('admin.newsletters.stop-list.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $stopList = $this->stopListRepository->findOrFail($id);

        return view('newsletters::admin.stop-list.edit', compact('stopList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'phone_number' => 'required|string|max:20|unique:newsletters_stop_list,phone_number,' . $id,
        ]);

        $stopList = $this->stopListRepository->update($request->all(), $id);

        session()->flash('success', trans('newsletters::app.admin.stop-list.update-success'));

        return redirect()->route('admin.newsletters.stop-list.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->stopListRepository->findOrFail($id);

        try {
            $this->stopListRepository->delete($id);

            return response()->json([
                'message' => trans('newsletters::app.admin.stop-list.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('newsletters::app.admin.stop-list.delete-failed'),
            ], 500);
        }
    }

    /**
     * Check if phone number is blocked.
     */
    public function check(Request $request)
    {
        $this->validate($request, [
            'phone_number' => 'required|string',
        ]);

        $isBlocked = $this->stopListRepository->isBlocked($request->phone_number);

        return response()->json([
            'is_blocked' => $isBlocked,
            'message' => $isBlocked 
                ? trans('newsletters::app.admin.stop-list.phone-blocked')
                : trans('newsletters::app.admin.stop-list.phone-not-blocked'),
        ]);
    }
}
