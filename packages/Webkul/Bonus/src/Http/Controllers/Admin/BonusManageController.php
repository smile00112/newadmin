<?php

namespace Webkul\Bonus\Http\Controllers\Admin;

use App\Services\BonusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Bonus\Models\BonusHistory;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Customer\Repositories\CustomerRepository;

class BonusManageController extends Controller
{
    /**
     * Static pagination count.
     *
     * @var int
     */
    public const COUNT = 10;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected BonusService $bonusService
    ) {}

    /**
     * Search customers by email, name or phone.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchCustomer(): JsonResponse
    {
        $query = request()->input('query');

        if (empty($query) || strlen($query) < 2) {
            return new JsonResponse([
                'data' => [],
            ]);
        }

        $searchTerm = '%' . urldecode($query) . '%';

        $customers = $this->customerRepository->scopeQuery(function ($queryBuilder) use ($searchTerm) {
            return $queryBuilder
                ->where(function ($q) use ($searchTerm) {
                    $q->where('email', 'like', $searchTerm)
                        ->orWhere('first_name', 'like', $searchTerm)
                        ->orWhere('last_name', 'like', $searchTerm)
                        ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', $searchTerm)
                        ->orWhere('phone', 'like', $searchTerm);
                })
                ->orderBy('created_at', 'desc');
        })->paginate(self::COUNT);

        return new JsonResponse([
            'data' => $customers->items(),
            'total' => $customers->total(),
        ]);
    }

    /**
     * Get customer information including bonus balance.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerInfo(int $id): JsonResponse
    {
        $customer = CustomerProxy::find($id);

        if (! $customer) {
            return new JsonResponse([
                'success' => false,
                'message' => trans('bonus::app.admin.settings.manage.customer-not-found'),
            ], 404);
        }

        $availableBalance = $this->bonusService->getAvailableBonusBalance($customer);
        $totalBalance = $customer->bonus_balance ?? 0;

        // Get recent bonus history
        $recentHistory = BonusHistory::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return new JsonResponse([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'email' => $customer->email,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'name' => $customer->first_name . ' ' . $customer->last_name,
                'available_balance' => round($availableBalance, 2),
                'total_balance' => round($totalBalance, 2),
            ],
            'recent_history' => $recentHistory->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'amount' => round($item->amount, 2),
                    'balance_after' => round($item->balance_after, 2),
                    'description' => $item->description,
                    'created_at' => $item->created_at->format('d.m.Y H:i'),
                    'expires_at' => $item->expires_at ? $item->expires_at->format('d.m.Y') : null,
                ];
            }),
        ]);
    }

    /**
     * Add bonus to customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBonus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'success' => false,
                'message' => trans('bonus::app.admin.settings.manage.validation-error'),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $customer = CustomerProxy::find($request->input('customer_id'));

            if (! $customer) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('bonus::app.admin.settings.manage.customer-not-found'),
                ], 404);
            }

            $amount = (float) $request->input('amount');
            $description = $request->input('description', 'Ручное начисление бонусов администратором');

            $history = $this->bonusService->accrueBonus($customer, $amount, null, null);

            // Update description if provided
            if ($request->has('description')) {
                $history->update(['description' => $description]);
            }

            $availableBalance = $this->bonusService->getAvailableBonusBalance($customer);

            return new JsonResponse([
                'success' => true,
                'message' => trans('bonus::app.admin.settings.manage.bonus-added-success'),
                'balance' => round($availableBalance, 2),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deduct bonus from customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deductBonus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'success' => false,
                'message' => trans('bonus::app.admin.settings.manage.validation-error'),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $customer = CustomerProxy::find($request->input('customer_id'));

            if (! $customer) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('bonus::app.admin.settings.manage.customer-not-found'),
                ], 404);
            }

            $amount = (float) $request->input('amount');
            $description = $request->input('description', 'Ручное списание бонусов администратором');

            $historyRecords = $this->bonusService->deductBonus($customer, $amount, null);

            // Update description for all records if provided
            if ($request->has('description')) {
                foreach ($historyRecords as $record) {
                    $record->update(['description' => $description]);
                }
            }

            $availableBalance = $this->bonusService->getAvailableBonusBalance($customer);

            return new JsonResponse([
                'success' => true,
                'message' => trans('bonus::app.admin.settings.manage.bonus-deducted-success'),
                'balance' => round($availableBalance, 2),
            ]);
        } catch (\RuntimeException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
