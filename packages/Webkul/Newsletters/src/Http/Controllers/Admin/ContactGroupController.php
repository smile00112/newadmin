<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\ContactGroupRepository;
use Webkul\Newsletters\Repositories\ContactRepository;
use Webkul\Newsletters\Repositories\ContactImportMappingRepository;
use Webkul\Newsletters\Models\NewslettersContact;
use Webkul\Newsletters\Events\ContactCacheInvalidated;

class ContactGroupController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ContactGroupRepository $contactGroupRepository,
        protected ContactRepository $contactRepository,
        protected ContactImportMappingRepository $contactImportMappingRepository
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
     * Download CSV import template with sample data.
     */
    public function downloadImportTemplate()
    {
        $headers = [
            'full_name',
            'phone',
            'email',
            'telegram_user_id',
            'gender',
            'last_order_date',
            'registration_date',
            'birth_date',
            'orders_count',
            'average_check',
            'total_check',
            'average_order_rating',
            'favorite_category',
            'favorite_dish',
            'store',
        ];

        // Sample data rows
        $sampleData = [
            [
                'Иван Иванов',
                '79001234567',
                'ivan@example.com',
                '123456789',
                'male',
                '2024-01-15',
                '2023-06-01',
                '1990-05-20',
                '5',
                '1500.00',
                '7500.00',
                '4.5',
                'Пицца',
                'Маргарита',
                'Магазин 1',
            ],
            [
                'Мария Петрова',
                '79009876543',
                'maria@example.com',
                '987654321',
                'female',
                '2024-01-20',
                '2023-08-15',
                '1992-11-10',
                '3',
                '2000.00',
                '6000.00',
                '4.8',
                'Суши',
                'Филадельфия',
                'Магазин 2',
            ],
            [
                'Петр Сидоров',
                '79005555555',
                'petr@example.com',
                '',
                'male',
                '2024-01-10',
                '2023-12-01',
                '1988-03-25',
                '2',
                '1200.00',
                '2400.00',
                '4.2',
                'Бургеры',
                'Чизбургер',
                'Магазин 1',
            ],
        ];

        // Create CSV content
        $csvContent = '';
        
        // Add BOM for UTF-8 encoding (for Excel compatibility)
        $csvContent .= "\xEF\xBB\xBF";
        
        // Add headers
        $csvContent .= implode(',', array_map(function($header) {
            return '"' . str_replace('"', '""', $header) . '"';
        }, $headers)) . "\n";
        
        // Add sample data rows
        foreach ($sampleData as $row) {
            $csvContent .= implode(',', array_map(function($cell) {
                return '"' . str_replace('"', '""', $cell) . '"';
            }, $row)) . "\n";
        }

        $filename = 'contact_import_template_' . date('Y-m-d') . '.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'has_external_integration' => 'nullable|boolean',
            'request_url' => 'nullable|string|url|max:500|required_if:has_external_integration,1',
            'request_token' => 'nullable|string|max:255|required_if:has_external_integration,1',
            'auto_request_frequency' => 'nullable|integer|in:86400,172800,259200,604800',
            'csv_file' => 'nullable|file|mimes:csv,txt',
        ]);

        // Convert checkbox value to boolean
        $validated['has_external_integration'] = $request->has('has_external_integration') ? (bool) $request->input('has_external_integration') : false;

        // Clear integration fields if integration is disabled
        if (!$validated['has_external_integration']) {
            $validated['request_url'] = null;
            $validated['request_token'] = null;
            $validated['auto_request_frequency'] = null;
        }

        // Create the contact group
        $group = $this->contactGroupRepository->create($validated);

        $importedCount = 0;
        $skippedCount = 0;

        // Import CSV if file is provided
        if ($request->hasFile('csv_file')) {
            try {
                $file = $request->file('csv_file');
                $delimiter = ',';
                $hasHeader = true; // Assume CSV has headers

                // Read CSV file line by line
                $csvData = [];
                $handle = fopen($file->getPathname(), 'r');
                
                if ($handle !== false) {
                    while (($line = fgets($handle)) !== false) {
                        $line = trim($line);
                        
                        // Skip completely empty lines
                        if (empty($line)) {
                            continue;
                        }
                        
                        $row = str_getcsv($line, $delimiter);
                        
                        // Skip rows that are completely empty after parsing
                        if (empty(array_filter($row, function($cell) { return trim($cell) !== ''; }))) {
                            continue;
                        }
                        
                        $csvData[] = $row;
                    }
                    fclose($handle);
                }

                if (count($csvData) > 0) {
                    // Get headers from first row
                    $headers = array_map('trim', $csvData[0]);
                    
                    // Remove header row from data
                    array_shift($csvData);

                    // Map headers to field names (normalize header names)
                    $fieldMap = [];
                    $fillableFields = (new NewslettersContact())->getFillable();
                    
                    foreach ($headers as $index => $header) {
                        $normalizedHeader = strtolower(trim($header));
                        // Try to match header with fillable fields
                        foreach ($fillableFields as $field) {
                            if ($normalizedHeader === strtolower($field)) {
                                $fieldMap[$field] = $index;
                                break;
                            }
                        }
                    }

                    DB::beginTransaction();

                    foreach ($csvData as $index => $row) {
                        try {
                            $contactData = [
                                'contact_group_id' => $group->id,
                            ];

                            // Map CSV columns to model fields
                            foreach ($fieldMap as $field => $columnIndex) {
                                if (isset($row[$columnIndex])) {
                                    $value = trim($row[$columnIndex]);
                                    
                                    // Sanitize phone number
                                    if ($field === 'phone') {
                                        $value = preg_replace('/[^0-9]/', '', $value);
                                    }

                                    // Handle date fields
                                    if (in_array($field, ['last_order_date', 'registration_date', 'birth_date']) && !empty($value)) {
                                        $timestamp = strtotime($value);
                                        $contactData[$field] = $timestamp ? date('Y-m-d', $timestamp) : null;
                                    } else {
                                        $contactData[$field] = $value ?: null;
                                    }
                                }
                            }

                            // Check and skip if required fields are empty
                            if (empty($contactData['full_name']) || empty($contactData['phone'])) {
                                $skippedCount++;
                                continue;
                            }

                            // Check for duplicates
                            $exists = $this->contactRepository->findWhere([
                                'phone' => $contactData['phone'],
                                'contact_group_id' => $group->id,
                            ])->first();

                            if ($exists) {
                                $skippedCount++;
                                continue;
                            }

                            $this->contactRepository->create($contactData);
                            $importedCount++;

                        } catch (\Exception $e) {
                            $skippedCount++;
                            \Log::error('CSV Import error during group creation', [
                                'row' => $index + 1,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    DB::commit();

                    // Invalidate cache for contact filters after import
                    if ($importedCount > 0) {
                        Event::dispatch(new ContactCacheInvalidated($group->id));
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('CSV Import failed during group creation', [
                    'group_id' => $group->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Prepare success message
        $message = trans('newsletters::app.admin.contact-groups.create-success');
        if ($importedCount > 0) {
            $message .= ' ' . trans('newsletters::app.admin.contacts.import-success', ['count' => $importedCount]);
            if ($skippedCount > 0) {
                $message .= ' ' . trans('newsletters::app.admin.contacts.import-skipped', ['count' => $skippedCount]);
            }
        }

        session()->flash('success', $message);

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
            'has_external_integration' => 'nullable|boolean',
            'request_url' => 'nullable|string|url|max:500|required_if:has_external_integration,1',
            'request_token' => 'nullable|string|max:255|required_if:has_external_integration,1',
            'auto_request_frequency' => 'nullable|integer|in:86400,172800,259200,604800',
        ]);

        // Convert checkbox value to boolean
        $validated['has_external_integration'] = $request->has('has_external_integration') ? (bool) $request->input('has_external_integration') : false;

        // Clear integration fields if integration is disabled
        if (!$validated['has_external_integration']) {
            $validated['request_url'] = null;
            $validated['request_token'] = null;
            $validated['auto_request_frequency'] = null;
        }

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

        // Read CSV file line by line (same approach as importContacts)
        $csvData = [];
        $handle = fopen($file->getPathname(), 'r');
        
        if ($handle === false) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot read file'
            ], 500);
        }

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            
            // Skip completely empty lines
            if (empty($line)) {
                continue;
            }
            
            $row = str_getcsv($line, $delimiter);
            
            // Skip rows that are completely empty after parsing
            if (empty(array_filter($row, function($cell) { return trim($cell) !== ''; }))) {
                continue;
            }
            
            $csvData[] = $row;
        }
        fclose($handle);

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

            // Read CSV file line by line to handle large files and encoding issues
            $csvData = [];
            $handle = fopen($file->getPathname(), 'r');
            
            if ($handle === false) {
                return response()->json([
                    'success' => false,
                    'message' => trans('newsletters::app.admin.contacts.import-failed') . ': Cannot read file'
                ], 500);
            }

            $lineNumber = 0;
            while (($line = fgets($handle)) !== false) {
                $lineNumber++;
                $line = trim($line);
                
                // Skip completely empty lines
                if (empty($line)) {
                    continue;
                }
                
                $row = str_getcsv($line, $delimiter);
                
                // Skip rows that are completely empty after parsing
                if (empty(array_filter($row, function($cell) { return trim($cell) !== ''; }))) {
                    continue;
                }
                
                $csvData[] = $row;
            }
            fclose($handle);

            \Log::info('CSV Import started', [
                'group_id' => $groupId,
                'total_lines_read' => $lineNumber,
                'csv_rows_parsed' => count($csvData),
                'has_header' => $hasHeader,
            ]);

            if ($hasHeader && count($csvData) > 0) {
                array_shift($csvData); // Remove header row
            }

            $imported = 0;
            $skipped = 0;
            $skippedReasons = [
                'empty_name' => 0,
                'empty_phone' => 0,
                'duplicate' => 0,
                'error' => 0,
            ];
            $errors = [];
            $totalRows = count($csvData);

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

                    // Check and skip if required fields are empty
                    $skipReason = null;
                    if (empty($contactData['full_name'])) {
                        $skipReason = 'empty_name';
                        $skippedReasons['empty_name']++;
                    } elseif (empty($contactData['phone'])) {
                        $skipReason = 'empty_phone';
                        $skippedReasons['empty_phone']++;
                    }

                    if ($skipReason) {
                        $skipped++;
                        \Log::debug('Contact skipped', [
                            'row' => $index + 1,
                            'reason' => $skipReason,
                            'data' => $contactData,
                        ]);
                        continue;
                    }

                    // Check for duplicates
                    $exists = $this->contactRepository->findWhere([
                        'phone' => $contactData['phone'],
                        'contact_group_id' => $groupId,
                    ])->first();

                    if ($exists) {
                        $skipped++;
                        $skippedReasons['duplicate']++;
                        \Log::debug('Contact skipped - duplicate', [
                            'row' => $index + 1,
                            'phone' => $contactData['phone'],
                        ]);
                        continue;
                    }

                    $this->contactRepository->create($contactData);
                    $imported++;

                } catch (\Exception $e) {
                    $skipped++;
                    $skippedReasons['error']++;
                    $errorMsg = "Row " . ($index + 1) . ": " . $e->getMessage();
                    $errors[] = $errorMsg;
                    \Log::error('CSV Import error', [
                        'row' => $index + 1,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            DB::commit();

            // Invalidate cache for contact filters after import
            if ($imported > 0) {
                Event::dispatch(new ContactCacheInvalidated($groupId));
            }

            \Log::info('CSV Import completed', [
                'group_id' => $groupId,
                'total_rows' => $totalRows,
                'imported' => $imported,
                'skipped' => $skipped,
                'skipped_reasons' => $skippedReasons,
                'errors_count' => count($errors),
            ]);

            $message = trans('newsletters::app.admin.contacts.import-success', ['count' => $imported]);
            if ($skipped > 0) {
                $message .= ' ' . trans('newsletters::app.admin.contacts.import-skipped', ['count' => $skipped]);
                
                // Add detailed reasons
                $reasonMessages = [];
                if ($skippedReasons['empty_name'] > 0) {
                    $reasonMessages[] = trans('newsletters::app.admin.contacts.import-skipped-empty-name', ['count' => $skippedReasons['empty_name']]);
                }
                if ($skippedReasons['empty_phone'] > 0) {
                    $reasonMessages[] = trans('newsletters::app.admin.contacts.import-skipped-empty-phone', ['count' => $skippedReasons['empty_phone']]);
                }
                if ($skippedReasons['duplicate'] > 0) {
                    $reasonMessages[] = trans('newsletters::app.admin.contacts.import-skipped-duplicate', ['count' => $skippedReasons['duplicate']]);
                }
                if ($skippedReasons['error'] > 0) {
                    $reasonMessages[] = trans('newsletters::app.admin.contacts.import-skipped-error', ['count' => $skippedReasons['error']]);
                }
                
                if (!empty($reasonMessages)) {
                    $message .= ' (' . implode(', ', $reasonMessages) . ')';
                }
            }

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'total_rows' => $totalRows,
                'skipped_reasons' => $skippedReasons,
                'errors' => $errors,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('CSV Import failed', [
                'group_id' => $groupId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.contacts.import-failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Persist CSV mapping configuration for a contact group.
     */
    public function saveImportMapping(Request $request, int $groupId)
    {
        \Log::info('saveImportMapping called', [
            'group_id' => $groupId,
            'request_data' => $request->all(),
        ]);

        try {
            $group = $this->contactGroupRepository->findOrFail($groupId);

            $validated = $request->validate([
                'mapping' => 'required|array|min:1',
                'mapping.*' => 'required|integer|min:0',
                'headers' => 'required|array|min:1',
            ]);

            \Log::info('Validation passed', [
                'group_id' => $groupId,
                'mapping_count' => count($validated['mapping']),
                'headers_count' => count($validated['headers']),
            ]);

            $timestamp = now();
            $records = [];

            foreach ($validated['mapping'] as $modelField => $columnIndex) {
                $csvField = $validated['headers'][$columnIndex] ?? null;

                $records[] = [
                    'contact_group_id' => $group->id,
                    'model_field' => $modelField,
                    'csv_field' => $csvField,
                    'csv_index' => $columnIndex,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            \Log::info('Records prepared', [
                'group_id' => $groupId,
                'records_count' => count($records),
                'records' => $records,
            ]);

            DB::transaction(function () use ($group, $records) {
                // Delete existing mappings for this group using model directly
                $model = $this->contactImportMappingRepository->getModel();
                $deleted = $model->where('contact_group_id', $group->id)->delete();

                \Log::info('Deleted existing mappings', [
                    'group_id' => $group->id,
                    'deleted_count' => $deleted,
                ]);

                // Insert new mappings
                if (! empty($records)) {
                    $inserted = $model->insert($records);
                    \Log::info('Inserted new mappings', [
                        'group_id' => $group->id,
                        'records_count' => count($records),
                        'inserted' => $inserted,
                    ]);
                }
            });

            \Log::info('Mapping saved successfully', ['group_id' => $groupId]);

            return response()->json([
                'success' => true,
                'message' => 'Mapping saved successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'group_id' => $groupId,
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to save import mapping', [
                'group_id' => $groupId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save mapping: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import contacts from an external integration endpoint page by page.
     */
    public function externalImport(Request $request, int $groupId)
    {
        $request->validate([
            'page' => 'nullable|integer|min:1',
            'request_url' => 'nullable|string|url|max:500',
            'request_token' => 'nullable|string|max:255',
        ]);

        $group = $this->contactGroupRepository->findOrFail($groupId);

        if (! $group->has_external_integration) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.contact-groups.external-import.integration-disabled'),
            ], 422);
        }

        $requestUrl = $request->input('request_url', $group->request_url);
        $requestToken = $request->input('request_token', $group->request_token);
        $page = max(1, (int) $request->input('page', 1));

        if (! $requestUrl || ! $requestToken) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.contact-groups.external-import.missing-credentials'),
            ], 422);
        }

        try {
            $externalResponse = Http::acceptJson()->retry(1, 200)->get($requestUrl, [
                'token' => $requestToken,
                'page' => $page,
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.contact-groups.external-import.connection-error'),
                'details' => $exception->getMessage(),
            ], 500);
        }

        if ($externalResponse->failed()) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.contact-groups.external-import.remote-error'),
                'details' => $externalResponse->body(),
            ], $externalResponse->status());
        }

        $payload = $externalResponse->json();

        if (! is_array($payload) || ! array_key_exists('users', $payload) || ! is_array($payload['users'])) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.contact-groups.external-import.invalid-response'),
            ], 422);
        }

        $totalPages = max(1, (int) ($payload['total_pages'] ?? 1));
        $currentPage = (int) ($payload['page'] ?? $page);

        try {
            [$imported, $skipped] = $this->persistExternalUsers($payload['users'], $groupId);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.contact-groups.external-import.store-error'),
                'details' => $exception->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'page' => $currentPage,
            'total_pages' => $totalPages,
            'per_page' => $payload['per_page'] ?? null,
            'total' => $payload['total'] ?? null,
            'users_processed' => count($payload['users']),
            'imported' => $imported,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Persist users fetched from an external integration.
     */
    protected function persistExternalUsers(array $users, int $groupId): array
    {
        $contactModel = new NewslettersContact();
        $fillable = array_diff($contactModel->getFillable(), ['contact_group_id']);

        $imported = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($users as $user) {
                if (! is_array($user)) {
                    $skipped++;
                    continue;
                }

                $contactData = [
                    'contact_group_id' => $groupId,
                ];

                foreach ($fillable as $field) {
                    if (array_key_exists($field, $user)) {
                        $contactData[$field] = $this->normalizeContactField($field, $user[$field]);
                    }
                }

                if (empty($contactData['full_name']) || empty($contactData['phone'])) {
                    $skipped++;
                    continue;
                }

                $contactData['phone'] = $this->sanitizePhone((string) $contactData['phone']);

                if (! $contactData['phone']) {
                    $skipped++;
                    continue;
                }

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
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

        return [$imported, $skipped];
    }

    /**
     * Normalize incoming field values before persisting.
     */
    protected function normalizeContactField(string $field, $value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '' || $value === null) {
            return null;
        }

        if (in_array($field, ['last_order_date', 'registration_date', 'birth_date'], true)) {
            $timestamp = strtotime((string) $value);

            return $timestamp ? date('Y-m-d', $timestamp) : null;
        }

        return $value;
    }

    /**
     * Sanitize phone numbers by leaving digits only.
     */
    protected function sanitizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        return $digits ?: null;
    }
}

