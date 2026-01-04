<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\VacapInstanceRepository;

class VacapInstanceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected VacapInstanceRepository $whatsappInstanceRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('newsletters::admin.whatsapp-instances.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mailingLists = app(\Webkul\Newsletters\Repositories\MailingListRepository::class)->all();

        return view('newsletters::admin.whatsapp-instances.create', compact('mailingLists'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'link_name' => 'required|string|max:255',
            'login' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $data = $request->except(['mailing_list_id']);
        $whatsappInstance = $this->whatsappInstanceRepository->create($data);

        session()->flash('success', trans('newsletters::app.admin.whatsapp-instances.create-success'));

        return redirect()->route('admin.newsletters.whatsapp-instances.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $whatsappInstance = $this->whatsappInstanceRepository->findOrFail($id);
        $mailingLists = app(\Webkul\Newsletters\Repositories\MailingListRepository::class)->all();

        return view('newsletters::admin.whatsapp-instances.edit', compact('whatsappInstance', 'mailingLists'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'link_name' => 'required|string|max:255',
            'login' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $data = $request->except(['mailing_list_id']);
        $whatsappInstance = $this->whatsappInstanceRepository->update($data, $id);

        session()->flash('success', trans('newsletters::app.admin.whatsapp-instances.update-success'));

        return redirect()->route('admin.newsletters.whatsapp-instances.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->whatsappInstanceRepository->findOrFail($id);

        try {
            $this->whatsappInstanceRepository->delete($id);

            return response()->json([
                'message' => trans('newsletters::app.admin.whatsapp-instances.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('newsletters::app.admin.whatsapp-instances.delete-failed'),
            ], 500);
        }
    }
}