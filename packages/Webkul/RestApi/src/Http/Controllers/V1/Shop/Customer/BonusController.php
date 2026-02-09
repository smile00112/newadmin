<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Bonus\Models\BonusLevel;
use Webkul\Bonus\Repositories\BonusLevelRepository;
use Webkul\Bonus\Repositories\BonusTransactionRepository;
use Webkul\Bonus\Repositories\CustomerBonusRepository;
use Webkul\Bonus\Services\BonusService;
use Webkul\Sales\Models\Order;

class BonusController extends CustomerController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected BonusService $bonusService,
        protected CustomerBonusRepository $customerBonusRepository,
        protected BonusLevelRepository $bonusLevelRepository,
        protected BonusTransactionRepository $bonusTransactionRepository
    ) {
        parent::__construct();
    }
    /**
     * Returns test bonus information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexTest(): JsonResponse
    {
        $data = [
            'points_balance' => 450,
            'spent_sum' => 0,
            'percent_max' => 10,
            'show_levels_info' => true,
            'level' => 2,
            'type' => 'orderscount',
            'levels_info' => [
                [
                    'id' => 1,
                    'name' => '1 грейд',
                    'cashback_percent' => 2,
                    'rule_to_level' => 0,
                    'description_top' => 'Возвращаем 2% заказа.',
                    'description_bottom' => '0-10 заказов',
                ],
                [
                    'id' => 2,
                    'name' => '3 грейд',
                    'cashback_percent' => 5,
                    'rule_to_level' => 10,
                    'description_top' => 'Возвращаем 5% заказа.',
                    'description_bottom' => '10-15 заказов',
                ],
                [
                    'id' => 3,
                    'name' => '3 грейд',
                    'rule_to_level' => 15,
                    'cashback_percent' => 8,
                    'description_top' => 'Возвращаем 8% заказа.',
                    'description_bottom' => '15-20 заказов',
                ],
                [
                    'id' => 4,
                    'name' => '4 грейд',
                    'rule_to_level' => 10,
                    'cashback_percent' => 2,
                    'description_top' => 'Возвращаем 10% заказа.',
                    'description_bottom' => '20-25 заказов',
                ],
                [
                    'id' => 5,
                    'name' => '5 грейд',
                    'rule_to_level' => 25,
                    'cashback_percent' => 12,
                    'description_top' => 'Возвращаем 12% заказа.',
                    'description_bottom' => '25+',
                ],
            ],
            'next_level_info' => [
                'text1' => 'Вы сделалли 12 заказов',
                'text2' => 'Осталось 2 закзаза до  до повышения',
            ],
            'bonus_history' => [],
        ];

        return response()->json($data);
    }

    /**
     * Returns bonus information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $this->resolveShopUser($request);

        if (! $this->bonusService->isEnabled()) {
            return response()->json([
                'points_balance' => 0,
                'balance' => 0,
                'spent_sum' => 0,
                'orders_count' => 0,
                'percent_max' => 0,
                'show_levels_info' => false,
                'level' => null,
                'type' => null,
                'levels_info' => [],
                'next_level_info' => null,
                'bonus_history' => [],
            ]);
        }

        $currencyCode = core()->getCurrentCurrencyCode();

        // Get bonus balance
        $pointsBalance = $this->bonusService->getAvailableBonuses($customer->id, $currencyCode);
        $totalBalance = $this->customerBonusRepository->getBalance($customer->id, $currencyCode);

        // Get settings
        $calculationType = (string) core()->getConfigData('bonus.general.settings.calculation_type');
        $maxUsagePercent = (float) core()->getConfigData('bonus.general.settings.max_usage_percent');
        $showLevelsInfo = (bool) core()->getConfigData('bonus.general.settings.show_levels_info', false);

        // Get customer statistics
        $ordersCount = $customer->orders()
            ->where('status', Order::STATUS_COMPLETED)
            ->count();

        $spentSum = (float) $customer->orders()
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('base_grand_total');

        // Get current level
        $currentLevel = $this->bonusService->calculateCustomerLevel($customer, $calculationType);

        // Get all active levels
        $allLevels = $this->bonusLevelRepository->getActiveLevels();

        // Format levels info
        $levelsInfo = [];
        $nextLevel = null;
        $foundCurrent = false;

        foreach ($allLevels as $level) {
            $isCurrent = $currentLevel && $currentLevel->id === $level->id;

            if ($isCurrent) {
                $foundCurrent = true;
            }

            // Format description based on calculation type
            $descriptionTop = 'Возвращаем ' . $level->cashback_percent . '% заказа.';
            $descriptionBottom = $this->formatLevelDescription($level, $calculationType, $allLevels);

            $levelData = [
                'id' => $level->id,
                'name' => $level->name,
                'rule_to_level' => (int) $level->threshold_value,
                'cashback_percent' => (int) $level->cashback_percent,
                'description_top' => $descriptionTop,
                'description_bottom' => $descriptionBottom,
                'is_current' => $isCurrent,
            ];

            // Find next level (first level after current)
            if ($foundCurrent && ! $isCurrent && $nextLevel === null) {
                $nextLevel = $level;
//                $levelData['name'] = $level->name;

                // Calculate remaining to next level
                $currentValue = match ($calculationType) {
                    BonusLevel::CALCULATION_TYPE_ORDERS_COUNT => $ordersCount,
                    BonusLevel::CALCULATION_TYPE_TOTAL_SPENT => $spentSum,
                    default => 0,
                };

//                if ($level->threshold_value > $currentValue) {
//                    $levelData['rule_to_level'] = (float) ($level->threshold_value - $currentValue);
//                }
            }

            $levelsInfo[] = $levelData;
        }

        // Format next level info
        $remaining = 0;
        $nextLevelInfo = null;
        if ($nextLevel) {
            $currentValue = match ($calculationType) {
                BonusLevel::CALCULATION_TYPE_ORDERS_COUNT => $ordersCount,
                BonusLevel::CALCULATION_TYPE_TOTAL_SPENT => $spentSum,
                default => 0,
            };

            $remaining = max(0, $nextLevel->threshold_value - $currentValue);

            if ($calculationType === BonusLevel::CALCULATION_TYPE_ORDERS_COUNT) {
                $nextLevelInfo = [
                    'text1' => 'Вы сделали ' . (int) $currentValue . ' заказов',
                    'text2' => '<b>' . (int) $remaining . ' '. $this->getOrderDeclension($remaining) .'</b> до повышения кэшбэка',
                ];
            } else {
                $nextLevelInfo = [
                    'text1' => 'Вы потратили ' . core()->formatPrice($currentValue),
                    'text2' => 'Осталось потратить ' . core()->formatPrice($remaining) . ' до повышения',
                ];
            }
        }

        // Get bonus history (limit to 20 most recent)
        $transactions = $this->bonusTransactionRepository->getCustomerTransactions($customer->id)
            ->take(20);

        $bonusHistory = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => (float) abs($transaction->amount),
                'currency_code' => $transaction->currency_code,
                'description' => $transaction->description,
                'order_id' => $transaction->order_id,
                'created_at' => $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : null,
            ];
        })->toArray();

        $data = [
            'points_balance' => (float) round($pointsBalance, 0, PHP_ROUND_HALF_DOWN ),
            'balance' => (float) round($totalBalance, 0, PHP_ROUND_HALF_DOWN ),
            'spent_sum' => (float) $spentSum,
            'orders_count' => (int) $ordersCount,
            'remaining' => (int) $remaining,
            'percent_max' => (float) $maxUsagePercent,
            'show_levels_info' => $showLevelsInfo,
            'level' => $currentLevel ? $currentLevel->id : null,
            'type' => $calculationType,
            'levels_info' => $levelsInfo,
            'next_level_info' => $nextLevelInfo,
            'bonus_history' => $bonusHistory,
        ];

        return response()->json($data);
    }

    /**
     * Format level description based on calculation type.
     *
     * @param  \Webkul\Bonus\Models\BonusLevel  $level
     * @param  string  $calculationType
     * @param  \Illuminate\Support\Collection  $allLevels
     * @return string
     */
    protected function formatLevelDescription(BonusLevel $level, string $calculationType, $allLevels): string
    {
        $threshold = (float) $level->threshold_value;

        if ($calculationType === BonusLevel::CALCULATION_TYPE_ORDERS_COUNT) {
            // Find next level threshold
            $nextThreshold = null;
            foreach ($allLevels as $l) {
                if ($l->threshold_value > $threshold) {
                    $nextThreshold = $l->threshold_value;
                    break;
                }
            }

            if ($nextThreshold !== null) {
                return (int) $threshold . '-' . (int) $nextThreshold . ' заказов';
            } else {
                return (int) $threshold . '+ заказов';
            }
        } else {
            // For total_spent, show threshold
            return 'от ' . core()->formatPrice($threshold);
        }
    }

    /**
     * Get correct declension of "заказ" word based on number.
     *
     * @param  int  $number
     * @return string
     */
    protected function getOrderDeclension(int $number): string
    {
        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;

        // Special case for 11-14
        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
            return 'заказов';
        }

        // Cases for 1, 21, 31, etc.
        if ($lastDigit === 1) {
            return 'заказ';
        }

        // Cases for 2, 3, 4, 22, 23, 24, etc.
        if ($lastDigit >= 2 && $lastDigit <= 4) {
            return 'заказа';
        }

        // All other cases: 5, 6, 7, 8, 9, 0, 10, 11-14 (already handled), 15-20, etc.
        return 'заказов';
    }
}
