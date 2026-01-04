<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\MailingListRepository;
use Webkul\Newsletters\Repositories\VacapInstanceRepository;
use Webkul\Newsletters\Repositories\CustomerNumberRepository;
use Illuminate\Support\Facades\DB;

class UnifiedNewsletterController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected MailingListRepository $mailingListRepository,
        protected VacapInstanceRepository $vacapInstanceRepository,
        protected CustomerNumberRepository $customerNumberRepository
    ) {}

    /**
     * Display the unified management page.
     */
    public function index()
    {
        $mailingLists = $this->mailingListRepository->all();
        $whatsappInstances = $this->vacapInstanceRepository->all();
        $customerNumbers = $this->customerNumberRepository->all();

        return view('newsletters::admin.unified.index', compact('mailingLists', 'whatsappInstances', 'customerNumbers'));
    }

    /**
     * Store all entities simultaneously.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            // Mailing List validation
            'mailing_list.message_text' => 'required|string',
            'mailing_list.active' => 'boolean',
            'mailing_list.start_at' => 'nullable|date|after:now',
            
            // WhatsApp Instance validation
            'whatsapp_instance.link_name' => 'required|string|max:255',
            'whatsapp_instance.login' => 'required|string|max:255',
            'whatsapp_instance.password' => 'required|string|max:255',
            'whatsapp_instance.mailing_list_id' => 'nullable|exists:newsletters_mailing_lists,id',
            
            // Customer Numbers validation
            'customer_numbers.*.phone_number' => 'required|string|max:20',
            'customer_numbers.*.name' => 'required|string|max:255',
            'customer_numbers.*.email' => 'nullable|email|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Create mailing list first
            $mailingList = $this->mailingListRepository->create([
                'message_text' => $request->input('mailing_list.message_text'),
                'active' => $request->input('mailing_list.active', true),
                'start_at' => $request->input('mailing_list.start_at'),
            ]);

            // Create WhatsApp instance with the new mailing list ID
            $whatsappInstance = $this->vacapInstanceRepository->create([
                'link_name' => $request->input('whatsapp_instance.link_name'),
                'login' => $request->input('whatsapp_instance.login'),
                'password' => $request->input('whatsapp_instance.password'),
                'mailing_list_id' => $mailingList->id,
            ]);

            // Create customer numbers
            $customerNumbers = [];
            if ($request->has('customer_numbers')) {
                foreach ($request->input('customer_numbers') as $customerData) {
                    if (!empty($customerData['phone_number']) && !empty($customerData['name'])) {
                        $customerNumbers[] = $this->customerNumberRepository->create([
                            'phone_number' => $customerData['phone_number'],
                            'name' => $customerData['name'],
                            'mailing_list_id' => $mailingList->id,
                        ]);
                    }
                }
            }

            DB::commit();

            session()->flash('success', trans('newsletters::app.admin.unified.create-success'));

            return redirect()->route('admin.newsletters.unified.index');

        } catch (\Exception $e) {
            DB::rollBack();
            
            session()->flash('error', trans('newsletters::app.admin.unified.create-failed') . ': ' . $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    /**
     * Update all entities simultaneously.
     */
    public function update(Request $request, int $mailingListId)
    {
        $this->validate($request, [
            // Mailing List validation
            'mailing_list.message_text' => 'required|string',
            'mailing_list.active' => 'boolean',
            'mailing_list.start_at' => 'nullable|date',
            
            // WhatsApp Instance validation
            'whatsapp_instance.link_name' => 'required|string|max:255',
            'whatsapp_instance.login' => 'required|string|max:255',
            'whatsapp_instance.password' => 'required|string|max:255',
            
            // Customer Numbers validation
            'customer_numbers.*.phone_number' => 'required|string|max:20',
            'customer_numbers.*.name' => 'required|string|max:255',
            'customer_numbers.*.email' => 'nullable|email|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Update mailing list
            $mailingList = $this->mailingListRepository->update([
                'message_text' => $request->input('mailing_list.message_text'),
                'active' => $request->input('mailing_list.active', true),
                'start_at' => $request->input('mailing_list.start_at'),
            ], $mailingListId);

            // Update WhatsApp instance (get first instance from relationship)
            $whatsappInstance = $mailingList->whatsappInstances()->first();
            if ($whatsappInstance) {
                $this->vacapInstanceRepository->update([
                    'link_name' => $request->input('whatsapp_instance.link_name'),
                    'login' => $request->input('whatsapp_instance.login'),
                    'password' => $request->input('whatsapp_instance.password'),
                ], $whatsappInstance->id);
            }

            // Update customer numbers
            if ($request->has('customer_numbers')) {
                // Delete existing customer numbers for this mailing list
                $this->customerNumberRepository->where('mailing_list_id', $mailingListId)->delete();
                
                // Create new customer numbers
                foreach ($request->input('customer_numbers') as $customerData) {
                    if (!empty($customerData['phone_number']) && !empty($customerData['name'])) {
                        $this->customerNumberRepository->create([
                            'phone_number' => $customerData['phone_number'],
                            'name' => $customerData['name'],
                            'mailing_list_id' => $mailingListId,
                        ]);
                    }
                }
            }

            DB::commit();

            session()->flash('success', trans('newsletters::app.admin.unified.update-success'));

            return redirect()->route('admin.newsletters.unified.index');

        } catch (\Exception $e) {
            DB::rollBack();
            
            session()->flash('error', trans('newsletters::app.admin.unified.update-failed') . ': ' . $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    /**
     * Show the form for editing a unified entry.
     */
    public function edit(int $mailingListId)
    {
        $mailingList = $this->mailingListRepository->findOrFail($mailingListId);
        $whatsappInstance = $mailingList->whatsappInstances()->first();
        $customerNumbers = $this->customerNumberRepository->where('mailing_list_id', $mailingListId)->get();

        return view('newsletters::admin.unified.edit', compact('mailingList', 'whatsappInstance', 'customerNumbers'));
    }

    /**
     * Delete a unified entry (all related entities).
     */
    public function destroy(int $mailingListId)
    {
        try {
            DB::beginTransaction();

            // Delete customer numbers first
            $this->customerNumberRepository->where('mailing_list_id', $mailingListId)->delete();
            
            // Detach WhatsApp instances (pivot table will be cleaned automatically by cascade)
            $mailingList = $this->mailingListRepository->findOrFail($mailingListId);
            $mailingList->whatsappInstances()->detach();
            
            // Delete mailing list
            $this->mailingListRepository->delete($mailingListId);

            DB::commit();

            return response()->json([
                'message' => trans('newsletters::app.admin.unified.delete-success'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => trans('newsletters::app.admin.unified.delete-failed'),
            ], 500);
        }
    }
}
