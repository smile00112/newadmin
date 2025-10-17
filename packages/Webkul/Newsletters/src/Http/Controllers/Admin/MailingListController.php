<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\MailingListRepository;
use Webkul\Newsletters\Repositories\VacapInstanceRepository;
use Webkul\Newsletters\Repositories\CustomerNumberRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Newsletters\Jobs\ProcessWhatsAppMailingList;

class MailingListController extends Controller
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
     * Display a listing of the resource.
     */
    public function index()
    {
        $mailingLists = $this->mailingListRepository->all();

        return view('newsletters::admin.mailing-lists.index', compact('mailingLists'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('newsletters::admin.mailing-lists.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Starting mailing list creation process', [
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $request->except(['whatsapp_instances', 'customer_numbers']),
            'whatsapp_instances_count' => $request->has('whatsapp_instances') ? count($request->input('whatsapp_instances')) : 0,
            'customer_numbers_count' => $request->has('customer_numbers') ? count($request->input('customer_numbers')) : 0,
        ]);

        $this->validate($request, [
            // Mailing List validation
            'message_text' => 'required|string',
            'active' => 'boolean',
            'start_at' => 'nullable|date|after:now',

            // WhatsApp Instances validation
            'whatsapp_instances.*.link_name' => 'required|string|max:255',
            'whatsapp_instances.*.login' => 'required|string|max:255',
            'whatsapp_instances.*.password' => 'required|string|max:255',

            // Customer Numbers validation
            'customer_numbers.*.phone_number' => 'required|string|max:20',
            'customer_numbers.*.name' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            Log::info('Database transaction started for mailing list creation');

            // Create mailing list first
            $mailingListData = [
                'message_text' => $request->input('message_text'),
                'active' => $request->input('active', true),
                'start_at' => $request->input('start_at'),
            ];

            Log::info('Creating mailing list', [
                'mailing_list_data' => $mailingListData,
            ]);

            $mailingList = $this->mailingListRepository->create($mailingListData);

            Log::info('Mailing list created successfully', [
                'mailing_list_id' => $mailingList->id,
                'message_text_preview' => substr($mailingList->message_text, 0, 100) . '...',
                'active' => $mailingList->active,
                'start_at' => $mailingList->start_at,
            ]);

            // Create WhatsApp instances with the new mailing list ID
            if ($request->has('whatsapp_instances')) {
                $whatsappInstances = $request->input('whatsapp_instances');
                $validInstances = array_filter($whatsappInstances, function($instance) {
                    return !empty($instance['link_name']) && !empty($instance['login']) && !empty($instance['password']);
                });

                Log::info('Processing WhatsApp instances', [
                    'total_instances' => count($whatsappInstances),
                    'valid_instances' => count($validInstances),
                    'mailing_list_id' => $mailingList->id,
                ]);

                foreach ($validInstances as $index => $instanceData) {
                    $instanceData['mailing_list_id'] = $mailingList->id;

                    Log::info('Creating WhatsApp instance', [
                        'instance_index' => $index,
                        'link_name' => $instanceData['link_name'],
                        'login' => $instanceData['login'],
                        'mailing_list_id' => $mailingList->id,
                    ]);

                    $instance = $this->vacapInstanceRepository->create($instanceData);

                    Log::info('WhatsApp instance created successfully', [
                        'instance_id' => $instance->id,
                        'link_name' => $instance->link_name,
                        'login' => $instance->login,
                        'mailing_list_id' => $instance->mailing_list_id,
                    ]);
                }
            }

            // Create customer numbers
            if ($request->has('customer_numbers')) {
                $customerNumbers = $request->input('customer_numbers');
                $validCustomers = array_filter($customerNumbers, function($customer) {
                    return !empty($customer['phone_number']) && !empty($customer['name']);
                });

                Log::info('Processing customer numbers', [
                    'total_customers' => count($customerNumbers),
                    'valid_customers' => count($validCustomers),
                    'mailing_list_id' => $mailingList->id,
                ]);

                foreach ($validCustomers as $index => $customerData) {
                    $customerData['mailing_list_id'] = $mailingList->id;

                    Log::info('Creating customer number', [
                        'customer_index' => $index,
                        'phone_number' => $customerData['phone_number'],
                        'name' => $customerData['name'],
                        'mailing_list_id' => $mailingList->id,
                    ]);

                    $customer = $this->customerNumberRepository->create($customerData);

                    Log::info('Customer number created successfully', [
                        'customer_id' => $customer->id,
                        'phone_number' => $customer->phone_number,
                        'name' => $customer->name,
                        'mailing_list_id' => $customer->mailing_list_id,
                    ]);
                }
            }

            DB::commit();
            Log::info('Database transaction committed successfully for mailing list creation', [
                'mailing_list_id' => $mailingList->id,
                'total_whatsapp_instances' => $request->has('whatsapp_instances') ? count(array_filter($request->input('whatsapp_instances'), function($instance) {
                    return !empty($instance['link_name']) && !empty($instance['login']) && !empty($instance['password']);
                })) : 0,
                'total_customer_numbers' => $request->has('customer_numbers') ? count(array_filter($request->input('customer_numbers'), function($customer) {
                    return !empty($customer['phone_number']) && !empty($customer['name']);
                })) : 0,
            ]);

            session()->flash('success', trans('newsletters::app.admin.mailing-lists.create-success'));

            return redirect()->route('admin.newsletters.mailing-lists.index');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create mailing list', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request_data' => $request->except(['whatsapp_instances', 'customer_numbers']),
                'whatsapp_instances_count' => $request->has('whatsapp_instances') ? count($request->input('whatsapp_instances')) : 0,
                'customer_numbers_count' => $request->has('customer_numbers') ? count($request->input('customer_numbers')) : 0,
            ]);

            session()->flash('error', trans('newsletters::app.admin.mailing-lists.create-failed') . ': ' . $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $mailingList = $this->mailingListRepository->findOrFail($id);
        $whatsappInstances = $this->vacapInstanceRepository->where('mailing_list_id', $id)->get();
        $customerNumbers = $this->customerNumberRepository->where('mailing_list_id', $id)->get();

        return view('newsletters::admin.mailing-lists.edit', compact('mailingList', 'whatsappInstances', 'customerNumbers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        Log::info('Starting mailing list update process', [
            'mailing_list_id' => $id,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $request->except(['whatsapp_instances', 'customer_numbers']),
            'whatsapp_instances_count' => $request->has('whatsapp_instances') ? count($request->input('whatsapp_instances')) : 0,
            'customer_numbers_count' => $request->has('customer_numbers') ? count($request->input('customer_numbers')) : 0,
        ]);

        $this->validate($request, [
            // Mailing List validation
            'message_text' => 'required|string',
            'active' => 'boolean',
            'start_at' => 'nullable|date',

            // WhatsApp Instances validation
            'whatsapp_instances.*.link_name' => 'required|string|max:255',
            'whatsapp_instances.*.login' => 'required|string|max:255',
            'whatsapp_instances.*.password' => 'required|string|max:255',

            // Customer Numbers validation
            'customer_numbers.*.phone_number' => 'required|string|max:20',
            'customer_numbers.*.name' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            Log::info('Database transaction started for mailing list update', ['mailing_list_id' => $id]);

            // Update mailing list
            $mailingListData = [
                'message_text' => $request->input('message_text'),
                'active' => $request->input('active', true),
                'start_at' => $request->input('start_at'),
            ];

            Log::info('Updating mailing list', [
                'mailing_list_id' => $id,
                'mailing_list_data' => $mailingListData,
            ]);

            $mailingList = $this->mailingListRepository->update($mailingListData, $id);

            Log::info('Mailing list updated successfully', [
                'mailing_list_id' => $mailingList->id,
                'message_text_preview' => substr($mailingList->message_text, 0, 100) . '...',
                'active' => $mailingList->active,
                'start_at' => $mailingList->start_at,
            ]);

            // Update WhatsApp instances
            if ($request->has('whatsapp_instances')) {
                // Get existing instances count before deletion
                $existingInstancesCount = $this->vacapInstanceRepository->where('mailing_list_id', $id)->count();

                Log::info('Deleting existing WhatsApp instances', [
                    'mailing_list_id' => $id,
                    'existing_instances_count' => $existingInstancesCount,
                ]);

                // Delete existing WhatsApp instances for this mailing list
                $this->vacapInstanceRepository->where('mailing_list_id', $id)->delete();

                Log::info('Existing WhatsApp instances deleted successfully', [
                    'mailing_list_id' => $id,
                    'deleted_count' => $existingInstancesCount,
                ]);

                // Create new WhatsApp instances
                $whatsappInstances = $request->input('whatsapp_instances');
                $validInstances = array_filter($whatsappInstances, function($instance) {
                    return !empty($instance['link_name']) && !empty($instance['login']) && !empty($instance['password']);
                });

                Log::info('Processing new WhatsApp instances', [
                    'total_instances' => count($whatsappInstances),
                    'valid_instances' => count($validInstances),
                    'mailing_list_id' => $id,
                ]);

                foreach ($validInstances as $index => $instanceData) {
                    $instanceData['mailing_list_id'] = $id;

                    Log::info('Creating WhatsApp instance', [
                        'instance_index' => $index,
                        'link_name' => $instanceData['link_name'],
                        'login' => $instanceData['login'],
                        'mailing_list_id' => $id,
                    ]);

                    $instance = $this->vacapInstanceRepository->create($instanceData);

                    Log::info('WhatsApp instance created successfully', [
                        'instance_id' => $instance->id,
                        'link_name' => $instance->link_name,
                        'login' => $instance->login,
                        'mailing_list_id' => $instance->mailing_list_id,
                    ]);
                }
            }

            // Update customer numbers
            if ($request->has('customer_numbers')) {
                // Get existing customers count before deletion
                $existingCustomersCount = $this->customerNumberRepository->where('mailing_list_id', $id)->count();

                Log::info('Deleting existing customer numbers', [
                    'mailing_list_id' => $id,
                    'existing_customers_count' => $existingCustomersCount,
                ]);

                // Delete existing customer numbers for this mailing list
                $this->customerNumberRepository->where('mailing_list_id', $id)->delete();

                Log::info('Existing customer numbers deleted successfully', [
                    'mailing_list_id' => $id,
                    'deleted_count' => $existingCustomersCount,
                ]);

                // Create new customer numbers
                $customerNumbers = $request->input('customer_numbers');
                $validCustomers = array_filter($customerNumbers, function($customer) {
                    return !empty($customer['phone_number']) && !empty($customer['name']);
                });

                Log::info('Processing new customer numbers', [
                    'total_customers' => count($customerNumbers),
                    'valid_customers' => count($validCustomers),
                    'mailing_list_id' => $id,
                ]);

                foreach ($validCustomers as $index => $customerData) {
                    $customerData['mailing_list_id'] = $id;

                    Log::info('Creating customer number', [
                        'customer_index' => $index,
                        'phone_number' => $customerData['phone_number'],
                        'name' => $customerData['name'],
                        'mailing_list_id' => $id,
                    ]);

                    $customer = $this->customerNumberRepository->create($customerData);

                    Log::info('Customer number created successfully', [
                        'customer_id' => $customer->id,
                        'phone_number' => $customer->phone_number,
                        'name' => $customer->name,
                        'mailing_list_id' => $customer->mailing_list_id,
                    ]);
                }
            }

            DB::commit();
            Log::info('Database transaction committed successfully for mailing list update', [
                'mailing_list_id' => $id,
                'total_whatsapp_instances' => $request->has('whatsapp_instances') ? count(array_filter($request->input('whatsapp_instances'), function($instance) {
                    return !empty($instance['link_name']) && !empty($instance['login']) && !empty($instance['password']);
                })) : 0,
                'total_customer_numbers' => $request->has('customer_numbers') ? count(array_filter($request->input('customer_numbers'), function($customer) {
                    return !empty($customer['phone_number']) && !empty($customer['name']);
                })) : 0,
            ]);

            session()->flash('success', trans('newsletters::app.admin.mailing-lists.update-success'));

            return redirect()->route('admin.newsletters.mailing-lists.index');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update mailing list', [
                'mailing_list_id' => $id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request_data' => $request->except(['whatsapp_instances', 'customer_numbers']),
                'whatsapp_instances_count' => $request->has('whatsapp_instances') ? count($request->input('whatsapp_instances')) : 0,
                'customer_numbers_count' => $request->has('customer_numbers') ? count($request->input('customer_numbers')) : 0,
            ]);

            session()->flash('error', trans('newsletters::app.admin.mailing-lists.update-failed') . ': ' . $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        Log::info('Starting mailing list deletion process', [
            'mailing_list_id' => $id,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        try {
            DB::beginTransaction();
            Log::info('Database transaction started for mailing list deletion', ['mailing_list_id' => $id]);

            // Get counts before deletion for logging
            $customerNumbersCount = $this->customerNumberRepository->where('mailing_list_id', $id)->count();
            $whatsappInstancesCount = $this->vacapInstanceRepository->where('mailing_list_id', $id)->count();

            Log::info('Deleting associated records', [
                'mailing_list_id' => $id,
                'customer_numbers_count' => $customerNumbersCount,
                'whatsapp_instances_count' => $whatsappInstancesCount,
            ]);

            // Delete customer numbers first
            $this->customerNumberRepository->where('mailing_list_id', $id)->delete();
            Log::info('Customer numbers deleted successfully', [
                'mailing_list_id' => $id,
                'deleted_customers_count' => $customerNumbersCount,
            ]);

            // Delete WhatsApp instances
            $this->vacapInstanceRepository->where('mailing_list_id', $id)->delete();
            Log::info('WhatsApp instances deleted successfully', [
                'mailing_list_id' => $id,
                'deleted_instances_count' => $whatsappInstancesCount,
            ]);

            // Delete mailing list
            Log::info('Deleting mailing list', ['mailing_list_id' => $id]);
            $this->mailingListRepository->delete($id);
            Log::info('Mailing list deleted successfully', ['mailing_list_id' => $id]);

            DB::commit();
            Log::info('Database transaction committed successfully for mailing list deletion', [
                'mailing_list_id' => $id,
                'deleted_customers_count' => $customerNumbersCount,
                'deleted_instances_count' => $whatsappInstancesCount,
            ]);

            return response()->json([
                'message' => trans('newsletters::app.admin.mailing-lists.delete-success'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete mailing list', [
                'mailing_list_id' => $id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => trans('newsletters::app.admin.mailing-lists.delete-failed'),
            ], 500);
        }
    }

    /**
     * Send the mailing list.
     */
    public function send(int $id)
    {
        Log::info('Starting mailing list send process', [
            'mailing_list_id' => $id,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $mailingList = $this->mailingListRepository->findOrFail($id);

        Log::info('Mailing list found for sending', [
            'mailing_list_id' => $mailingList->id,
            'current_status' => $mailingList->status,
            'customers_count' => $mailingList->customerNumbers()->count(),
        ]);

        if ($mailingList->status !== 'draft') {
            Log::warning('Attempted to send mailing list with invalid status', [
                'mailing_list_id' => $id,
                'current_status' => $mailingList->status,
                'expected_status' => 'draft',
            ]);

            return response()->json([
                'message' => trans('newsletters::app.admin.mailing-lists.send-failed'),
            ], 400);
        }

        try {
            // Update status to sending
            Log::info('Updating mailing list status to sending', ['mailing_list_id' => $id]);
            $this->mailingListRepository->update(['status' => 'sending'], $id);

            // Here you would implement the actual sending logic
            // For now, we'll just update the status to sent
            $customersCount = $mailingList->customerNumbers()->count();

            Log::info('Processing mailing list send', [
                'mailing_list_id' => $id,
                'customers_count' => $customersCount,
            ]);

            $this->mailingListRepository->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sent_count' => $customersCount,
            ], $id);

            Log::info('Mailing list sent successfully', [
                'mailing_list_id' => $id,
                'sent_count' => $customersCount,
                'sent_at' => now(),
            ]);

            return response()->json([
                'message' => trans('newsletters::app.admin.mailing-lists.send-success'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send mailing list', [
                'mailing_list_id' => $id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            $this->mailingListRepository->update(['status' => 'failed'], $id);

            Log::info('Mailing list status updated to failed', ['mailing_list_id' => $id]);

            return response()->json([
                'message' => trans('newsletters::app.admin.mailing-lists.send-failed'),
            ], 500);
        }
    }

    public function startMailing(int $id)
    {
        $mailingList = $this->mailingListRepository->findOrFail($id);

        if (!$mailingList->active) {
            return response()->json([
                'success' => false,
                'message' => 'Mailing list is not active'
            ], 400);
        }

        if ($mailingList->whatsappInstances()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No WhatsApp instances configured for this mailing list'
            ], 400);
        }

        if ($mailingList->customerNumbers()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No customer numbers found for this mailing list'
            ], 400);
        }

        // Dispatch the mailing job
        ProcessWhatsAppMailingList::dispatch($id);

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp mailing campaign started successfully'
        ]);
    }

    public function testMailing(int $id)
    {

        $mailingList = $this->mailingListRepository->findOrFail($id);

        $service = new \Webkul\Newsletters\Services\WhatsAppMailingService();
        $vacapInstance = \Webkul\Newsletters\Models\VacapInstance::find(8);
       // dd($vacapInstance);
        $service->sendMessage($vacapInstance, '79206003708', 'Privet');
//https://1105.api.green-api.com
//1105346932
//5fa9c1aac71e4278bbde55cf579420b77cf9a264fdfd4a5b87
//
//$service->sendMessage();


    }

}

