<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\ContactFilterRepository;
use Webkul\Newsletters\Repositories\ContactFilterConditionRepository;
use Webkul\Newsletters\Repositories\ContactGroupRepository;
use Webkul\Newsletters\Repositories\ContactRepository;
use Webkul\Newsletters\Models\NewslettersContact;

class ContactFilterController extends Controller
{
    /**
     * Allowed fields for filtering.
     */
    protected array $allowedFields = [
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

    /**
     * Numeric fields.
     */
    protected array $numericFields = [
        'orders_count',
        'average_check',
        'total_check',
        'average_order_rating',
    ];

    /**
     * Date fields.
     */
    protected array $dateFields = [
        'last_order_date',
        'registration_date',
        'birth_date',
    ];

    /**
     * Text fields.
     */
    protected array $textFields = [
        'gender',
        'favorite_category',
        'favorite_dish',
        'store',
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ContactFilterRepository $contactFilterRepository,
        protected ContactFilterConditionRepository $contactFilterConditionRepository,
        protected ContactGroupRepository $contactGroupRepository,
        protected ContactRepository $contactRepository
    ) {}

    /**
     * Display a listing of filters for a contact group.
     */
    public function index(int $groupId)
    {
        $group = $this->contactGroupRepository->findOrFail($groupId);
        $filters = $this->contactFilterRepository->getByGroupId($groupId);

        // Load conditions for each filter
        $filters->load('conditions');

        return response()->json([
            'success' => true,
            'filters' => $filters,
        ]);
    }

    /**
     * Store a newly created filter.
     */
    public function store(Request $request, int $groupId)
    {
        $group = $this->contactGroupRepository->findOrFail($groupId);

        $validated = $this->validateFilter($request);

        DB::beginTransaction();

        try {
            // Create filter
            $filterData = [
                'contact_group_id' => $groupId,
                'name' => $validated['name'],
            ];

            $filter = $this->contactFilterRepository->create($filterData);

            // Create conditions
            $conditions = $validated['conditions'];
            foreach ($conditions as $index => $condition) {
                $conditionData = $this->prepareConditionData($condition, $filter->id, $index);
                $this->contactFilterConditionRepository->create($conditionData);
            }

            DB::commit();

            $filter->load('conditions');

            return response()->json([
                'success' => true,
                'message' => trans('newsletters::app.admin.contact-filters.create-success'),
                'filter' => $filter,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.contact-filters.create-error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified filter.
     */
    public function update(Request $request, int $groupId, int $id)
    {
        $filter = $this->contactFilterRepository->findOrFail($id);

        $validated = $this->validateFilter($request);

        DB::beginTransaction();

        try {
            // Update filter name
            $this->contactFilterRepository->update([
                'name' => $validated['name'],
            ], $id);

            // Delete existing conditions
            $filter->conditions()->delete();

            // Create new conditions
            $conditions = $validated['conditions'];
            foreach ($conditions as $index => $condition) {
                $conditionData = $this->prepareConditionData($condition, $filter->id, $index);
                $this->contactFilterConditionRepository->create($conditionData);
            }

            DB::commit();

            $filter->load('conditions');

            return response()->json([
                'success' => true,
                'message' => trans('newsletters::app.admin.contact-filters.update-success'),
                'filter' => $filter,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.contact-filters.update-error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified filter.
     */
    public function destroy(int $groupId, int $id)
    {
        try {

            $filter = $this->contactFilterRepository->findOrFail($id);
            DB::beginTransaction();

            try {
                // Delete associated conditions first
                $filter->conditions()->delete();

                // Delete the filter
                $this->contactFilterRepository->delete($id);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => trans('newsletters::app.admin.contact-filters.delete-success'),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => trans('newsletters::app.admin.contact-filters.delete-error 1') . ': ' . $e->getMessage(),
                ], 500);
            }
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.contact-filters.not-found'),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.contact-filters.delete-error 2') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unique values for a field.
     */
    public function getFieldValues(Request $request,  int $groupId)
    {
        $request->validate([
            'field' => 'required|string|in:' . implode(',', $this->textFields),
            'contact_group_id' => 'nullable|integer|exists:newsletters_contact_groups,id',
        ]);

        $field = $request->input('field');
        $groupId = $request->input('contact_group_id');

        $query = NewslettersContact::select($field)
            ->whereNotNull($field)
            ->where($field, '!=', '');

        if ($groupId) {
            $query->where('contact_group_id', $groupId);
        }

        // Apply company filter
        $admin = auth()->guard('admin')->user();
        if ($admin && $admin->company_id) {
            $query->where('company_id', $admin->company_id);
        }

        $values = $query->distinct()
            ->orderBy($field)
            ->pluck($field)
            ->filter()
            ->values()
            ->toArray();

        return response()->json([
            'success' => true,
            'values' => $values,
        ]);
    }

    /**
     * Apply filter and get matching contacts.
     */
    public function applyFilter( int $groupId, int $id)
    {
        $filter = $this->contactFilterRepository->findOrFail($id);
        $filter->load('conditions');

        $query = NewslettersContact::query()
            ->where('contact_group_id', $filter->contact_group_id);

        // Apply company filter
        $admin = auth()->guard('admin')->user();
        if ($admin && $admin->company_id) {
            $query->where('company_id', $admin->company_id);
        }

        // Apply all conditions with AND logic
        foreach ($filter->conditions as $condition) {
            $this->applyConditionToQuery($query, $condition);
        }

        $contacts = $query->get();

        return response()->json([
            'success' => true,
            'count' => $contacts->count(),
            'contacts' => $contacts,
        ]);
    }

    /**
     * Prepare condition data for storage.
     */
    protected function prepareConditionData(array $condition, int $filterId, int $sortOrder): array
    {
        $field = $condition['field'];
        $operator = $condition['operator'];

        $data = [
            'filter_id' => $filterId,
            'field' => $field,
            'operator' => $operator,
            'sort_order' => $sortOrder,
        ];

        // Convert date values to timestamps for storage
        if (in_array($field, $this->dateFields)) {
            if (isset($condition['value_from'])) {
                $data['value_from'] = strtotime($condition['value_from']);
            }
            if (isset($condition['value_to'])) {
                $data['value_to'] = strtotime($condition['value_to']);
            }
            if (isset($condition['value'])) {
                $data['value'] = strtotime($condition['value']);
            }
        } else {
            // For numeric and text fields, store as is
            if (isset($condition['value_from'])) {
                $data['value_from'] = in_array($field, $this->numericFields) ? (float) $condition['value_from'] : $condition['value_from'];
            }
            if (isset($condition['value_to'])) {
                $data['value_to'] = in_array($field, $this->numericFields) ? (float) $condition['value_to'] : $condition['value_to'];
            }
            if (isset($condition['value'])) {
                $data['value'] = in_array($field, $this->numericFields) ? (float) $condition['value'] : $condition['value'];
            }
        }

        if (isset($condition['values']) && is_array($condition['values'])) {
            $data['values'] = $condition['values'];
        }

        return $data;
    }

    /**
     * Apply a single condition to query.
     */
    protected function applyConditionToQuery($query, $condition)
    {
        $field = $condition->field;
        $operator = $condition->operator;

        if (in_array($field, $this->dateFields)) {
            $this->applyDateCondition($query, $condition);
        } elseif (in_array($field, $this->numericFields)) {
            $this->applyNumericCondition($query, $condition);
        } elseif (in_array($field, $this->textFields)) {
            $this->applyTextCondition($query, $condition);
        }
    }

    /**
     * Apply date condition to query.
     */
    protected function applyDateCondition($query, $condition)
    {
        $field = $condition->field;

        switch ($condition->operator) {
            case 'between':
                $from = $condition->value_from ? date('Y-m-d', $condition->value_from) : null;
                $to = $condition->value_to ? date('Y-m-d', $condition->value_to) : null;
                if ($from && $to) {
                    $query->whereBetween($field, [$from, $to]);
                }
                break;

            case 'gte':
                $value = $condition->value ? date('Y-m-d', $condition->value) : null;
                if ($value) {
                    $query->where($field, '>=', $value);
                }
                break;

            case 'lte':
                $value = $condition->value ? date('Y-m-d', $condition->value) : null;
                if ($value) {
                    $query->where($field, '<=', $value);
                }
                break;

            case 'equals':
                $value = $condition->value ? date('Y-m-d', $condition->value) : null;
                if ($value) {
                    $query->where($field, $value);
                }
                break;
        }
    }

    /**
     * Apply numeric condition to query.
     */
    protected function applyNumericCondition($query, $condition)
    {
        $field = $condition->field;

        switch ($condition->operator) {
            case 'between':
                if ($condition->value_from !== null && $condition->value_to !== null) {
                    $query->whereBetween($field, [$condition->value_from, $condition->value_to]);
                }
                break;

            case 'gte':
                if ($condition->value !== null) {
                    $query->where($field, '>=', $condition->value);
                }
                break;

            case 'lte':
                if ($condition->value !== null) {
                    $query->where($field, '<=', $condition->value);
                }
                break;

            case 'equals':
                if ($condition->value !== null) {
                    $query->where($field, $condition->value);
                }
                break;
        }
    }

    /**
     * Apply text condition to query.
     */
    protected function applyTextCondition($query, $condition)
    {
        $field = $condition->field;

        switch ($condition->operator) {
            case 'equals':
                if ($condition->value) {
                    $query->where($field, $condition->value);
                }
                break;

            case 'in':
                if ($condition->values && is_array($condition->values) && count($condition->values) > 0) {
                    $query->whereIn($field, $condition->values);
                }
                break;
        }
    }

    /**
     * Validate filter data.
     */
    protected function validateFilter(Request $request): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'conditions' => 'required|array|min:1',
            'conditions.*.field' => 'required|string|in:' . implode(',', $this->allowedFields),
            'conditions.*.operator' => 'required|string',
        ];

        $conditions = $request->input('conditions', []);

        foreach ($conditions as $index => $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? null;

            if (!$field || !$operator) {
                continue;
            }

            // Validate operator based on field type
            // Initialize the rule first if it doesn't exist
            if (!isset($rules["conditions.{$index}.operator"])) {
                $rules["conditions.{$index}.operator"] = 'required|string';
            }

            if (in_array($field, $this->numericFields) || in_array($field, $this->dateFields)) {
                $rules["conditions.{$index}.operator"] .= '|in:between,gte,lte,equals';
            } elseif (in_array($field, $this->textFields)) {
                $rules["conditions.{$index}.operator"] .= '|in:equals,in';
            }

            // Validate values based on operator
            if ($operator === 'between') {
                $rules["conditions.{$index}.value_from"] = 'required';
                $rules["conditions.{$index}.value_to"] = 'required';
                if (in_array($field, $this->dateFields)) {
                    $rules["conditions.{$index}.value_from"] .= '|date';
                    $rules["conditions.{$index}.value_to"] .= '|date';
                } elseif (in_array($field, $this->numericFields)) {
                    $rules["conditions.{$index}.value_from"] .= '|numeric';
                    $rules["conditions.{$index}.value_to"] .= '|numeric';
                }
            } elseif (in_array($operator, ['gte', 'lte', 'equals'])) {
                $rules["conditions.{$index}.value"] = 'required';
                if (in_array($field, $this->dateFields)) {
                    $rules["conditions.{$index}.value"] .= '|date';
                } elseif (in_array($field, $this->numericFields)) {
                    $rules["conditions.{$index}.value"] .= '|numeric';
                }
            } elseif ($operator === 'in') {
                $rules["conditions.{$index}.values"] = 'required|array|min:1';
                $rules["conditions.{$index}.values.*"] = 'required|string';
            }
        }

        return $request->validate($rules);
    }
}
