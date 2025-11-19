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
            $query = $this->contactRepository->newQuery();

            // Filter by contact group if provided
            if ($request->has('contact_group_id') && $request->contact_group_id) {
                $query->where('contact_group_id', (int) $request->contact_group_id);
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

            // Convert models to arrays for proper JSON serialization
            $contactsArray = collect($contacts->items())->map(function ($contact) {
                return $contact->toArray();
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


