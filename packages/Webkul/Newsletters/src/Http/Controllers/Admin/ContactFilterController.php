<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\ContactFilterRepository;
use Webkul\Newsletters\Repositories\ContactFilterConditionRepository;
use Webkul\Newsletters\Repositories\ContactGroupRepository;
use Webkul\Newsletters\Repositories\ContactRepository;
use Webkul\Newsletters\Models\NewslettersContact;
use Webkul\Core\Models\CoreConfig;

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
    public function update(Request $request, int $groupId, int $filterId)
    {
        $filter = $this->contactFilterRepository->findOrFail($filterId);

        $validated = $this->validateFilter($request);

        DB::beginTransaction();

        try {
            // Update filter name
            $this->contactFilterRepository->update([
                'name' => $validated['name'],
            ], $filterId);

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
                    'message' => trans('newsletters::app.admin.contact-filters.delete-error') . ': ' . $e->getMessage(),
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
                'message' => trans('newsletters::app.admin.contact-filters.delete-error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unique values for a field.
     */
    public function getFieldValues(Request $request)
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
     * Count contacts matching filter conditions.
     */
    public function countContacts(Request $request, int $groupId)
    {
        $request->validate([
            'conditions' => 'required|array|min:1',
        ]);

        $group = $this->contactGroupRepository->findOrFail($groupId);

        $conditions = $request->input('conditions', []);

        // Generate cache key based on groupId and conditions hash
        $conditionsHash = md5(json_encode($conditions));
        $cacheKey = "contact_filter_count_{$groupId}_{$conditionsHash}";

        // Get counts from cache or calculate them
        $result = Cache::remember($cacheKey, 3600, function () use ($groupId, $conditions) {
            // Helper function to build base query with conditions
            $buildQuery = function () use ($groupId, $conditions) {
                $query = NewslettersContact::query()
                    ->where('contact_group_id', $groupId);

                // Apply company filter
                $admin = auth()->guard('admin')->user();
                if ($admin && $admin->company_id) {
                    $query->where('company_id', $admin->company_id);
                }

                // Apply all conditions with AND logic
                foreach ($conditions as $conditionData) {
                    // Create a temporary condition object for applyConditionToQuery
                    $condition = (object) [
                        'field' => $conditionData['field'] ?? null,
                        'operator' => $conditionData['operator'] ?? null,
                        'value' => $conditionData['value'] ?? null,
                        'value_from' => $conditionData['value_from'] ?? null,
                        'value_to' => $conditionData['value_to'] ?? null,
                        'values' => $conditionData['values'] ?? null,
                    ];

                     // Convert date values from string to timestamp if needed
                     if (in_array($condition->field, $this->dateFields)) {
                         // Для дат без времени всегда используем UTC при парсинге, чтобы избежать смещения
                         // Часовой пояс из настроек используется только для отображения, но не для парсинга
                         $utcTimezone = new \DateTimeZone('UTC');

                         if ($condition->value_from) {
                            if (is_numeric($condition->value_from)) {
                                // Уже timestamp
                                $condition->value_from = $condition->value_from;
                            } else {
                                // Парсим дату как полночь UTC, чтобы избежать смещения
                                $dateTime = \DateTime::createFromFormat('Y-m-d', $condition->value_from, $utcTimezone);
                                if ($dateTime === false) {
                                    // Если парсинг не удался, пробуем через new DateTime
                                    $dateTime = new \DateTime($condition->value_from . ' 00:00:00', $utcTimezone);
                                } else {
                                    $dateTime->setTime(0, 0, 0);
                                }
                                $condition->value_from = $dateTime->getTimestamp();
                            }
                        }
                        if ($condition->value_to) {
                            if (is_numeric($condition->value_to)) {
                                // Уже timestamp
                                $condition->value_to = $condition->value_to;
                            } else {
                                // Парсим дату как полночь UTC, чтобы избежать смещения
                                $utcTimezone = new \DateTimeZone('UTC');
                                $dateTime = \DateTime::createFromFormat('Y-m-d', $condition->value_to, $utcTimezone);
                                if ($dateTime === false) {
                                    // Если парсинг не удался, пробуем через new DateTime
                                    $dateTime = new \DateTime($condition->value_to . ' 00:00:00', $utcTimezone);
                                } else {
                                    $dateTime->setTime(0, 0, 0);
                                }
                                $condition->value_to = $dateTime->getTimestamp();
                            }
                        }
                        if ($condition->value) {
                            if (is_numeric($condition->value)) {
                                // Уже timestamp
                                $condition->value = $condition->value;
                            } else {
                                // Парсим дату как полночь UTC, чтобы избежать смещения
                                $utcTimezone = new \DateTimeZone('UTC');
                                $dateTime = \DateTime::createFromFormat('Y-m-d', $condition->value, $utcTimezone);
                                if ($dateTime === false) {
                                    // Если парсинг не удался, пробуем через new DateTime
                                    $dateTime = new \DateTime($condition->value . ' 00:00:00', $utcTimezone);
                                } else {
                                    $dateTime->setTime(0, 0, 0);
                                }
                                $condition->value = $dateTime->getTimestamp();
                            }
                        }
                    }

                    $this->applyConditionToQuery($query, $condition);
                }

                return $query;
            };

            // Build base query
            $baseQuery = $buildQuery();

            // Total count
            $totalCount = (clone $baseQuery)->count();

            // Email channel count (where email is not null and not empty)
            $emailCount = (clone $baseQuery)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->count();

            // Telegram channel count (where telegram_user_id is not null and not empty)
            $telegramCount = (clone $baseQuery)
                ->whereNotNull('telegram_user_id')
                ->where('telegram_user_id', '!=', '')
                ->count();

            // WhatsApp channel count (where phone is not null and not empty)
            $whatsappCount = (clone $baseQuery)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->count();

            return [
                'count' => $totalCount,
                'channels' => [
                    'email' => $emailCount,
                    'telegram' => $telegramCount,
                    'whatsapp' => $whatsappCount,
                ],
            ];
        });

        // Handle old cache format (when result is just an integer)
        if (is_int($result) || (is_numeric($result) && !is_array($result))) {
            // Old format: just a count number, clear cache and recalculate
            Cache::forget($cacheKey);

            // Recalculate with new format
            $buildQuery = function () use ($groupId, $conditions) {
                $query = NewslettersContact::query()
                    ->where('contact_group_id', $groupId);

                $admin = auth()->guard('admin')->user();
                if ($admin && $admin->company_id) {
                    $query->where('company_id', $admin->company_id);
                }

                foreach ($conditions as $conditionData) {
                    $condition = (object) [
                        'field' => $conditionData['field'] ?? null,
                        'operator' => $conditionData['operator'] ?? null,
                        'value' => $conditionData['value'] ?? null,
                        'value_from' => $conditionData['value_from'] ?? null,
                        'value_to' => $conditionData['value_to'] ?? null,
                        'values' => $conditionData['values'] ?? null,
                    ];

                    if (in_array($condition->field, $this->dateFields)) {
                        $utcTimezone = new \DateTimeZone('UTC');

                        if ($condition->value_from) {
                            if (!is_numeric($condition->value_from)) {
                                $dateTime = \DateTime::createFromFormat('Y-m-d', $condition->value_from, $utcTimezone);
                                if ($dateTime === false) {
                                    $dateTime = new \DateTime($condition->value_from . ' 00:00:00', $utcTimezone);
                                } else {
                                    $dateTime->setTime(0, 0, 0);
                                }
                                $condition->value_from = $dateTime->getTimestamp();
                            }
                        }
                        if ($condition->value_to) {
                            if (!is_numeric($condition->value_to)) {
                                $dateTime = \DateTime::createFromFormat('Y-m-d', $condition->value_to, $utcTimezone);
                                if ($dateTime === false) {
                                    $dateTime = new \DateTime($condition->value_to . ' 00:00:00', $utcTimezone);
                                } else {
                                    $dateTime->setTime(0, 0, 0);
                                }
                                $condition->value_to = $dateTime->getTimestamp();
                            }
                        }
                        if ($condition->value) {
                            if (!is_numeric($condition->value)) {
                                $dateTime = \DateTime::createFromFormat('Y-m-d', $condition->value, $utcTimezone);
                                if ($dateTime === false) {
                                    $dateTime = new \DateTime($condition->value . ' 00:00:00', $utcTimezone);
                                } else {
                                    $dateTime->setTime(0, 0, 0);
                                }
                                $condition->value = $dateTime->getTimestamp();
                            }
                        }
                    }

                    $this->applyConditionToQuery($query, $condition);
                }

                return $query;
            };

            $baseQuery = $buildQuery();
            $totalCount = (clone $baseQuery)->count();
            $emailCount = (clone $baseQuery)->whereNotNull('email')->where('email', '!=', '')->count();
            $telegramCount = (clone $baseQuery)->whereNotNull('telegram_user_id')->where('telegram_user_id', '!=', '')->count();
            $whatsappCount = (clone $baseQuery)->whereNotNull('phone')->where('phone', '!=', '')->count();

            $result = [
                'count' => $totalCount,
                'channels' => [
                    'email' => $emailCount,
                    'telegram' => $telegramCount,
                    'whatsapp' => $whatsappCount,
                ],
            ];

            // Cache the new format
            Cache::put($cacheKey, $result, 3600);
        }

        // Ensure result is in correct format
        if (!is_array($result) || !isset($result['count']) || !isset($result['channels'])) {
            // Fallback: return zero counts if format is invalid
            $result = [
                'count' => 0,
                'channels' => [
                    'email' => 0,
                    'telegram' => 0,
                    'whatsapp' => 0,
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'count' => $result['count'],
            'channels' => $result['channels'],
        ]);
    }

    /**
     * Apply filter and get matching contacts.
     */
    public function applyFilter(int $filterId)
    {
        $filter = $this->contactFilterRepository->findOrFail($filterId);
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
            // Для дат без времени всегда используем UTC, чтобы избежать смещения при сохранении
            // Часовой пояс из настроек используется только для отображения и фильтрации
            $utcTimezone = new \DateTimeZone('UTC');

            if (isset($condition['value_from'])) {
                // Парсим дату как полночь UTC, чтобы избежать смещения
                $dateTime = \DateTime::createFromFormat('Y-m-d', $condition['value_from'], $utcTimezone);
                if ($dateTime === false) {
                    // Если парсинг не удался, пробуем через new DateTime
                    $dateTime = new \DateTime($condition['value_from'] . ' 00:00:00', $utcTimezone);
                } else {
                    $dateTime->setTime(0, 0, 0);
                }
                $data['value_from'] = $dateTime->getTimestamp();
            }
            if (isset($condition['value_to'])) {
                // Парсим дату как полночь UTC, чтобы избежать смещения
                $dateTime = \DateTime::createFromFormat('Y-m-d', $condition['value_to'], $utcTimezone);
                if ($dateTime === false) {
                    // Если парсинг не удался, пробуем через new DateTime
                    $dateTime = new \DateTime($condition['value_to'] . ' 00:00:00', $utcTimezone);
                } else {
                    $dateTime->setTime(0, 0, 0);
                }
                $data['value_to'] = $dateTime->getTimestamp();
            }
            if (isset($condition['value'])) {
                // Парсим дату как полночь UTC, чтобы избежать смещения
                $dateTime = \DateTime::createFromFormat('Y-m-d', $condition['value'], $utcTimezone);
                if ($dateTime === false) {
                    // Если парсинг не удался, пробуем через new DateTime
                    $dateTime = new \DateTime($condition['value'] . ' 00:00:00', $utcTimezone);
                } else {
                    $dateTime->setTime(0, 0, 0);
                }
                $data['value'] = $dateTime->getTimestamp();
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

        // Special handling for birth_date - compare only day and month, ignore year
        if ($field === 'birth_date') {
            $this->applyBirthDateCondition($query, $condition);
            return;
        }

        // Standard date comparison for other date fields
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
     * Apply birth date condition - compare only day and month, ignore year.
     */
    protected function applyBirthDateCondition($query, $condition)
    {
        switch ($condition->operator) {
            case 'equals':
                $value = $condition->value ? date('Y-m-d', $condition->value) : null;
                if ($value) {
                    $month = date('m', $condition->value);
                    $day = date('d', $condition->value);
                    $query->whereRaw('MONTH(birth_date) = ? AND DAY(birth_date) = ?', [$month, $day]);
                }
                break;

            case 'between':
                $from = $condition->value_from ? date('Y-m-d', $condition->value_from) : null;
                $to = $condition->value_to ? date('Y-m-d', $condition->value_to) : null;
                if ($from && $to) {
                    $fromMonth = (int)date('m', $condition->value_from);
                    $fromDay = (int)date('d', $condition->value_from);
                    $toMonth = (int)date('m', $condition->value_to);
                    $toDay = (int)date('d', $condition->value_to);

                    // Handle range that crosses year boundary (e.g., Dec 25 - Jan 5)
                    if ($fromMonth > $toMonth || ($fromMonth == $toMonth && $fromDay > $toDay)) {
                        // Range crosses year boundary: (fromMonth/fromDay to Dec 31) OR (Jan 1 to toMonth/toDay)
                        $query->where(function($q) use ($fromMonth, $fromDay, $toMonth, $toDay) {
                            $q->where(function($q1) use ($fromMonth, $fromDay) {
                                // From date to end of year: month > fromMonth OR (month = fromMonth AND day >= fromDay)
                                $q1->whereRaw('MONTH(birth_date) > ?', [$fromMonth])
                                   ->orWhereRaw('(MONTH(birth_date) = ? AND DAY(birth_date) >= ?)', [$fromMonth, $fromDay]);
                            })
                            ->orWhere(function($q1) use ($toMonth, $toDay) {
                                // Start of year to to date: month < toMonth OR (month = toMonth AND day <= toDay)
                                $q1->whereRaw('MONTH(birth_date) < ?', [$toMonth])
                                   ->orWhereRaw('(MONTH(birth_date) = ? AND DAY(birth_date) <= ?)', [$toMonth, $toDay]);
                            });
                        });
                    } else {
                        // Normal range within same year
                        if ($fromMonth == $toMonth) {
                            // Same month: day between fromDay and toDay
                            $query->whereRaw('MONTH(birth_date) = ? AND DAY(birth_date) >= ? AND DAY(birth_date) <= ?',
                                [$fromMonth, $fromDay, $toDay]);
                        } else {
                            // Different months: (month > fromMonth AND month < toMonth) OR
                            // (month = fromMonth AND day >= fromDay) OR (month = toMonth AND day <= toDay)
                            $query->where(function($q) use ($fromMonth, $fromDay, $toMonth, $toDay) {
                                $q->whereRaw('MONTH(birth_date) > ? AND MONTH(birth_date) < ?', [$fromMonth, $toMonth])
                                   ->orWhereRaw('(MONTH(birth_date) = ? AND DAY(birth_date) >= ?)', [$fromMonth, $fromDay])
                                   ->orWhereRaw('(MONTH(birth_date) = ? AND DAY(birth_date) <= ?)', [$toMonth, $toDay]);
                            });
                        }
                    }
                }
                break;

            case 'gte':
                $value = $condition->value ? date('Y-m-d', $condition->value) : null;
                if ($value) {
                    $month = (int)date('m', $condition->value);
                    $day = (int)date('d', $condition->value);
                    $query->where(function($q) use ($month, $day) {
                        $q->whereRaw('MONTH(birth_date) > ?', [$month])
                          ->orWhere(function($q1) use ($month, $day) {
                              $q1->whereRaw('MONTH(birth_date) = ? AND DAY(birth_date) >= ?', [$month, $day]);
                          });
                    });
                }
                break;

            case 'lte':
                $value = $condition->value ? date('Y-m-d', $condition->value) : null;
                if ($value) {
                    $month = (int)date('m', $condition->value);
                    $day = (int)date('d', $condition->value);
                    $query->where(function($q) use ($month, $day) {
                        $q->whereRaw('MONTH(birth_date) < ?', [$month])
                          ->orWhere(function($q1) use ($month, $day) {
                              $q1->whereRaw('MONTH(birth_date) = ? AND DAY(birth_date) <= ?', [$month, $day]);
                          });
                    });
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
