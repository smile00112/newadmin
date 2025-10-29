<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\ContactGroupRepository;
use Webkul\Newsletters\Repositories\ContactRepository;

class ContactGroupController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ContactGroupRepository $contactGroupRepository,
        protected ContactRepository $contactRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = $this->contactGroupRepository
            ->withCount('contacts')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('newsletters::admin.contact-groups.index', compact('groups'));
    }

    /**
     * Show contacts for a specific group.
     */
    public function contacts(int $groupId)
    {
        $group = $this->contactGroupRepository->findOrFail($groupId);
        
        $contacts = $this->contactRepository
            ->where('contact_group_id', $groupId)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('newsletters::admin.contact-groups.contacts', compact('group', 'contacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('newsletters::admin.contact-groups.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $this->contactGroupRepository->create($validated);

        session()->flash('success', trans('newsletters::app.admin.contact-groups.create-success'));

        return redirect()->route('admin.newsletters.contact-groups.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $group = $this->contactGroupRepository->findOrFail($id);

        return view('newsletters::admin.contact-groups.edit', compact('group'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $this->contactGroupRepository->update($validated, $id);

        session()->flash('success', trans('newsletters::app.admin.contact-groups.update-success'));

        return redirect()->route('admin.newsletters.contact-groups.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        // Delete all contacts in the group first
        $this->contactRepository->where('contact_group_id', $id)->delete();
        
        // Then delete the group
        $this->contactGroupRepository->delete($id);

        session()->flash('success', trans('newsletters::app.admin.contact-groups.delete-success'));

        return redirect()->route('admin.newsletters.contact-groups.index');
    }

    /**
     * Preview CSV headers for mapping.
     */
    public function previewCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'delimiter' => 'nullable|string|max:1',
            'has_header' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $delimiter = $request->input('delimiter', ',');
        $hasHeader = $request->boolean('has_header');

        $csvData = array_map(function($line) use ($delimiter) {
            return str_getcsv($line, $delimiter);
        }, file($file->getPathname(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

        $headers = $hasHeader && count($csvData) > 0 ? $csvData[0] : array_map(fn($i) => "Column " . ($i + 1), range(0, count($csvData[0]) - 1));

        return response()->json([
            'headers' => $headers,
            'row_count' => count($csvData) - ($hasHeader ? 1 : 0),
        ]);
    }

    /**
     * Import contacts from CSV.
     */
    public function importContacts(Request $request, int $groupId)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'delimiter' => 'nullable|string|max:1',
            'has_header' => 'nullable|boolean',
            'mapping' => 'required|string',
        ]);

        try {
            $file = $request->file('file');
            $delimiter = $request->input('delimiter', ',');
            $hasHeader = $request->boolean('has_header');
            
            // Parse mapping JSON string
            $mappingJson = $request->input('mapping');
            $mapping = json_decode($mappingJson, true);
            
            if (!is_array($mapping)) {
                return response()->json([
                    'success' => false,
                    'message' => trans('newsletters::app.admin.contacts.import-failed') . ': Invalid mapping format'
                ], 400);
            }

            $csvData = array_map(function($line) use ($delimiter) {
                return str_getcsv($line, $delimiter);
            }, file($file->getPathname(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

            if ($hasHeader && count($csvData) > 0) {
                array_shift($csvData); // Remove header row
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($csvData as $index => $row) {
                try {
                    $contactData = [
                        'contact_group_id' => $groupId,
                    ];

                    // Map CSV columns to model fields
                    foreach ($mapping as $field => $columnIndex) {
                        if ($columnIndex !== null && isset($row[$columnIndex])) {
                            $value = trim($row[$columnIndex]);
                            
                            // Sanitize phone number
                            if ($field === 'phone') {
                                $value = preg_replace('/[^0-9]/', '', $value);
                            }

                            $contactData[$field] = $value ?: null;
                        }
                    }

                    // Skip if required fields are empty
                    if (empty($contactData['full_name']) || empty($contactData['phone'])) {
                        $skipped++;
                        continue;
                    }

                    // Check for duplicates
                    $exists = $this->contactRepository->findWhere([
                        'phone' => $contactData['phone'],
                        'contact_group_id' => $groupId,
                    ])->first();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    $this->contactRepository->create($contactData);
                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
                'message' => trans('newsletters::app.admin.contacts.import-success', ['count' => $imported])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.contacts.import-failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }
}

