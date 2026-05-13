<?php

namespace Webkul\Admin\Http\Controllers\Marketing\PushNotifications;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webkul\Admin\DataGrids\Marketing\PushNotifications\PushCampaignDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\PushNotification\Models\PushCampaign;
use Webkul\PushNotification\Models\PushCampaignLog;
use Webkul\PushNotification\Services\PushCampaignService;

class PushCampaignController extends Controller
{
    public function __construct(
        protected PushCampaignService $campaignService,
        protected CustomerGroupRepository $customerGroupRepository
    ) {}

    /**
     * Display campaign list.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(PushCampaignDataGrid::class)->process();
        }

        return view('admin::marketing.push-notifications.campaigns.index');
    }

    /**
     * Show create form panel (used inside iframe drawer).
     */
    public function createPanel()
    {
        $customerGroups = $this->customerGroupRepository->all();
        $selectedCustomers = [];

        return view('admin::marketing.push-notifications.campaigns.panel', compact('customerGroups', 'selectedCustomers'));
    }

    /**
     * Store a new campaign.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'title'     => 'required|string|max:255',
            'body'      => 'required|string|max:4096',
            'image_url' => 'nullable|url|max:512',
            'deep_link' => 'nullable|string|max:512',
            'data'      => 'nullable|array',
            'segment_filters'                        => 'nullable|array',
            'segment_filters.customer_group_ids'     => 'nullable|array',
            'segment_filters.customer_group_ids.*'   => 'integer',
            'segment_filters.customer_ids'           => 'nullable|array',
            'segment_filters.customer_ids.*'         => 'integer',
            'segment_filters.gender'                 => 'nullable|in:Male,Female,Other',
            'segment_filters.phones'                 => 'nullable|string|max:10000',
            'segment_filters.has_orders'             => 'nullable|in:0,1',
            'segment_filters.registered_from'        => 'nullable|date',
            'segment_filters.registered_to'          => 'nullable|date',
            'segment_filters.last_order_from'        => 'nullable|date',
            'segment_filters.last_order_to'          => 'nullable|date',
        ]);

        if (isset($validated['segment_filters']['phones'])) {
            $validated['segment_filters']['phones'] = $this->normalizePhonesInput($validated['segment_filters']['phones']);
        }

        if (isset($validated['segment_filters']['customer_ids'])) {
            $validated['segment_filters']['customer_ids'] = $this->normalizeCustomerIdsInput($validated['segment_filters']['customer_ids']);
        }

        // Clean up empty filter values
        if (! empty($validated['segment_filters'])) {
            $validated['segment_filters'] = array_filter(
                $validated['segment_filters'],
                fn ($v) => $v !== null && $v !== '' && $v !== []
            );
        }

        $campaign = PushCampaign::create([
            ...$validated,
            'status'     => 'draft',
            'created_by' => auth()->id(),
        ]);

        return new JsonResponse([
            'message' => 'Кампания создана',
            'id'      => $campaign->id,
        ]);
    }

    /**
     * Show edit form panel.
     */
    public function editPanel(int $id)
    {
        $campaign = PushCampaign::findOrFail($id);
        $customerGroups = $this->customerGroupRepository->all();
        $selectedIds = $this->normalizeCustomerIdsInput($campaign->segment_filters['customer_ids'] ?? []);

        $selectedCustomers = [];

        if (! empty($selectedIds)) {
            $selectedCustomers = DB::table('customers')
                ->whereIn('id', $selectedIds)
                ->select('id', 'first_name', 'last_name', 'phone')
                ->get()
                ->map(function ($customer) {
                    $name = trim(($customer->first_name ?? '').' '.($customer->last_name ?? ''));

                    return [
                        'id'    => (int) $customer->id,
                        'name'  => $name !== '' ? $name : ('Клиент #'.$customer->id),
                        'phone' => $customer->phone,
                    ];
                })
                ->values()
                ->all();
        }

        return view('admin::marketing.push-notifications.campaigns.panel', compact('campaign', 'customerGroups', 'selectedCustomers'));
    }

    /**
     * Update an existing campaign (only draft/failed allowed).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $campaign = PushCampaign::findOrFail($id);

        if (! in_array($campaign->status, ['draft', 'failed'])) {
            return new JsonResponse(['message' => 'Нельзя редактировать отправленную кампанию'], 422);
        }

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'title'     => 'required|string|max:255',
            'body'      => 'required|string|max:4096',
            'image_url' => 'nullable|url|max:512',
            'deep_link' => 'nullable|string|max:512',
            'data'      => 'nullable|array',
            'segment_filters'                        => 'nullable|array',
            'segment_filters.customer_group_ids'     => 'nullable|array',
            'segment_filters.customer_group_ids.*'   => 'integer',
            'segment_filters.customer_ids'           => 'nullable|array',
            'segment_filters.customer_ids.*'         => 'integer',
            'segment_filters.gender'                 => 'nullable|in:Male,Female,Other',
            'segment_filters.phones'                 => 'nullable|string|max:10000',
            'segment_filters.has_orders'             => 'nullable|in:0,1',
            'segment_filters.registered_from'        => 'nullable|date',
            'segment_filters.registered_to'          => 'nullable|date',
            'segment_filters.last_order_from'        => 'nullable|date',
            'segment_filters.last_order_to'          => 'nullable|date',
        ]);

        if (isset($validated['segment_filters']['phones'])) {
            $validated['segment_filters']['phones'] = $this->normalizePhonesInput($validated['segment_filters']['phones']);
        }

        if (isset($validated['segment_filters']['customer_ids'])) {
            $validated['segment_filters']['customer_ids'] = $this->normalizeCustomerIdsInput($validated['segment_filters']['customer_ids']);
        }

        if (! empty($validated['segment_filters'])) {
            $validated['segment_filters'] = array_filter(
                $validated['segment_filters'],
                fn ($v) => $v !== null && $v !== '' && $v !== []
            );
        }

        $campaign->update(array_merge($validated, ['status' => 'draft']));

        return new JsonResponse(['message' => 'Кампания обновлена']);
    }

    /**
     * Send campaign immediately.
     */
    public function send(int $id): JsonResponse
    {
        $campaign = PushCampaign::findOrFail($id);

        if (! in_array($campaign->status, ['draft', 'failed'])) {
            return new JsonResponse(['message' => 'Кампания уже отправлена или отправляется'], 422);
        }

        $this->campaignService->dispatchCampaign($campaign);

        return new JsonResponse([
            'message'           => 'Рассылка запущена',
            'total_recipients'  => $campaign->fresh()->total_recipients,
        ]);
    }

