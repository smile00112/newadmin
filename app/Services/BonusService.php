<?php

namespace App\Services;

use App\Repositories\BonusHistoryRepository;
use App\Repositories\BonusLevelRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\Bonus\Models\BonusHistory;
use Webkul\Customer\Models\CustomerProxy;

class BonusService
{
    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(
        protected BonusLevelRepository $bonusLevelRepository,
        protected BonusHistoryRepository $bonusHistoryRepository
    ) {}

    /**
     * Calculate customer level based on calculation type.
     *
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @param  string  $calculationType
     * @return \Webkul\Bonus\Models\BonusLevel|null
     */
    public function calculateCustomerLevel($customer, string $calculationType): ?\Webkul\Bonus\Models\BonusLevel
    {
        return $this->bonusLevelRepository->getLevelForCustomer($customer, $calculationType);
    }

    /**
     * Update customer level.
     *
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @return void
     */
    public function updateCustomerLevel($customer): void
    {
        $calculationType = core()->getConfigData('bonus_system.general.calculation_type') ?? 'orders';

        if (! core()->getConfigData('bonus_system.general.enabled')) {
            return;
        }

        $level = $this->calculateCustomerLevel($customer, $calculationType);

        $customer->update([
            'bonus_level_id' => $level?->id,
        ]);
    }

    /**
     * Calculate cashback for order.
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @return float
     */
    public function calculateCashback($order, $customer): float
    {
        if (! core()->getConfigData('bonus_system.general.enabled')) {
            return 0;
        }

        $level = $customer->bonusLevel;

        if (! $level || ! $level->cashback_percent) {
            return 0;
        }

        // Get order items that participate in bonus system
        $orderItems = $this->getBonusEligibleItems($order);

        $totalAmount = 0;
        foreach ($orderItems as $item) {
            $totalAmount += $item->base_total;
        }

        $cashback = ($totalAmount * $level->cashback_percent) / 100;

        return round($cashback, 4);
    }

    /**
     * Get bonus eligible items from order.
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return \Illuminate\Support\Collection
     */
    protected function getBonusEligibleItems($order)
    {
        $includedProducts = core()->getConfigData('bonus_system.general.included_products');
        $excludedProducts = core()->getConfigData('bonus_system.general.excluded_products');

        $items = $order->items;

        // If included products list is not empty, filter by it
        if (! empty($includedProducts)) {
            $includedIds = is_array($includedProducts) ? $includedProducts : explode(',', $includedProducts);
            $items = $items->whereIn('product_id', $includedIds);
        }

        // Exclude products from excluded list
        if (! empty($excludedProducts)) {
            $excludedIds = is_array($excludedProducts) ? $excludedProducts : explode(',', $excludedProducts);
            $items = $items->whereNotIn('product_id', $excludedIds);
        }

        return $items;
    }

