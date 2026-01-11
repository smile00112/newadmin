<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Jobs\ProcessWhatsAppBatchByInstances;
use Webkul\Newsletters\Models\StopList;
use Webkul\Newsletters\Repositories\MailingListRepository;
use Webkul\Newsletters\Repositories\VacapInstanceRepository;
use Webkul\Newsletters\Repositories\CustomerNumberRepository;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;
use Webkul\Newsletters\Repositories\MailInstanceRepository;
use Webkul\Newsletters\Repositories\TelegramBotInstanceRepository;
use Webkul\Newsletters\Repositories\ContactGroupRepository;
use Webkul\Newsletters\Repositories\ContactFilterRepository;
use Webkul\Newsletters\Http\Controllers\Admin\ContactFilterController;
use Webkul\Newsletters\Models\NewslettersContact;
use Webkul\Newsletters\Models\VacapInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Webkul\Newsletters\Jobs\ProcessWhatsAppMailingList;
use Webkul\Newsletters\Services\MailingListStarterService;

class MailingListController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected MailingListRepository $mailingListRepository,
        protected VacapInstanceRepository $vacapInstanceRepository,
        protected CustomerNumberRepository $customerNumberRepository,
        protected CompanyAccountRepository $accountRepository,
        protected MailInstanceRepository $mailInstanceRepository,
        protected TelegramBotInstanceRepository $telegramBotInstanceRepository,
        protected ContactGroupRepository $contactGroupRepository,
        protected ContactFilterRepository $contactFilterRepository,
        protected ContactFilterController $contactFilterController,
        protected MailingListStarterService $mailingListStarterService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mailingLists = $this->mailingListRepository->with('customerNumbers')->withCount([
            'customerNumbers as numbers_delivered' => function ($query) {
                $query->where('sending', true)->orWhere('send_error', true);
            },
            'customerNumbers as numbers_viewed' => function ($query) {
                $query->where('viewed', true);
            },
            'customerNumbers as incoming_messages_count' => function ($query) {
                $query->where('incoming_message', true);
            }
        ])->orderBy('created_at', 'desc')->all();
        
        // Get account balance for current admin
        $hasBalance = true;
        $accountBalance = 0;
        $admin = auth()->guard('admin')->user();
        if ($admin && $admin->company_id) {
            $account = $this->accountRepository->getOrCreateForCompany($admin->company_id);
            $accountBalance = $account->balance;
            $hasBalance = $account->balance > 0;
        }
        
        return view('newsletters::admin.mailing-lists.index', compact('mailingLists', 'hasBalance', 'accountBalance'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get available instances for selection
        $mailInstances = $this->mailInstanceRepository->getAllForCompany();
        $telegramInstances = $this->telegramBotInstanceRepository->getAllForCompany();
        $whatsappInstances = $this->vacapInstanceRepository->all();
        
        // Get contact groups for filter selection
        $contactGroups = $this->contactGroupRepository->all();
        
        return view('newsletters::admin.mailing-lists.create', compact('mailInstances', 'telegramInstances', 'whatsappInstances', 'contactGroups'));
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
            'mailing_hours_from' => ['nullable', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'mailing_hours_to' => ['nullable', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'message_delay_from' => 'nullable|integer|min:1|max:3600',
            'message_delay_to' => 'nullable|integer|min:1|max:3600',
            'max_messages_per_instance' => 'nullable|integer|min:1',
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,avi,mov,webm|max:10240', // 10MB max

            // WhatsApp Instances validation
            'whatsapp_instances.*.link_name' => 'required|string|max:255',
            'whatsapp_instances.*.login' => 'required|string|max:255',
            'whatsapp_instances.*.password' => 'required|string|max:255',

            // Contact Group and Filter validation
            'contact_group_id' => 'nullable|integer|exists:newsletters_contact_groups,id',
            'filter_id' => 'nullable|integer|exists:newsletters_contact_filters,id|required_with:contact_group_id',

            // Customer Numbers validation - optional if filter is used
            'customer_numbers.*.phone_number' => 'nullable|string|max:20',
            'customer_numbers.*.name' => 'nullable|string|max:255',

            // Auto-reply validation
            'auto_reply_enabled' => 'nullable|boolean',
            'auto_replies' => 'nullable|array',
            'auto_replies.*.phrase' => 'required_with:auto_replies|string|max:500',
            'auto_replies.*.response' => 'required_with:auto_replies|string|max:2000',
        ]);

        try {
            DB::beginTransaction();
            Log::info('Database transaction started for mailing list creation');

            // Handle file upload
            $messageLinks = null;
            if ($request->hasFile('media_file')) {
                $file = $request->file('media_file');
                $path = $file->store('newsletters/media', 'public');
                $url = Storage::url($path);

                // Убеждаемся, что URL полный (начинается с http:// или https://)
                if (!preg_match('/^https?:\/\//', $url)) {
                    $url = url($url);
                }

                $messageLinks = [
                    [
                        'type' => strpos($file->getMimeType(), 'image/') === 0 ? 'image' : 'video',
                        'url' => $url,
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]
                ];
            }

            // Process auto-replies
            $autoReplies = null;
            if ($request->has('auto_replies') && is_array($request->input('auto_replies'))) {
                $autoReplies = array_filter($request->input('auto_replies'), function($reply) {
                    return !empty($reply['phrase']) && !empty($reply['response']);
                });
                // Re-index array and ensure proper structure
                $autoReplies = array_values($autoReplies);
                if (empty($autoReplies)) {
                    $autoReplies = null;
                }
            }

            // Create mailing list first
            $mailingListData = [
                'message_text' => $request->input('message_text'),
                'message_links' => $messageLinks,
                'active' => (bool) $request->input('active', false),
                'start_at' => $request->input('start_at'),
                'mailing_hours_from' => $request->input('mailing_hours_from'),
                'mailing_hours_to' => $request->input('mailing_hours_to'),
                'message_delay_from' => $request->input('message_delay_from', 5),
                'message_delay_to' => $request->input('message_delay_to', 5),
                'max_messages_per_instance' => $request->input('max_messages_per_instance') ?: 500,
                'channel_type' => $request->input('channel_type', 'whatsapp'),
                'filter_id' => $request->input('filter_id'),
                'auto_reply_enabled' => (bool) $request->input('auto_reply_enabled', false),
                'auto_replies' => $autoReplies,
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

            // Handle WhatsApp instances - either select existing or create new
            $whatsappInstanceIds = [];
            
            // Check if user selected existing WhatsApp instances
            if ($request->has('whatsapp_instance_ids') && is_array($request->input('whatsapp_instance_ids'))) {
                $whatsappInstanceIds = array_filter($request->input('whatsapp_instance_ids'));
            }
            
            // Create new WhatsApp instances if provided
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
                    // Remove mailing_list_id if present
                    unset($instanceData['mailing_list_id']);
                    // company_id will be automatically set by repository

                    Log::info('Creating WhatsApp instance', [
                        'instance_index' => $index,
                        'link_name' => $instanceData['link_name'],
                        'login' => $instanceData['login'],
                    ]);

                    $instance = $this->vacapInstanceRepository->create($instanceData);
                    $whatsappInstanceIds[] = $instance->id;

                    Log::info('WhatsApp instance created successfully', [
                        'instance_id' => $instance->id,
                        'link_name' => $instance->link_name,
                        'login' => $instance->login,
                    ]);
                }
            }
            
            // Attach WhatsApp instances to mailing list
            if (!empty($whatsappInstanceIds)) {
                $mailingList->whatsappInstances()->sync($whatsappInstanceIds);
                Log::info('Attached WhatsApp instances to mailing list', [
                    'mailing_list_id' => $mailingList->id,
                    'instance_ids' => $whatsappInstanceIds,
                ]);
            }

            // Handle mail instances - either select existing or create new
            $mailInstanceIds = [];
            
            if ($request->input('channel_type') === 'email') {
                // Check if user selected existing mail instances
                if ($request->has('mail_instance_ids') && is_array($request->input('mail_instance_ids'))) {
                    $mailInstanceIds = array_filter($request->input('mail_instance_ids'));
                }
                
                // Create new mail instances if provided
                if ($request->has('mail_instances')) {
                    $mailInstances = $request->input('mail_instances');
                    $validInstances = array_filter($mailInstances, function($instance) {
                        return !empty($instance['host']) && !empty($instance['username']) && !empty($instance['password']) && !empty($instance['from_email']);
                    });

                    Log::info('Processing mail instances', [
                        'total_instances' => count($mailInstances),
                        'valid_instances' => count($validInstances),
                        'mailing_list_id' => $mailingList->id,
                    ]);

                    foreach ($validInstances as $index => $instanceData) {
                        // Remove mailing_list_id if present
                        unset($instanceData['mailing_list_id']);

                        Log::info('Creating mail instance', [
                            'instance_index' => $index,
                            'host' => $instanceData['host'],
                            'username' => $instanceData['username'],
                        ]);

                        $instance = $this->mailInstanceRepository->create($instanceData);
                        $mailInstanceIds[] = $instance->id;

                        Log::info('Mail instance created successfully', [
                            'instance_id' => $instance->id,
                            'host' => $instance->host,
                            'username' => $instance->username,
                        ]);
                    }
                }
                
                // Attach mail instances to mailing list
                if (!empty($mailInstanceIds)) {
                    $mailingList->mailInstances()->sync($mailInstanceIds);
                    Log::info('Attached mail instances to mailing list', [
                        'mailing_list_id' => $mailingList->id,
                        'instance_ids' => $mailInstanceIds,
                    ]);
                }
            }
            
            // Handle telegram instances - either select existing or create new
            $telegramInstanceIds = [];
            
            if ($request->input('channel_type') === 'telegram') {
                // Check if user selected existing telegram instances
                if ($request->has('telegram_instance_ids') && is_array($request->input('telegram_instance_ids'))) {
                    $telegramInstanceIds = array_filter($request->input('telegram_instance_ids'));
                }
                
                // Create new telegram instances if provided
                if ($request->has('telegram_instances')) {
                    $telegramInstances = $request->input('telegram_instances');
                    $validInstances = array_filter($telegramInstances, function($instance) {
                        return !empty($instance['bot_token']);
                    });

                    Log::info('Processing telegram instances', [
                        'total_instances' => count($telegramInstances),
                        'valid_instances' => count($validInstances),
                        'mailing_list_id' => $mailingList->id,
                    ]);

                    foreach ($validInstances as $index => $instanceData) {
                        // Remove mailing_list_id if present
                        unset($instanceData['mailing_list_id']);

                        Log::info('Creating telegram instance', [
                            'instance_index' => $index,
                            'bot_username' => $instanceData['bot_username'] ?? null,
                        ]);

                        $instance = $this->telegramBotInstanceRepository->create($instanceData);
                        $telegramInstanceIds[] = $instance->id;

                        Log::info('Telegram instance created successfully', [
                            'instance_id' => $instance->id,
                            'bot_username' => $instance->bot_username,
                        ]);
                    }
                }
                
                // Attach telegram instances to mailing list
                if (!empty($telegramInstanceIds)) {
                    $mailingList->telegramInstances()->sync($telegramInstanceIds);
                    Log::info('Attached telegram instances to mailing list', [
                        'mailing_list_id' => $mailingList->id,
                        'instance_ids' => $telegramInstanceIds,
                    ]);
                }
            }

            // Get contacts from filter if filter_id is provided
            $contactsFromFilter = [];
            if ($request->has('filter_id') && $request->filter_id) {
                try {
                    $filter = $this->contactFilterRepository->findOrFail($request->filter_id);
                    $response = $this->contactFilterController->applyFilter($filter->id);
                    $responseData = json_decode($response->getContent(), true);
                    
                    if (isset($responseData['contacts']) && is_array($responseData['contacts'])) {
                        $contactsFromFilter = $responseData['contacts'];
                        
                        Log::info('Got contacts from filter', [
                            'filter_id' => $filter->id,
                            'contacts_count' => count($contactsFromFilter),
                            'mailing_list_id' => $mailingList->id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to get contacts from filter', [
                        'filter_id' => $request->filter_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Prepare customer numbers array
            $allCustomerNumbers = [];
            $seenPhones = []; // Track phone numbers to avoid duplicates within the array

            // Add customer numbers from filter
            foreach ($contactsFromFilter as $contactData) {
                $contact = is_array($contactData) ? (object) $contactData : $contactData;
                if (!empty($contact->phone) && !empty($contact->full_name)) {
                    $phoneKey = $contact->phone . '-' . $mailingList->id;
                    // Skip if we've already seen this phone number in the array
                    if (!isset($seenPhones[$phoneKey])) {
                        // Get telegram_id from contact if available
                        $telegramId = null;
                        if (!empty($contact->id)) {
                            $contactModel = NewslettersContact::find($contact->id);
                            if ($contactModel && !empty($contactModel->telegram_user_id)) {
                                $telegramId = $contactModel->telegram_user_id;
                            }
                        }
                        // Fallback to telegram_user_id from contactData if available
                        if (empty($telegramId) && !empty($contact->telegram_user_id)) {
                            $telegramId = $contact->telegram_user_id;
                        }
                        
                        $allCustomerNumbers[] = [
                            'phone_number' => $contact->phone,
                            'name' => $contact->full_name ?? '',
                            'email' => $contact->email ?? null,
                            'telegram_id' => $telegramId,
                            'mailing_list_id' => $mailingList->id,
                            'contact_id' => $contact->id ?? null,
                        ];
                        $seenPhones[$phoneKey] = true;
                    }
                }
            }

            // Add manually entered customer numbers
            if ($request->has('customer_numbers')) {
                $customerNumbers = $request->input('customer_numbers');
                $validCustomers = array_filter($customerNumbers, function($customer) {
                    return !empty($customer['phone_number']) && !empty($customer['name']);
                });

                foreach ($validCustomers as $customerData) {
                    $phoneKey = $customerData['phone_number'] . '-' . $mailingList->id;
                    // Skip if we've already seen this phone number in the array
                    if (!isset($seenPhones[$phoneKey])) {
                        // Get telegram_id from contact if contact_id is provided
                        $telegramId = $customerData['telegram_id'] ?? null;
                        if (empty($telegramId) && !empty($customerData['contact_id'])) {
                            $contact = NewslettersContact::find($customerData['contact_id']);
                            if ($contact && !empty($contact->telegram_user_id)) {
                                $telegramId = $contact->telegram_user_id;
                            }
                        }
                        
                        $allCustomerNumbers[] = [
                            'phone_number' => $customerData['phone_number'],
                            'name' => $customerData['name'],
                            'email' => $customerData['email'] ?? null,
                            'telegram_id' => $telegramId,
                            'mailing_list_id' => $mailingList->id,
                            'contact_id' => $customerData['contact_id'] ?? null,
                        ];
                        $seenPhones[$phoneKey] = true;
                    }
                }
            }

            // Create customer numbers
            if (!empty($allCustomerNumbers)) {
                Log::info('Processing customer numbers', [
                    'total_customers' => count($allCustomerNumbers),
                    'from_filter' => count($contactsFromFilter),
                    'manual' => $request->has('customer_numbers') ? count($request->input('customer_numbers')) : 0,
                    'mailing_list_id' => $mailingList->id,
                ]);

                foreach ($allCustomerNumbers as $index => $customerData) {
                    // company_id will be automatically set by repository

                    // Check if customer with same phone_number and mailing_list_id already exists
                    $existingCustomerByPhone = $this->customerNumberRepository
                        ->where('phone_number', $customerData['phone_number'])
                        ->where('mailing_list_id', $mailingList->id)
                        ->first();

                    if ($existingCustomerByPhone) {
                        // Skip duplicate - log and continue
                        Log::info('Skipping duplicate customer number', [
                            'customer_index' => $index,
                            'existing_customer_id' => $existingCustomerByPhone->id,
                            'phone_number' => $customerData['phone_number'],
                            'name' => $customerData['name'],
                            'mailing_list_id' => $mailingList->id,
                        ]);
                        continue;
                    }

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
        // Only load WhatsApp instances if channel_type is not email
        $whatsappInstances = $mailingList->channel_type !== 'email' 
            ? $mailingList->whatsappInstances 
            : collect();

        // Get customer numbers with sorting and pagination
        $customerNumbers = $this->customerNumberRepository
            ->where('mailing_list_id', $id)
            ->with('whatsAppInstance')
            ->orderBy('incoming_message', 'desc') // incoming_message = 1 first
            ->orderBy('id', 'desc') // then by id desc
            ->limit(50)
            ->get();

        $totalCustomerNumbers = $this->customerNumberRepository->where('mailing_list_id', $id)->count();

        // Get admin users and customers for user numbers
        $adminUsers = \Webkul\User\Models\Admin::select('id', 'name', 'email')->get();
        $customers = \Webkul\Customer\Models\Customer::select('id', 'first_name', 'last_name', 'email', 'phone')->get();
        
        // Get account balance for current admin
        $hasBalance = true;
        $accountBalance = 0;
        $admin = auth()->guard('admin')->user();
        if ($admin && $admin->company_id) {
            $account = $this->accountRepository->getOrCreateForCompany($admin->company_id);
            $accountBalance = $account->balance;
            $hasBalance = $account->balance > 0;
        }

        // Combine admin users and customers into a unified user numbers array
        $userNumbers = collect();

        // Add admin users
        foreach ($adminUsers as $admin) {
            $userNumbers->push((object)[
                'id' => 'admin_' . $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'type' => 'admin'
            ]);
        }

        // Add customers
        foreach ($customers as $customer) {
            $userNumbers->push((object)[
                'id' => 'customer_' . $customer->id,
                'name' => $customer->first_name . ' ' . $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'type' => 'customer'
            ]);
        }

        // Get all available instances for selection
        $mailInstances = $this->mailInstanceRepository->getAllForCompany();
        $telegramInstances = $this->telegramBotInstanceRepository->getAllForCompany();
        $allWhatsappInstances = $this->vacapInstanceRepository->all();
        
        // Get selected instance IDs for pre-selection
        $selectedMailInstanceIds = $mailingList->mailInstances->pluck('id')->toArray();
        $selectedTelegramInstanceIds = $mailingList->telegramInstances->pluck('id')->toArray();
        $selectedWhatsappInstanceIds = $whatsappInstances->pluck('id')->toArray();
        
        // Get contact groups for filter selection
        $contactGroups = $this->contactGroupRepository->all();
        
        // Get saved filter_id and contact_group_id from database
        $savedFilterId = $mailingList->filter_id;
        $savedContactGroupId = null;
        
        // If filter_id exists, get contact_group_id from the filter
        if ($savedFilterId) {
            try {
                $filter = $this->contactFilterRepository->findOrFail($savedFilterId);
                $savedContactGroupId = $filter->contact_group_id;
                
                Log::info('Loaded filter from database', [
                    'mailing_list_id' => $id,
                    'filter_id' => $savedFilterId,
                    'contact_group_id' => $savedContactGroupId,
                ]);
            } catch (\Exception $e) {
                Log::warning('Filter not found in database', [
                    'mailing_list_id' => $id,
                    'filter_id' => $savedFilterId,
                    'error' => $e->getMessage(),
                ]);
                // If filter was deleted, reset filter_id
                $savedFilterId = null;
            }
        }
        
        // Fallback: Try to determine contact_group_id from customer numbers if filter_id is not set
        if (!$savedContactGroupId) {
            $contactIds = $customerNumbers->whereNotNull('contact_id')->pluck('contact_id')->unique()->toArray();
            if (!empty($contactIds)) {
                $firstContact = NewslettersContact::whereIn('id', $contactIds)->first();
                if ($firstContact) {
                    $savedContactGroupId = $firstContact->contact_group_id;
                }
            }
        }
        
        Log::info('Saved filter determination result', [
            'mailing_list_id' => $id,
            'saved_contact_group_id' => $savedContactGroupId,
            'saved_filter_id' => $savedFilterId,
        ]);
        
        return view('newsletters::admin.mailing-lists.edit', compact(
            'mailingList', 
            'whatsappInstances', 
            'customerNumbers', 
            'userNumbers', 
            'totalCustomerNumbers', 
            'hasBalance', 
            'accountBalance',
            'mailInstances',
            'telegramInstances',
            'allWhatsappInstances',
            'selectedMailInstanceIds',
            'selectedTelegramInstanceIds',
            'selectedWhatsappInstanceIds',
            'contactGroups',
            'savedContactGroupId',
            'savedFilterId'
        ));
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
            'mailing_hours_from' => ['nullable', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'mailing_hours_to' => ['nullable', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'message_delay_from' => 'nullable|integer|min:1|max:3600',
            'message_delay_to' => 'nullable|integer|min:1|max:3600',
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,avi,mov,webm|max:10240', // 10MB max

            // WhatsApp Instances validation
            'whatsapp_instances.*.link_name' => 'nullable|string|max:255',
            'whatsapp_instances.*.login' => 'nullable|string|max:255',
            'whatsapp_instances.*.password' => 'nullable|string|max:255',

            // Contact Group and Filter validation
            'contact_group_id' => 'nullable|integer|exists:newsletters_contact_groups,id',
            'filter_id' => 'nullable|integer|exists:newsletters_contact_filters,id|required_with:contact_group_id',

            // Customer Numbers validation - nullable because we filter them later
            'customer_numbers.*.phone_number' => 'nullable|string|max:20',
            'customer_numbers.*.name' => 'nullable|string|max:255',

            // Auto-reply validation
            'auto_reply_enabled' => 'nullable|boolean',
            'auto_replies' => 'nullable|array',
            'auto_replies.*.phrase' => 'required_with:auto_replies|string|max:500',
            'auto_replies.*.response' => 'required_with:auto_replies|string|max:2000',
        ]);

        try {
            DB::beginTransaction();
            Log::info('Database transaction started for mailing list update', ['mailing_list_id' => $id]);

            // Get existing mailing list to preserve existing message_links if no new file is uploaded
            $existingMailingList = $this->mailingListRepository->find($id);
            $messageLinks = $existingMailingList->message_links ?? null;

            // Handle file upload
            if ($request->hasFile('media_file')) {
                // Delete old file if exists
                if ($messageLinks && isset($messageLinks[0]['path'])) {
                    Storage::disk('public')->delete($messageLinks[0]['path']);
                }

                $file = $request->file('media_file');
                $path = $file->store('newsletters/media', 'public');
                $url = Storage::url($path);

                // Убеждаемся, что URL полный (начинается с http:// или https://)
                if (!preg_match('/^https?:\/\//', $url)) {
                    $url = url($url);
                }

                $messageLinks = [
                    [
                        'type' => strpos($file->getMimeType(), 'image/') === 0 ? 'image' : 'video',
                        'url' => $url,
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]
                ];
            }

            // Process auto-replies
            $autoReplies = null;
            if ($request->has('auto_replies') && is_array($request->input('auto_replies'))) {
                $autoReplies = array_filter($request->input('auto_replies'), function($reply) {
                    return !empty($reply['phrase']) && !empty($reply['response']);
                });
                // Re-index array and ensure proper structure
                $autoReplies = array_values($autoReplies);
                if (empty($autoReplies)) {
                    $autoReplies = null;
                }
            }

            // Update mailing list
            $mailingListData = [
                'message_text' => $request->input('message_text'),
                'message_links' => $messageLinks,
                'active' => (bool) $request->input('active', false),
                'start_at' => $request->input('start_at'),
                'mailing_hours_from' => $request->input('mailing_hours_from'),
                'mailing_hours_to' => $request->input('mailing_hours_to'),
                'message_delay_from' => $request->input('message_delay_from', 5),
                'message_delay_to' => $request->input('message_delay_to', 5),
                'max_messages_per_instance' => $request->input('max_messages_per_instance') ?: 500,
                'channel_type' => $request->input('channel_type', $existingMailingList->channel_type ?? 'whatsapp'),
                'filter_id' => $request->input('filter_id'),
                'auto_reply_enabled' => (bool) $request->input('auto_reply_enabled', false),
                'auto_replies' => $autoReplies,
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

            // Handle WhatsApp instances - sync selected and created instances
            $whatsappInstanceIds = [];
            
            // Get selected existing WhatsApp instances
            if ($request->has('whatsapp_instance_ids') && is_array($request->input('whatsapp_instance_ids'))) {
                $whatsappInstanceIds = array_filter($request->input('whatsapp_instance_ids'));
            }
            
            // Create or update WhatsApp instances if provided
            if ($request->has('whatsapp_instances')) {
                $whatsappInstances = $request->input('whatsapp_instances');
                $validInstances = array_filter($whatsappInstances, function($instance) {
                    return !empty($instance['link_name']) && !empty($instance['login']) && !empty($instance['password']);
                });

                Log::info('Processing WhatsApp instances for update', [
                    'total_instances' => count($whatsappInstances),
                    'valid_instances' => count($validInstances),
                    'mailing_list_id' => $id,
                ]);

                foreach ($validInstances as $index => $instanceData) {
                    // Remove mailing_list_id if present
                    unset($instanceData['mailing_list_id']);

                    // Check if this is an update (has ID) or new instance
                    if (isset($instanceData['id']) && !empty($instanceData['id'])) {
                        // Update existing instance
                        Log::info('Updating existing WhatsApp instance', [
                            'instance_id' => $instanceData['id'],
                            'instance_index' => $index,
                            'link_name' => $instanceData['link_name'],
                            'login' => $instanceData['login'],
                        ]);

                        $this->vacapInstanceRepository->update($instanceData, $instanceData['id']);
                        $whatsappInstanceIds[] = $instanceData['id'];

                        Log::info('WhatsApp instance updated successfully', [
                            'instance_id' => $instanceData['id'],
                            'link_name' => $instanceData['link_name'],
                            'login' => $instanceData['login'],
                        ]);
                    } else {
                        // Create new instance
                        Log::info('Creating new WhatsApp instance', [
                            'instance_index' => $index,
                            'link_name' => $instanceData['link_name'],
                            'login' => $instanceData['login'],
                        ]);

                        $instance = $this->vacapInstanceRepository->create($instanceData);
                        $whatsappInstanceIds[] = $instance->id;

                        Log::info('WhatsApp instance created successfully', [
                            'instance_id' => $instance->id,
                            'link_name' => $instance->link_name,
                            'login' => $instance->login,
                        ]);
                    }
                }
            }
            
            // Sync WhatsApp instances with mailing list
            $mailingList->whatsappInstances()->sync($whatsappInstanceIds);
            Log::info('Synced WhatsApp instances with mailing list', [
                'mailing_list_id' => $id,
                'instance_ids' => $whatsappInstanceIds,
            ]);

            // Handle mail instances - sync selected and created instances
            $mailInstanceIds = [];
            
            if ($request->input('channel_type') === 'email') {
                // Get selected existing mail instances
                if ($request->has('mail_instance_ids') && is_array($request->input('mail_instance_ids'))) {
                    $mailInstanceIds = array_filter($request->input('mail_instance_ids'));
                }
                
                // Create or update mail instances if provided
                if ($request->has('mail_instances')) {
                    $mailInstances = $request->input('mail_instances');
                    $validInstances = array_filter($mailInstances, function($instance) {
                        return !empty($instance['host']) && !empty($instance['username']) && !empty($instance['password']) && !empty($instance['from_email']);
                    });

                    Log::info('Processing mail instances for update', [
                        'total_instances' => count($mailInstances),
                        'valid_instances' => count($validInstances),
                        'mailing_list_id' => $id,
                    ]);

                    foreach ($validInstances as $index => $instanceData) {
                        // Remove mailing_list_id if present
                        unset($instanceData['mailing_list_id']);

                        // Check if this is an update (has ID) or new instance
                        if (isset($instanceData['id']) && !empty($instanceData['id'])) {
                            // Update existing instance
                            Log::info('Updating existing mail instance', [
                                'instance_id' => $instanceData['id'],
                                'instance_index' => $index,
                                'host' => $instanceData['host'],
                            ]);

                            $this->mailInstanceRepository->update($instanceData, $instanceData['id']);
                            $mailInstanceIds[] = $instanceData['id'];

                            Log::info('Mail instance updated successfully', [
                                'instance_id' => $instanceData['id'],
                                'host' => $instanceData['host'],
                            ]);
                        } else {
                            // Create new instance
                            Log::info('Creating new mail instance', [
                                'instance_index' => $index,
                                'host' => $instanceData['host'],
                            ]);

                            $instance = $this->mailInstanceRepository->create($instanceData);
                            $mailInstanceIds[] = $instance->id;

                            Log::info('Mail instance created successfully', [
                                'instance_id' => $instance->id,
                                'host' => $instance->host,
                            ]);
                        }
                    }
                }
                
                // Sync mail instances with mailing list
                $mailingList->mailInstances()->sync($mailInstanceIds);
                Log::info('Synced mail instances with mailing list', [
                    'mailing_list_id' => $id,
                    'instance_ids' => $mailInstanceIds,
                ]);
            }
            
            // Handle telegram instances - sync selected and created instances
            $telegramInstanceIds = [];
            
            if ($request->input('channel_type') === 'telegram') {
                // Get selected existing telegram instances
                if ($request->has('telegram_instance_ids') && is_array($request->input('telegram_instance_ids'))) {
                    $telegramInstanceIds = array_filter($request->input('telegram_instance_ids'));
                }
                
                // Create or update telegram instances if provided
                if ($request->has('telegram_instances')) {
                    $telegramInstances = $request->input('telegram_instances');
                    $validInstances = array_filter($telegramInstances, function($instance) {
                        return !empty($instance['bot_token']);
                    });

                    Log::info('Processing telegram instances for update', [
                        'total_instances' => count($telegramInstances),
                        'valid_instances' => count($validInstances),
                        'mailing_list_id' => $id,
                    ]);

                    foreach ($validInstances as $index => $instanceData) {
                        // Remove mailing_list_id if present
                        unset($instanceData['mailing_list_id']);

                        // Check if this is an update (has ID) or new instance
                        if (isset($instanceData['id']) && !empty($instanceData['id'])) {
                            // Update existing instance
                            Log::info('Updating existing telegram instance', [
                                'instance_id' => $instanceData['id'],
                                'instance_index' => $index,
                                'bot_username' => $instanceData['bot_username'] ?? null,
                            ]);

                            $this->telegramBotInstanceRepository->update($instanceData, $instanceData['id']);
                            $telegramInstanceIds[] = $instanceData['id'];

                            Log::info('Telegram instance updated successfully', [
                                'instance_id' => $instanceData['id'],
                                'bot_username' => $instanceData['bot_username'] ?? null,
                            ]);
                        } else {
                            // Create new instance
                            Log::info('Creating new telegram instance', [
                                'instance_index' => $index,
                                'bot_username' => $instanceData['bot_username'] ?? null,
                            ]);

                            $instance = $this->telegramBotInstanceRepository->create($instanceData);
                            $telegramInstanceIds[] = $instance->id;

                            Log::info('Telegram instance created successfully', [
                                'instance_id' => $instance->id,
                                'bot_username' => $instance->bot_username,
                            ]);
                        }
                    }
                }
                
                // Sync telegram instances with mailing list
                $mailingList->telegramInstances()->sync($telegramInstanceIds);
                Log::info('Synced telegram instances with mailing list', [
                    'mailing_list_id' => $id,
                    'instance_ids' => $telegramInstanceIds,
                ]);
            }

            // Get contacts from filter if filter_id is provided
            $contactsFromFilter = [];
            if ($request->has('filter_id') && $request->filter_id) {
                try {
                    $filter = $this->contactFilterRepository->findOrFail($request->filter_id);
                    $response = $this->contactFilterController->applyFilter($filter->id);
                    $responseData = json_decode($response->getContent(), true);
                    
                    if (isset($responseData['contacts']) && is_array($responseData['contacts'])) {
                        $contactsFromFilter = $responseData['contacts'];
                        
                        Log::info('Got contacts from filter for update', [
                            'filter_id' => $filter->id,
                            'contacts_count' => count($contactsFromFilter),
                            'mailing_list_id' => $id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to get contacts from filter for update', [
                        'filter_id' => $request->filter_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Prepare customer numbers array
            $allCustomerNumbers = [];
            $seenPhones = []; // Track phone numbers to avoid duplicates within the array

            // Add customer numbers from filter
            foreach ($contactsFromFilter as $contactData) {
                $contact = is_array($contactData) ? (object) $contactData : $contactData;
                if (!empty($contact->phone) && !empty($contact->full_name)) {
                    $phoneKey = $contact->phone . '-' . $id;
                    // Skip if we've already seen this phone number in the array
                    if (!isset($seenPhones[$phoneKey])) {
                        // Get telegram_id from contact if available
                        $telegramId = null;
                        if (!empty($contact->id)) {
                            $contactModel = NewslettersContact::find($contact->id);
                            if ($contactModel && !empty($contactModel->telegram_user_id)) {
                                $telegramId = $contactModel->telegram_user_id;
                            }
                        }
                        // Fallback to telegram_user_id from contactData if available
                        if (empty($telegramId) && !empty($contact->telegram_user_id)) {
                            $telegramId = $contact->telegram_user_id;
                        }
                        
                        $allCustomerNumbers[] = [
                            'phone_number' => $contact->phone,
                            'name' => $contact->full_name ?? '',
                            'email' => $contact->email ?? null,
                            'telegram_id' => $telegramId,
                            'mailing_list_id' => $id,
                            'contact_id' => $contact->id ?? null,
                        ];
                        $seenPhones[$phoneKey] = true;
                    }
                }
            }

            // Add manually entered customer numbers
            if ($request->has('customer_numbers')) {
                $customerNumbers = $request->input('customer_numbers');

                // Filter out empty entries and validate required fields
                // Note: We need phone_number and name, but id is optional (new customers won't have it)
                $validCustomers = array_filter($customerNumbers, function($customer) {
                    return !empty($customer['phone_number']) && !empty($customer['name']);
                });

                foreach ($validCustomers as $customerData) {
                    $phoneKey = $customerData['phone_number'] . '-' . $id;
                    // Skip if we've already seen this phone number in the array
                    if (!isset($seenPhones[$phoneKey])) {
                        // Get telegram_id from contact if contact_id is provided
                        $telegramId = $customerData['telegram_id'] ?? null;
                        if (empty($telegramId) && !empty($customerData['contact_id'])) {
                            $contact = NewslettersContact::find($customerData['contact_id']);
                            if ($contact && !empty($contact->telegram_user_id)) {
                                $telegramId = $contact->telegram_user_id;
                            }
                        }
                        
                        $allCustomerNumbers[] = [
                            'phone_number' => $customerData['phone_number'],
                            'name' => $customerData['name'],
                            'email' => $customerData['email'] ?? null,
                            'telegram_id' => $telegramId,
                            'mailing_list_id' => $id,
                            'contact_id' => $customerData['contact_id'] ?? null,
                            'id' => $customerData['id'] ?? null, // Preserve ID for updates
                            'delivered' => $customerData['delivered'] ?? null,
                            'viewed' => $customerData['viewed'] ?? null,
                            'incoming_message' => $customerData['incoming_message'] ?? null,
                            'whatsapp_instance_id' => $customerData['whatsapp_instance_id'] ?? null,
                        ];
                        $seenPhones[$phoneKey] = true;
                    }
                }
            }

            // Update customer numbers
            if (!empty($allCustomerNumbers) || $request->has('customer_numbers')) {
                Log::info('Processing customer numbers for update', [
                    'total_customers' => count($allCustomerNumbers),
                    'from_filter' => count($contactsFromFilter),
                    'manual' => $request->has('customer_numbers') ? count($request->input('customer_numbers')) : 0,
                    'mailing_list_id' => $id,
                ]);

                // Get existing customers
                $existingCustomers = $this->customerNumberRepository->where('mailing_list_id', $id)->get()->keyBy('id');
                $processedCustomerIds = [];

                foreach ($allCustomerNumbers as $index => $customerData) {
                    // Check if this is an update (has ID) or new customer
                    if (isset($customerData['id']) && !empty($customerData['id']) && $existingCustomers->has($customerData['id'])) {
                        // Update existing customer
                        $existingCustomer = $existingCustomers->get($customerData['id']);

                        Log::info('Updating existing customer number', [
                            'customer_id' => $existingCustomer->id,
                            'customer_index' => $index,
                            'phone_number' => $customerData['phone_number'],
                            'name' => $customerData['name'],
                            'delivered' => $customerData['delivered'] ?? 0,
                            'viewed' => $customerData['viewed'] ?? 0,
                            'incoming_message' => $customerData['incoming_message'] ?? 0,
                            'whatsapp_instance_id' => $customerData['whatsapp_instance_id'] ?? null,
                            'mailing_list_id' => $id,
                        ]);

                        // Prepare update data (exclude ID and empty/null fields that shouldn't be updated)
                        $updateData = [
                            'phone_number' => $customerData['phone_number'],
                            'name' => $customerData['name'],
                            'mailing_list_id' => $id,
                        ];

                        // Add email and contact_id if provided
                        if (isset($customerData['email'])) {
                            $updateData['email'] = $customerData['email'];
                        }
                        if (isset($customerData['contact_id'])) {
                            $updateData['contact_id'] = $customerData['contact_id'];
                        }
                        // Add telegram_id if provided
                        if (isset($customerData['telegram_id'])) {
                            $updateData['telegram_id'] = $customerData['telegram_id'];
                        }

                        // Only include these fields if they are explicitly set and not empty
                        if (isset($customerData['delivered'])) {
                            $updateData['delivered'] = (bool)$customerData['delivered'];
                        }
                        if (isset($customerData['viewed'])) {
                            $updateData['viewed'] = (bool)$customerData['viewed'];
                        }
                        if (isset($customerData['incoming_message'])) {
                            $updateData['incoming_message'] = (bool)$customerData['incoming_message'];
                        }
                        // Only update whatsapp_instance_id if it's explicitly provided and not empty
                        if (isset($customerData['whatsapp_instance_id']) && !empty($customerData['whatsapp_instance_id'])) {
                            $updateData['whatsapp_instance_id'] = $customerData['whatsapp_instance_id'];
                        }

                        $this->customerNumberRepository->update($updateData, $existingCustomer->id);
                        $processedCustomerIds[] = $existingCustomer->id;

                        Log::info('Customer number updated successfully', [
                            'customer_id' => $existingCustomer->id,
                            'phone_number' => $customerData['phone_number'],
                            'name' => $customerData['name'],
                            'mailing_list_id' => $id,
                        ]);
                    } else {
                        // Create new customer - but first check for duplicates by phone_number
                        Log::info('Creating new customer number', [
                            'customer_index' => $index,
                            'phone_number' => $customerData['phone_number'],
                            'name' => $customerData['name'],
                            'mailing_list_id' => $id,
                        ]);

                        // Check if customer with same phone_number and mailing_list_id already exists
                        $existingCustomerByPhone = $this->customerNumberRepository
                            ->where('phone_number', $customerData['phone_number'])
                            ->where('mailing_list_id', $id)
                            ->first();

                        if ($existingCustomerByPhone) {
                            // Update existing customer instead of creating duplicate
                            Log::info('Customer with same phone number exists, updating instead', [
                                'existing_customer_id' => $existingCustomerByPhone->id,
                                'phone_number' => $customerData['phone_number'],
                                'mailing_list_id' => $id,
                            ]);

                            // Prepare update data
                            $updateData = [
                                'phone_number' => $customerData['phone_number'],
                                'name' => $customerData['name'],
                                'mailing_list_id' => $id,
                            ];

                            // Add email and contact_id if provided
                            if (isset($customerData['email'])) {
                                $updateData['email'] = $customerData['email'];
                            }
                            if (isset($customerData['contact_id'])) {
                                $updateData['contact_id'] = $customerData['contact_id'];
                            }
                            // Add telegram_id if provided
                            if (isset($customerData['telegram_id'])) {
                                $updateData['telegram_id'] = $customerData['telegram_id'];
                            }

                            // Only include these fields if they are explicitly set and not empty
                            if (isset($customerData['delivered'])) {
                                $updateData['delivered'] = (bool)$customerData['delivered'];
                            }
                            if (isset($customerData['viewed'])) {
                                $updateData['viewed'] = (bool)$customerData['viewed'];
                            }
                            if (isset($customerData['incoming_message'])) {
                                $updateData['incoming_message'] = (bool)$customerData['incoming_message'];
                            }
                            // Only update whatsapp_instance_id if it's explicitly provided and not empty
                            if (isset($customerData['whatsapp_instance_id']) && !empty($customerData['whatsapp_instance_id'])) {
                                $updateData['whatsapp_instance_id'] = $customerData['whatsapp_instance_id'];
                            }

                            $this->customerNumberRepository->update($updateData, $existingCustomerByPhone->id);
                            $processedCustomerIds[] = $existingCustomerByPhone->id;

                            Log::info('Existing customer number updated successfully', [
                                'customer_id' => $existingCustomerByPhone->id,
                                'phone_number' => $customerData['phone_number'],
                                'name' => $customerData['name'],
                                'mailing_list_id' => $id,
                            ]);
                        } else {
                            // Prepare create data
                            $createData = [
                                'phone_number' => $customerData['phone_number'],
                                'name' => $customerData['name'],
                                'email' => $customerData['email'] ?? null,
                                'telegram_id' => $customerData['telegram_id'] ?? null,
                                'mailing_list_id' => $id,
                                'contact_id' => $customerData['contact_id'] ?? null,
                                'delivered' => false,
                                'viewed' => false,
                                'incoming_message' => false,
                            ];

                            // Add whatsapp_instance_id if provided
                            if (isset($customerData['whatsapp_instance_id']) && !empty($customerData['whatsapp_instance_id'])) {
                                $createData['whatsapp_instance_id'] = $customerData['whatsapp_instance_id'];
                            }

                            $customer = $this->customerNumberRepository->create($createData);
                            $processedCustomerIds[] = $customer->id;

                            Log::info('Customer number created successfully', [
                                'customer_id' => $customer->id,
                                'phone_number' => $customer->phone_number,
                                'name' => $customer->name,
                                'mailing_list_id' => $customer->mailing_list_id,
                            ]);
                        }
                    }
                }

                // Delete customers that were not in the request (removed by user)
                $customersToDelete = $existingCustomers->whereNotIn('id', $processedCustomerIds);

                if ($customersToDelete->count() > 0) {
                    Log::info('Deleting removed customer numbers', [
                        'mailing_list_id' => $id,
                        'customers_to_delete' => $customersToDelete->pluck('id')->toArray(),
                    ]);

                    foreach ($customersToDelete as $customerToDelete) {
                        $this->customerNumberRepository->delete($customerToDelete->id);

                        Log::info('Customer number deleted', [
                            'customer_id' => $customerToDelete->id,
                            'phone_number' => $customerToDelete->phone_number,
                            'name' => $customerToDelete->name,
                            'mailing_list_id' => $id,
                        ]);
                    }
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
            $whatsappInstancesCount = $mailingList->whatsappInstances()->count();

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

            // Detach WhatsApp instances (pivot table will be cleaned automatically by cascade)
            $mailingList->whatsappInstances()->detach();
            Log::info('WhatsApp instances detached from mailing list', [
                'mailing_list_id' => $id,
                'detached_instances_count' => $whatsappInstancesCount,
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

        // Check account balance
        if ($mailingList->company_id) {
            $account = $this->accountRepository->getOrCreateForCompany($mailingList->company_id);
            if ($account->balance <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => trans('newsletters::app.admin.account.insufficient-balance'),
                ], 402);
            }
        }

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
        try {
            $mailingList = $this->mailingListRepository->findOrFail($id);
            $result = $this->mailingListStarterService->start($mailingList);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message']
            ], $result['status_code']);
        } catch (\Exception $e) {
            Log::error('Failed to start mailing list', [
                'mailing_list_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.mailing-lists.mailing-start-failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    public function pauseMailing(int $id)
    {
        try {
            $mailingList = $this->mailingListRepository->findOrFail($id);

            // Pause mailing list: set active to false and status to paused
            $this->mailingListRepository->update([
                'active' => false,
                'status' => 'paused'
            ], $id);

            Log::info('Pausing mailing list', [
                'mailing_list_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => trans('newsletters::app.admin.mailing-lists.mailing-paused')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to pause mailing list', [
                'mailing_list_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.mailing-lists.mailing-pause-failed')
            ], 500);
        }
    }

    /**
     * Calculate delay in seconds based on mailing list parameters.
     */
    protected function calculateMailingDelay($mailingList): int
    {
        //TODO - вынести в отдельный сервис
        // Явно используем часовой пояс из конфигурации
        $timezone = config('app.timezone', 'UTC');
        $now = now()->setTimezone($timezone);
        $delay = 0;

        // Check start_at (datetime)
        if ($mailingList->start_at) {
            $startAt = \Carbon\Carbon::parse($mailingList->start_at);
            // Убеждаемся, что start_at в правильном часовом поясе
            if ($startAt->timezone->getName() !== $timezone) {
                $startAt = $startAt->setTimezone($timezone);
            }
            if ($startAt->isFuture()) {
                $secondsUntilStart = $now->diffInSeconds($startAt, false);
                if ($secondsUntilStart > 0) {
                    $delay = max($delay, $secondsUntilStart);
                }
            }

            Log::info("calculateMailingDelay 1111", [
                '$delay' => $delay,
            ]);
        }

        // Check mailing_hours_from (time)
        if ($mailingList->mailing_hours_from) {
            $fromTime = $mailingList->mailing_hours_from;
            $toTime = $mailingList->mailing_hours_to;

            if (!$toTime) {
                // Если toTime не указан, считаем что диапазон не переходит через полночь
                $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
                $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
                if ($secondsUntilFrom > 0) {
                    $delay = max($delay, $secondsUntilFrom);
                }
                Log::info("calculateMailingDelay 222 111", [
                    '$delay' => $delay,
                    '$hoursFromToday' => $hoursFromToday,
                    '$secondsUntilFrom' => $secondsUntilFrom,
                ]);
            } else {
                // Преобразуем время в минуты для корректного сравнения
                $fromMinutes = $this->timeToMinutes($fromTime);
                $toMinutes = $this->timeToMinutes($toTime);
                // Используем час и минуту из объекта $now, который уже в правильном часовом поясе
                $currentMinutes = $now->hour * 60 + $now->minute;

                // Проверяем, переходит ли диапазон через полночь
                $spansMidnight = $toMinutes < $fromMinutes;

                if ($spansMidnight) {
                    // Диапазон переходит через полночь (например, 10:00 - 03:00)
                    // Мы в диапазоне, если: currentMinutes >= fromMinutes ИЛИ currentMinutes <= toMinutes
                    if ($currentMinutes >= $fromMinutes || $currentMinutes <= $toMinutes) {
                        // Текущее время в диапазоне - можно начинать сразу (delay = 0)
                        $delay = 0;
                        Log::info("calculateMailingDelay 222 222 111 000", [
                            '$delay' => $delay,

                        ]);
                    } else {
                        // Текущее время между окончанием и началом (например, 04:00 - 09:59)
                        // Устанавливаем задержку до начала времени рассылки
                        $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
                        $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
                        if ($secondsUntilFrom > 0) {
                            $delay = max($delay, $secondsUntilFrom);
                        }
                        Log::info("calculateMailingDelay 222 222 111", [
                            '$delay' => $delay,
                            '$hoursFromToday' => $hoursFromToday,
                            '$secondsUntilFrom' => $secondsUntilFrom,
                            '$currentMinutes' => $currentMinutes,
                        ]);

                    }

                } else {
                    // Обычный диапазон в пределах одного дня (например, 10:00 - 18:00)
                    if ($currentMinutes < $fromMinutes) {
                        // Вычисляем секунды до начала времени рассылки
                        $hoursFromToday = $now->copy()->setTimeFromTimeString($fromTime);
                        $secondsUntilFrom = $now->diffInSeconds($hoursFromToday, false);
                        if ($secondsUntilFrom > 0) {
                            $delay = max($delay, $secondsUntilFrom);
                        }

                        Log::info("calculateMailingDelay 222 222 2222 111", [
                            '$delay' => $delay,
                            '$hoursFromToday' => $hoursFromToday,
                            '$secondsUntilFrom' => $secondsUntilFrom,
                        ]);
                    } elseif ($currentMinutes > $toMinutes) {
                        // Если текущее время больше времени окончания - переносим на завтра
                        $hoursFromTomorrow = $now->copy()->addDay()->setTimeFromTimeString($fromTime);
                        $secondsUntilFrom = $now->diffInSeconds($hoursFromTomorrow, false);
                        if ($secondsUntilFrom > 0) {
                            $delay = max($delay, $secondsUntilFrom);
                        }

                        Log::info("calculateMailingDelay 222 222 222", [
                            '$delay' => $delay,
                            '$hoursFromTomorrow' => $hoursFromTomorrow,
                            '$secondsUntilFrom' => $secondsUntilFrom,
                        ]);
                    }
                    // Если текущее время в диапазоне mailing_hours_from - mailing_hours_to, delay = 0 (можно начинать)
                }
            }
        }

        return (int) $delay;
    }

    /**
     * Преобразует время в формате "H:i" в минуты от начала дня
     */
    protected function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return (int) $hours * 60 + (int) $minutes;
    }

    public function testMailing(int $id)
    {

        $mailingList = $this->mailingListRepository->findOrFail($id);
        $service = new \Webkul\Newsletters\Services\WhatsAppMailingService();

        foreach($mailingList->customerNumbers as $customerNumber){
            if(\Webkul\Newsletters\Models\StopList::where('phone_number', $customerNumber->phone_number)->exists()){
                Log::error('Number in stopList', [
                    'phone' => $customerNumber->phone_number
                ]);
                continue;
            }

            $text = $service->makeRandomMessage($mailingList->message_text);
            $instance = $service->makeRandomInstance($mailingList->whatsappInstances);
            $message_id = $service->sendMessage($instance, $customerNumber, $text);

            if($message_id){
                //привязываем инстанс к сообщению
                //присваиваем сообщению номер из greenapi
                $customerNumber->update([
                    'greenapi_chat_id' => $message_id,
                    'whatsapp_instance_id' => $instance->id
                ]);

                //заносим в стоп лист
                \Webkul\Newsletters\Models\StopList::create(['phone_number' => $customerNumber->phone_number]);
            }
            else{
                Log::error('Failed to send message, no message_id return');
            }

        }

    }

}