    /**
     * Preview audience size for given filters.
     */
    public function audienceCount(Request $request): JsonResponse
    {
        $filters = $request->input('segment_filters', []);

        if (isset($filters['phones']) && is_string($filters['phones'])) {
            $filters['phones'] = $this->normalizePhonesInput($filters['phones']);
        }

        if (isset($filters['customer_ids']) && is_array($filters['customer_ids'])) {
            $filters['customer_ids'] = $this->normalizeCustomerIdsInput($filters['customer_ids']);
        }

        // Clean empty values
        if (is_array($filters)) {
            $filters = array_filter($filters, fn ($v) => $v !== null && $v !== '' && $v !== []);
        }

        $count = $this->campaignService->countAudience($filters);

        return new JsonResponse(['count' => $count]);
    }

    /**
     * Search customers with active push token by phone or name.
     */
    public function customerSearch(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if ($query === '' || mb_strlen($query) < 2) {
            return new JsonResponse(['items' => []]);
        }

        $digits = preg_replace('/\D+/', '', $query);

        $customers = DB::table('customers')
            ->join('customer_push_tokens as cpt', function ($join) {
                $join->on('cpt.customer_id', '=', 'customers.id')
                    ->where('cpt.is_active', 1);
            })
            ->where('customers.status', 1)
            ->where(function ($builder) use ($query, $digits) {
                $like = '%'.$query.'%';

                $builder->where('customers.first_name', 'like', $like)
                    ->orWhere('customers.last_name', 'like', $like)
                    ->orWhereRaw("CONCAT(COALESCE(customers.first_name, ''), ' ', COALESCE(customers.last_name, '')) like ?", [$like])
                    ->orWhere('customers.email', 'like', $like);

                if ($digits !== '') {
                    $builder->orWhereRaw("REGEXP_REPLACE(customers.phone, '[^0-9]', '') like ?", ['%'.$digits.'%']);
                }
            })
            ->groupBy('customers.id', 'customers.first_name', 'customers.last_name', 'customers.phone', 'customers.email')
            ->selectRaw("customers.id, customers.first_name, customers.last_name, customers.phone, customers.email, COUNT(cpt.id) as active_tokens")
            ->orderByDesc('active_tokens')
            ->limit(20)
            ->get()
            ->map(function ($customer) {
                $name = trim(($customer->first_name ?? '').' '.($customer->last_name ?? ''));

                return [
                    'id'            => (int) $customer->id,
                    'name'          => $name !== '' ? $name : ('Клиент #'.$customer->id),
                    'phone'         => $customer->phone,
                    'email'         => $customer->email,
                    'active_tokens' => (int) $customer->active_tokens,
                ];
            })
            ->values();

        return new JsonResponse(['items' => $customers]);
    }

    /**
     * Normalize textarea phone input into an array of unique digit-only values.
     */
    private function normalizePhonesInput(?string $input): array
    {
        if (! $input) {
            return [];
        }

        $parts = preg_split('/[\n,;]+/', $input) ?: [];
        $phones = [];

        foreach ($parts as $part) {
            $normalized = preg_replace('/\D+/', '', trim($part));

            if ($normalized !== '') {
                $phones[] = $normalized;
            }
        }

        return array_values(array_unique($phones));
    }

    /**
     * Normalize customer IDs from request payload.
     */
    private function normalizeCustomerIdsInput(array $ids): array
    {
        $normalized = array_values(array_filter(array_map(static fn ($id) => (int) $id, $ids), static fn ($id) => $id > 0));

        return array_values(array_unique($normalized));
    }

    /**
     * Show campaign statistics page.
     */
    public function show(int $id)
    {
        $campaign = PushCampaign::findOrFail($id);

        $estimatedAudience = in_array($campaign->status, ['draft', 'failed'])
            ? $this->campaignService->countAudience($campaign->segment_filters ?? [])
            : $campaign->total_recipients;

        $logs = PushCampaignLog::where('campaign_id', $id)
            ->with('customer:id,first_name,last_name,email,phone')
            ->latest()
            ->paginate(50);

        $statusBreakdown = PushCampaignLog::where('campaign_id', $id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin::marketing.push-notifications.campaigns.show', compact(
            'campaign',
            'logs',
            'statusBreakdown',
            'estimatedAudience'
        ));
    }

    /**
     * Delete a campaign (only draft/sent allowed).
     */
    public function destroy(int $id): JsonResponse
    {
        $campaign = PushCampaign::findOrFail($id);

        if ($campaign->status === 'sending') {
            return new JsonResponse(['message' => 'Нельзя удалить кампанию в процессе отправки'], 422);
        }

        $campaign->delete();

        return new JsonResponse(['message' => 'Кампания удалена']);
    }
}
