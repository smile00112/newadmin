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
    public function index(Request $request)
    {
        $this->requireNewsletterPermission('newsletters.registration-requests.view');
        
        $perPage = $request->get('per_page', 15);
        $requests = $this->registrationRequestRepository->paginate($perPage)->appends($request->query());

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
}




