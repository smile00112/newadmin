<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\CustomerNumberRepository;
use Webkul\Newsletters\Events\CustomerNumberMessageRead;
use Webkul\Newsletters\Services\WhatsAppMailingService;
use Webkul\Newsletters\Models\NewslettersContact;

class CustomerNumberController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CustomerNumberRepository $customerNumberRepository,
        protected WhatsAppMailingService $whatsAppMailingService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('newsletters::admin.customer-numbers.index');
    }

    /**
     * Display messages list.
     */
    public function messages(Request $request)
    {
        $query = $this->customerNumberRepository->newQuery();
        
        // Search by phone number if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('phone_number', 'like', '%' . $searchTerm . '%');
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'id');
        $sortDir = $request->get('sort_dir', 'desc');
        
        // Validate sort_by field
        $allowedSortFields = ['id', 'phone_number', 'created_at', 'mailing_list_id'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'id';
        }
        
        // Validate sort direction
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }
        
        // Handle sorting by mailing_list_id (need to join with mailing_lists table)
        if ($sortBy === 'mailing_list_id') {
            $query->leftJoin('newsletters_mailing_lists', 'newsletters_customer_numbers.mailing_list_id', '=', 'newsletters_mailing_lists.id')
                  ->select('newsletters_customer_numbers.*')
                  ->orderBy('newsletters_mailing_lists.message_text', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }
        
        // Always prioritize incoming messages
        if ($sortBy !== 'incoming_message') {
            $query->orderBy('incoming_message', 'desc');
        }
        
        $messages = $query
            ->with(['mailingList', 'whatsAppInstance'])
            ->paginate(50)
            ->appends($request->query());

        return view('newsletters::admin.messages.index', compact('messages', 'sortBy', 'sortDir'));
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
            'phone_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+?[1-9]\d{1,14}$/',
                Rule::unique('newsletters_customer_numbers')
                    ->where('mailing_list_id', $request->mailing_list_id),
            ],
            'name' => 'required|string|max:255',
            'mailing_list_id' => 'required|exists:newsletters_mailing_lists,id',
        ]);

        $data = $request->all();
        
        // Get telegram_id from contact if contact_id is provided
        if (empty($data['telegram_id']) && !empty($data['contact_id'])) {
            $contact = NewslettersContact::find($data['contact_id']);
            if ($contact && !empty($contact->telegram_user_id)) {
                $data['telegram_id'] = $contact->telegram_user_id;
            }
        }

        $customerNumber = $this->customerNumberRepository->create($data);

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
        $customerNumber = $this->customerNumberRepository->findOrFail($id);
        
        $rules = [
            'phone_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+?[1-9]\d{1,14}$/',
                Rule::unique('newsletters_customer_numbers')
                    ->where('mailing_list_id', $customerNumber->mailing_list_id)
                    ->ignore($id),
            ],
            'name' => 'sometimes|string|max:255',
        ];
        
        // Add mailing_list_id validation only if it's present in the request
        if ($request->has('mailing_list_id')) {
            $rules['mailing_list_id'] = 'required|exists:newsletters_mailing_lists,id';
        }
        
        $this->validate($request, $rules);

        $data = $request->only(['phone_number', 'name', 'delivered', 'viewed', 'telegram_id', 'contact_id', 'email']);
        
        // Get telegram_id from contact if contact_id is provided and telegram_id is not set
        if (empty($data['telegram_id']) && !empty($data['contact_id'])) {
            $contact = NewslettersContact::find($data['contact_id']);
            if ($contact && !empty($contact->telegram_user_id)) {
                $data['telegram_id'] = $contact->telegram_user_id;
            }
        }
        
        // Keep the original mailing_list_id if not provided in request
        if ($request->has('mailing_list_id')) {
            $data['mailing_list_id'] = $request->mailing_list_id;
        }
        
        $customerNumber = $this->customerNumberRepository->update($data, $id);

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => trans('newsletters::app.admin.customer-numbers.update-success'),
                'customer_number' => $customerNumber,
            ]);
        }

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
            $skipped = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($csvData as $index => $row) {
                try {
                    $data = array_combine($header, $row);

                    $rawPhone = trim($data['phone_number'] ?? '');
                    $phoneNumber = preg_replace('/[^0-9]/', '', $rawPhone);
                    $name = trim($data['name'] ?? '');

                    // Skip empty rows
                    if (empty($phoneNumber)) {
                        $skipped++;
                        continue;
                    }

                    // Check for duplicates
                    $exists = $this->customerNumberRepository->findWhere([
                        'phone_number' => $phoneNumber,
                        'mailing_list_id' => $mailingListId,
                    ])->first();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    $createData = [
                        'phone_number' => $phoneNumber,
                        'name' => $name ?: null,
                        'mailing_list_id' => $mailingListId,
                    ];
                    
                    // Try to get telegram_id from contact if contact_id is in CSV
                    if (isset($data['contact_id']) && !empty($data['contact_id'])) {
                        $contact = NewslettersContact::find($data['contact_id']);
                        if ($contact && !empty($contact->telegram_user_id)) {
                            $createData['telegram_id'] = $contact->telegram_user_id;
                            $createData['contact_id'] = $contact->id;
                        }
                    }
                    // Also check if telegram_id is directly in CSV
                    if (isset($data['telegram_id']) && !empty($data['telegram_id'])) {
                        $createData['telegram_id'] = $data['telegram_id'];
                    }
                    
                    $this->customerNumberRepository->create($createData);

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    Log::error("CSV Import Error at row " . ($index + 2), [
                        'error' => $e->getMessage(),
                        'data' => $data ?? null,
                    ]);
                }
            }

            DB::commit();

            $message = trans('newsletters::app.admin.customer-numbers.import-success', ['count' => $imported]);
            if ($skipped > 0) {
                $message .= ' ' . trans('newsletters::app.admin.customer-numbers.import-skipped', ['count' => $skipped]);
            }

            session()->flash('success', $message);

            if (!empty($errors)) {
                session()->flash('warning', 'Some rows had errors: ' . implode('; ', array_slice($errors, 0, 5)));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CSV Import Failed', ['error' => $e->getMessage()]);
            session()->flash('error', trans('newsletters::app.admin.customer-numbers.import-failed') . ': ' . $e->getMessage());
        }

        return redirect()->route('admin.newsletters.customer-numbers.index');
    }

    /**
     * Get chat history for a customer number.
     */
    public function getChatHistory(Request $request)
    {
        $this->validate($request, [
            'customer_id' => 'required|exists:newsletters_customer_numbers,id',
        ]);

        try {
            $customerNumber = $this->customerNumberRepository->with(['whatsAppInstance'])->findOrFail($request->customer_id);

            if(!$customerNumber->whatsAppInstance){
                return response()->json([
                    'success' => true,
                    'chat_history' => trans('newsletters::app.admin.customer-numbers.no-whatsapp-instance'),
                    'customer_number' => $customerNumber,
                ]);
            }

            // Clear incoming_message flag when chat history is viewed
            if ($customerNumber->incoming_message) {
                $customerNumber->incoming_message = false;
                $customerNumber->save();
                
                // Broadcast the change to update the UI in real-time
                broadcast(new CustomerNumberMessageRead($customerNumber));
                
                Log::info('Incoming message flag cleared for customer number', [
                    'customer_number_id' => $customerNumber->id,
                    'phone_number' => $customerNumber->phone_number,
                ]);
            }

            try {
                $chatHistory = $this->whatsAppMailingService->getChatHistory($customerNumber->whatsAppInstance, $customerNumber->phone_number);
            } catch (\Exception $e) {
                Log::warning('Failed to retrieve chat history from WhatsApp service', [
                    'customer_number_id' => $customerNumber->id,
                    'error' => $e->getMessage(),
                ]);
                
                // Return empty chat history instead of error
                $chatHistory = trans('newsletters::app.admin.customer-numbers.chat-history-unavailable');
            }

            return response()->json([
                'success' => true,
                'customer_number' => $customerNumber,
                'chat_history' => $chatHistory,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve chat history', [
                'customer_id' => $request->customer_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.customer-numbers.chat-history-failed') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search customer numbers.
     */
    public function search(Request $request)
    {
        $this->validate($request, [
            'query' => 'required|string|min:2',
            'mailing_list_id' => 'nullable|exists:newsletters_mailing_lists,id',
        ]);

        try {
            $query = $this->customerNumberRepository->newQuery();
            
            $searchTerm = $request->query;
            
            $query->where(function($q) use ($searchTerm) {
                $q->where('phone_number', 'like', '%' . $searchTerm . '%')
                  ->orWhere('name', 'like', '%' . $searchTerm . '%');
            });
            
            if ($request->mailing_list_id) {
                $query->where('mailing_list_id', $request->mailing_list_id);
            }
            
            $results = $query->with(['mailingList', 'whatsAppInstance'])->limit(20)->get();
            
            return response()->json([
                'success' => true,
                'results' => $results,
                'count' => $results->count(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Customer number search failed', [
                'query' => $request->query,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.customer-numbers.search-failed'),
            ], 500);
        }
    }

    /**
     * Send reply message to customer.
     */
    public function sendReply(Request $request)
    {
        $this->validate($request, [
            'customer_number_id' => 'required|exists:newsletters_customer_numbers,id',
            'message' => 'required|string|min:1|max:4096',
        ]);

        try {
            $customerNumber = $this->customerNumberRepository->with(['whatsAppInstance'])->findOrFail($request->customer_number_id);

            if (!$customerNumber->whatsAppInstance) {
                return response()->json([
                    'success' => false,
                    'message' => trans('newsletters::app.admin.customer-numbers.no-whatsapp-instance'),
                ], 400);
            }

            // Send message via WhatsApp
            $messageId = $this->whatsAppMailingService->sendMessage(
                $customerNumber->whatsAppInstance,
                $customerNumber->phone_number,
                $request->message
            );

            if ($messageId) {
                Log::info('Reply message sent successfully', [
                    'customer_number_id' => $customerNumber->id,
                    'phone_number' => $customerNumber->phone_number,
                    'message_id' => $messageId,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => trans('newsletters::app.admin.customer-numbers.message-sent-success'),
                    'message_id' => $messageId,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.customer-numbers.message-sent-failed'),
            ], 500);

        } catch (\Exception $e) {
            Log::error('Failed to send reply message', [
                'customer_number_id' => $request->customer_number_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.customer-numbers.message-sent-failed') . ': ' . $e->getMessage(),
            ], 500);
        }
    }
}
