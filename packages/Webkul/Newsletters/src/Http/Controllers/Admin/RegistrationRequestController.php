<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\RegistrationRequestRepository;
use Webkul\Newsletters\Traits\HasNewsletterRole;

class RegistrationRequestController extends Controller
{
    use HasNewsletterRole;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected RegistrationRequestRepository $registrationRequestRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->requireNewsletterPermission('newsletters.registration-requests.view');
        
        $requests = $this->registrationRequestRepository->all();

        return view('newsletters::admin.registration-requests.index', compact('requests'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $this->requireNewsletterPermission('newsletters.registration-requests.edit');
        
        $request = $this->registrationRequestRepository->findOrFail($id);

        return view('newsletters::admin.registration-requests.edit', compact('request'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $this->requireNewsletterPermission('newsletters.registration-requests.edit');
        
        $registrationRequest = $this->registrationRequestRepository->findOrFail($id);

        $data = $request->validate([
            'status' => 'required|string|in:pending,processed,rejected',
        ]);

        $this->registrationRequestRepository->update($data, $id);

        session()->flash('success', trans('newsletters::app.admin.registration-requests.update-success'));

        return redirect()->route('admin.newsletters.registration-requests.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->requireNewsletterPermission('newsletters.registration-requests.delete');
        
        try {
            $this->registrationRequestRepository->delete($id);

            return response()->json([
                'message' => trans('newsletters::app.admin.registration-requests.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('newsletters::app.admin.registration-requests.delete-failed'),
            ], 500);
        }
    }

    /**
     * Mass delete selected registration requests.
     */
    public function massDestroy(Request $request)
    {
        $this->requireNewsletterPermission('newsletters.registration-requests.delete');

        $validated = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:registration_requests,id',
        ]);

        try {
            $deletedCount = 0;
            foreach (array_unique($validated['ids']) as $id) {
                $this->registrationRequestRepository->delete((int) $id);
                $deletedCount++;
            }

            return response()->json([
                'message' => trans('newsletters::app.admin.registration-requests.mass-delete-success', ['count' => $deletedCount]),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('newsletters::app.admin.registration-requests.mass-delete-failed'),
            ], 500);
        }
    }
}




