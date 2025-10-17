<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\CustomerNumberRepository;

class CustomerNumberController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CustomerNumberRepository $customerNumberRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('newsletters::admin.customer-numbers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mailingLists = app(\Webkul\Newsletters\Repositories\MailingListRepository::class)->all();

        return view('newsletters::admin.customer-numbers.create', compact('mailingLists'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'phone_number' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'mailing_list_id' => 'required|exists:newsletters_mailing_lists,id',
        ]);

        $customerNumber = $this->customerNumberRepository->create($request->all());

        session()->flash('success', trans('newsletters::app.admin.customer-numbers.create-success'));

        return redirect()->route('admin.newsletters.customer-numbers.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $customerNumber = $this->customerNumberRepository->findOrFail($id);
        $mailingLists = app(\Webkul\Newsletters\Repositories\MailingListRepository::class)->all();

        return view('newsletters::admin.customer-numbers.edit', compact('customerNumber', 'mailingLists'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'phone_number' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'mailing_list_id' => 'required|exists:newsletters_mailing_lists,id',
        ]);

        $customerNumber = $this->customerNumberRepository->update($request->all(), $id);

        session()->flash('success', trans('newsletters::app.admin.customer-numbers.update-success'));

        return redirect()->route('admin.newsletters.customer-numbers.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->customerNumberRepository->findOrFail($id);

        try {
            $this->customerNumberRepository->delete($id);

            return response()->json([
                'message' => trans('newsletters::app.admin.customer-numbers.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('newsletters::app.admin.customer-numbers.delete-failed'),
            ], 500);
        }
    }

    /**
     * Import customer numbers from CSV.
     */
    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:csv,txt',
            'mailing_list_id' => 'required|exists:newsletters_mailing_lists,id',
        ]);

        try {
            $file = $request->file('file');
            $mailingListId = $request->mailing_list_id;
            
            $csvData = array_map('str_getcsv', file($file->getPathname()));
            $header = array_shift($csvData);

            $imported = 0;
            foreach ($csvData as $row) {
                $data = array_combine($header, $row);
                
                $this->customerNumberRepository->create([
                    'phone_number' => $data['phone_number'] ?? '',
                    'name' => $data['name'] ?? null,
                    'mailing_list_id' => $mailingListId,
                ]);
                
                $imported++;
            }

            session()->flash('success', trans('newsletters::app.admin.customer-numbers.import-success', ['count' => $imported]));

        } catch (\Exception $e) {
            session()->flash('error', trans('newsletters::app.admin.customer-numbers.import-failed'));
        }

        return redirect()->route('admin.newsletters.customer-numbers.index');
    }
}
