<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\ContactRepository;

class ContactController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ContactRepository $contactRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->contactRepository->newQuery();

        // Filter by contact group if provided
        if ($request->has('contact_group_id') && $request->contact_group_id) {
            $query->where('contact_group_id', $request->contact_group_id);
        }

        // Search by phone number if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('phone', 'like', '%' . $searchTerm . '%');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'id');
        $sortDir = $request->get('sort_dir', 'desc');

        // Validate sort_by field
        $allowedSortFields = ['id', 'full_name', 'phone', 'gender', 'total_check'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'id';
        }

        // Validate sort direction
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $query->orderBy($sortBy, $sortDir);

        $contacts = $query->paginate(20)->appends($request->query());

        return view('newsletters::admin.contacts.index', compact('contacts', 'sortBy', 'sortDir'));
    }

    /**
     * Get contacts data for component (AJAX request).
     */
    public function getContacts(Request $request)
    {
        try {
            $model = $this->contactRepository->makeModel();
            $query = $model->newQuery();

            // Apply company filter
            $admin = auth()->guard('admin')->user();
            if ($admin && $admin->company_id) {
                $query->where('company_id', $admin->company_id);
            }

            // Filter by contact group if provided
            if ($request->has('contact_group_id') && $request->contact_group_id) {
                $query->where('contact_group_id', (int) $request->contact_group_id);
            }

            // Search by phone number or name if provided
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('phone', 'like', '%' . $searchTerm . '%')
                      ->orWhere('full_name', 'like', '%' . $searchTerm . '%');
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'id');
            $sortDir = $request->get('sort_dir', 'desc');

            // Validate sort_by field
            $allowedSortFields = ['id', 'full_name', 'phone', 'gender', 'total_check'];
            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'id';
            }

            // Validate sort direction
            if (!in_array($sortDir, ['asc', 'desc'])) {
                $sortDir = 'desc';
            }

            $query->orderBy($sortBy, $sortDir);

            $contacts = $query->paginate(20)->appends($request->query());

            // Convert models to arrays for proper JSON serialization
            $contactsArray = collect($contacts->items())->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'full_name' => $contact->full_name,
                    'phone' => $contact->phone,
                    'email' => $contact->email,
                    'telegram_user_id' => $contact->telegram_user_id,
                    'gender' => $contact->gender,
                    'last_order_date' => $contact->last_order_date ? $contact->last_order_date->format('Y-m-d') : null,
                    'registration_date' => $contact->registration_date ? $contact->registration_date->format('Y-m-d') : null,
                    'birth_date' => $contact->birth_date ? $contact->birth_date->format('Y-m-d') : null,
                    'orders_count' => $contact->orders_count,
                    'average_check' => $contact->average_check,
                    'total_check' => $contact->total_check,
                    'average_order_rating' => $contact->average_order_rating,
                    'favorite_category' => $contact->favorite_category,
                    'favorite_dish' => $contact->favorite_dish,
                    'store' => $contact->store,
                    'contact_group_id' => $contact->contact_group_id,
                    'company_id' => $contact->company_id,
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'contacts' => $contactsArray,
                'pagination' => [
                    'current_page' => $contacts->currentPage(),
                    'last_page' => $contacts->lastPage(),
                    'per_page' => $contacts->perPage(),
                    'total' => $contacts->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getContacts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching contacts.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Store a newly created contact.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string',
                'email' => 'nullable|email|max:255',
                'telegram_user_id' => 'nullable|string|max:255',
                'gender' => 'nullable|in:male,female,other',
                'last_order_date' => 'nullable|date',
                'registration_date' => 'nullable|date',
                'birth_date' => 'nullable|date',
                'orders_count' => 'nullable|integer|min:0',
                'average_check' => 'nullable|numeric|min:0',
                'total_check' => 'nullable|numeric|min:0',
                'average_order_rating' => 'nullable|numeric|min:0|max:5',
                'favorite_category' => 'nullable|string|max:255',
                'favorite_dish' => 'nullable|string|max:255',
                'store' => 'nullable|string|max:255',
                'contact_group_id' => 'required|exists:newsletters_contact_groups,id',
            ]);

            // Sanitize phone number - remove all non-numeric characters
            if (isset($validated['phone'])) {
                $validated['phone'] = preg_replace('/[^0-9]/', '', $validated['phone']);
            }

            // Check for duplicate phone in the same contact group
            $existingContact = $this->contactRepository->findWhere([
                'phone' => $validated['phone'],
                'contact_group_id' => $validated['contact_group_id'],
            ])->first();

            if ($existingContact) {
                return response()->json([
                    'success' => false,
                    'message' => __('newsletters::app.admin.contacts.duplicate-phone'),
                ], 400);
            }

            // Create the contact
            $contact = $this->contactRepository->create($validated);

            return response()->json([
                'success' => true,
                'message' => __('newsletters::app.admin.contacts.create-success'),
                'contact' => [
                    'id' => $contact->id,
                    'full_name' => $contact->full_name,
                    'phone' => $contact->phone,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('newsletters::app.common.messages.validation-error'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in store contact: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('newsletters::app.common.messages.error'),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Clear all contacts from the current group.
     */
    public function clearGroupContacts(Request $request)
    {
        try {
            $contactGroupId = $request->input('contact_group_id');

            if (!$contactGroupId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact group ID is required.',
                ], 400);
            }

            $deletedCount = $this->contactRepository
                ->where('contact_group_id', (int) $contactGroupId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => __('newsletters::app.admin.contacts.clear-success', ['count' => $deletedCount]),
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in clearGroupContacts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while clearing contacts.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}