    /**
     * Accrue bonus to customer.
     *
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @param  float  $amount
     * @param  \Webkul\Sales\Contracts\Order|null  $order
     * @param  \Carbon\Carbon|null  $expiresAt
     * @return BonusHistory
     */
    public function accrueBonus($customer, float $amount, $order = null, ?Carbon $expiresAt = null): BonusHistory
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Bonus amount must be greater than 0');
        }

        return DB::transaction(function () use ($customer, $amount, $order, $expiresAt) {
            // Calculate expiration date if not provided
            if (! $expiresAt) {
                $expirationDays = (int) (core()->getConfigData('bonus_system.general.bonus_expiration_days') ?? 0);
                $expiresAt = $expirationDays > 0 ? now()->addDays($expirationDays) : null;
            }

            // Get current balance
            $currentBalance = $this->getAvailableBonusBalance($customer);

            // Update customer balance
            $customer->increment('bonus_balance', $amount);

            // Create history record
            $history = $this->bonusHistoryRepository->create([
                'customer_id' => $customer->id,
                'order_id' => $order?->id,
                'type' => 'accrual',
                'amount' => $amount,
                'base_amount' => $amount,
                'balance_after' => $currentBalance + $amount,
                'expires_at' => $expiresAt,
                'description' => $order
                    ? "Начисление бонусов за заказ #{$order->increment_id}"
                    : 'Начисление бонусов',
            ]);

            return $history;
        });
    }

    /**
     * Deduct bonus from customer.
     *
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @param  float  $amount
     * @param  \Webkul\Sales\Contracts\Order|null  $order
     * @return array Array of BonusHistory records
     */
    public function deductBonus($customer, float $amount, $order = null): array
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Bonus amount must be greater than 0');
        }

        $availableBalance = $this->getAvailableBonusBalance($customer);

        if ($availableBalance < $amount) {
            throw new \RuntimeException('Insufficient bonus balance');
        }

        return DB::transaction(function () use ($customer, $amount, $order) {
            $deducted = 0;
            $historyRecords = [];

            // Get active bonuses ordered by expiration date (FIFO - first expired first)
            $activeBonuses = $this->bonusHistoryRepository->getActiveBonuses($customer->id)
                ->sortBy('expires_at');

            foreach ($activeBonuses as $bonus) {
                if ($deducted >= $amount) {
                    break;
                }

                $remaining = $amount - $deducted;
                $deductAmount = min($bonus->amount, $remaining);

                // Update customer balance
                $customer->decrement('bonus_balance', $deductAmount);

                // Create deduction history
                $currentBalance = $this->getAvailableBonusBalance($customer);

                $history = $this->bonusHistoryRepository->create([
                    'customer_id' => $customer->id,
                    'order_id' => $order?->id,
                    'type' => 'deduction',
                    'amount' => -$deductAmount,
                    'base_amount' => -$deductAmount,
                    'balance_after' => $currentBalance - $deductAmount,
                    'expires_at' => null,
                    'description' => $order
                        ? "Списание бонусов за заказ #{$order->increment_id}"
                        : 'Списание бонусов',
                ]);

                $historyRecords[] = $history;
                $deducted += $deductAmount;
            }

            return $historyRecords;
        });
    }

    /**
     * Refund bonus to customer.
     *
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @param  float  $amount
     * @param  \Webkul\Sales\Contracts\Order|null  $order
     * @return BonusHistory
     */
    public function refundBonus($customer, float $amount, $order = null): BonusHistory
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Bonus amount must be greater than 0');
        }

        return DB::transaction(function () use ($customer, $amount, $order) {
            // Calculate expiration date
            $expirationDays = (int) (core()->getConfigData('bonus_system.general.bonus_expiration_days') ?? 0);
            $expiresAt = $expirationDays > 0 ? now()->addDays($expirationDays) : null;

            // Get current balance
            $currentBalance = $this->getAvailableBonusBalance($customer);

            // Update customer balance
            $customer->increment('bonus_balance', $amount);

            // Create history record
            $history = $this->bonusHistoryRepository->create([
                'customer_id' => $customer->id,
                'order_id' => $order?->id,
                'type' => 'refund',
                'amount' => $amount,
                'base_amount' => $amount,
                'balance_after' => $currentBalance + $amount,
                'expires_at' => $expiresAt,
                'description' => $order
                    ? "Возврат бонусов за заказ #{$order->increment_id}"
                    : 'Возврат бонусов',
            ]);

            return $history;
        });
    }

    /**
     * Expire bonuses (for command).
     *
     * @return int Number of expired bonuses
     */
    public function expireBonuses(): int
    {
        $expiredBonuses = $this->bonusHistoryRepository->model
            ->expired()
            ->where('type', 'accrual')
            ->get();

        $expiredCount = 0;

        foreach ($expiredBonuses as $bonus) {
            $customer = CustomerProxy::find($bonus->customer_id);

            if (! $customer) {
                continue;
            }

            DB::transaction(function () use ($customer, $bonus, &$expiredCount) {
                // Only expire if bonus hasn't been used
                if ($bonus->amount > 0) {
                    // Update customer balance
                    $customer->decrement('bonus_balance', $bonus->amount);

                    // Create expiration history
                    $this->bonusHistoryRepository->create([
                        'customer_id' => $customer->id,
                        'order_id' => $bonus->order_id,
                        'type' => 'expiration',
                        'amount' => -$bonus->amount,
                        'base_amount' => -$bonus->base_amount,
                        'balance_after' => $this->getAvailableBonusBalance($customer),
                        'expires_at' => null,
                        'description' => "Истечение срока действия бонусов",
                    ]);

                    $expiredCount++;
                }
            });
        }

        return $expiredCount;
    }

    /**
     * Get available bonus balance for customer.
     *
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @return float
     */
    public function getAvailableBonusBalance($customer): float
    {
        return $this->bonusHistoryRepository->getAvailableBalance($customer->id);
    }
}
