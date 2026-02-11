<?php

namespace Webkul\Bonus\Services;

use Illuminate\Support\Facades\DB;
use Webkul\Bonus\Models\BonusLevel;
use Webkul\Bonus\Models\BonusTransaction;
use Webkul\Bonus\Repositories\BonusLevelRepository;
use Webkul\Bonus\Repositories\BonusTransactionRepository;
use Webkul\Bonus\Repositories\CustomerBonusRepository;
use Webkul\Checkout\Models\Cart;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;

class BonusService
{
    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(
        protected BonusLevelRepository $bonusLevelRepository,
        protected CustomerBonusRepository $customerBonusRepository,
        protected BonusTransactionRepository $bonusTransactionRepository
    ) {}

    /**
     * Read bonus setting from core_config (SystemConfig).
     */
    protected function config(string $key, mixed $default = null, ?string $channelCode = null): mixed
    {
        // Settings are defined in packages/Webkul/Bonus/src/Config/system.php under bonus.general.settings.*
        $value = core()->getConfigData("bonus.general.settings.{$key}", $channelCode);

        return $value ?? $default;
    }

    /**
     * Normalize multiselect/text settings value into array of integers.
     */
    protected function normalizeIdList(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter(array_map('intval', $value)));
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            // JSON array stored by admin config sometimes
            if (str_starts_with($trimmed, '[')) {
                $decoded = json_decode($trimmed, true);

                if (is_array($decoded)) {
                    return array_values(array_filter(array_map('intval', $decoded)));
                }
            }

            // Comma-separated fallback
            return array_values(array_filter(array_map('intval', array_map('trim', explode(',', $trimmed)))));
        }

        return [];
    }

    /**
     * Check if bonus system is enabled.
     *
     * @param  string|null  $channelCode
     * @return bool
     */
    public function isEnabled(?string $channelCode = null): bool
    {
        return (bool) $this->config('enabled', false, $channelCode);
    }

    /**
     * Calculate customer bonus level based on calculation type.
     *
     * @param  \Webkul\Customer\Models\Customer  $customer
     * @param  string  $calculationType
     * @param  float|null  $cartValue
     * @return \Webkul\Bonus\Models\BonusLevel|null
     */
    public function calculateCustomerLevel(Customer $customer, string $calculationType, ?float $cartValue = null): ?BonusLevel
    {
        if (! $this->isEnabled()) {
            return null;
        }

        // Get all active levels - calculation_type is now a system setting, not per level
        $levels = $this->bonusLevelRepository->getActiveLevels();

        if ($levels->isEmpty()) {
            return null;
        }

        $value = match ($calculationType) {
            BonusLevel::CALCULATION_TYPE_ORDERS_COUNT => $this->getCustomerOrdersCount($customer),
            BonusLevel::CALCULATION_TYPE_TOTAL_SPENT => $this->getCustomerTotalSpent($customer),
            BonusLevel::CALCULATION_TYPE_CART_VALUE => $cartValue ?? 0,
            default => 0,
        };

        $applicableLevel = null;

        foreach ($levels as $level) {
            if ($value >= $level->threshold_value) {
                if (! $applicableLevel || $level->threshold_value > $applicableLevel->threshold_value) {
                    $applicableLevel = $level;
                }
            }
        }

        return $applicableLevel;
    }

    /**
     * Get customer completed orders count.
     *
     * @param  \Webkul\Customer\Models\Customer  $customer
     * @return int
     */
    protected function getCustomerOrdersCount(Customer $customer): int
    {
        return $customer->orders()
            ->where('status', Order::STATUS_COMPLETED)
            ->count();
    }

    /**
     * Get customer total spent amount.
     *
     * @param  \Webkul\Customer\Models\Customer  $customer
     * @return float
     */
    protected function getCustomerTotalSpent(Customer $customer): float
    {
        return (float) $customer->orders()
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('base_grand_total');
    }

    /**
     * Calculate cashback amount for order.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @return float
     */
    public function calculateCashbackAmount(Order $order): float
    {
        if (! $this->isEnabled()) {
            return 0;
        }

        if (! $order->customer_id || $order->is_guest) {
            return 0;
        }

        $customer = $order->customer;

        if (! $customer) {
            return 0;
        }

        // Get order total excluding excluded products
        $orderTotal = $this->getOrderTotalForCashback($order);

        if ($orderTotal <= 0) {
            return 0;
        }

        // Use a single calculation type chosen in settings
        $calculationType = (string) $this->config('calculation_type', BonusLevel::CALCULATION_TYPE_TOTAL_SPENT);

        $level = $this->calculateCustomerLevel(
            $customer,
            $calculationType,
            $calculationType === BonusLevel::CALCULATION_TYPE_CART_VALUE ? (float) $order->base_grand_total : null
        );

        if (! $level) {
            return 0;
        }

        return round($orderTotal * (((float) $level->cashback_percent) / 100), 4);
    }

    /**
     * Get order total for cashback calculation (excluding excluded products).
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @return float
     */
    protected function getOrderTotalForCashback(Order $order): float
    {
        $excludedProductIds = $this->normalizeIdList($this->config('excluded_product_ids', []));
        $participatingProductIds = $this->normalizeIdList($this->config('participating_product_ids', []));

        $total = 0;

        foreach ($order->items as $item) {
            $productId = $item->product_id;

            // Skip if product is excluded
            if (! empty($excludedProductIds) && in_array($productId, $excludedProductIds)) {
                continue;
            }

            // Skip if participating list exists and product is not in it
            if (! empty($participatingProductIds) && ! in_array($productId, $participatingProductIds)) {
                continue;
            }

            $total += $item->base_total;
        }

        return (float) $total;
    }

    /**
     * Accrue bonuses for completed order.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @return void
     */
    public function accrueBonuses(Order $order): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        if (! $order->customer_id || $order->is_guest) {
            return;
        }

        // Check if bonuses already accrued
        if ($order->base_bonus_amount_accrued > 0) {
            return;
        }

        $amount = $this->calculateCashbackAmount($order);

        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($order, $amount) {
            $currencyCode = $order->order_currency_code ?? core()->getCurrentCurrencyCode();
            $expiryDays = (int) $this->config('expiry_days', 365);
            $expiresAt = $expiryDays > 0 ? now()->addDays($expiryDays) : null;

            // Create transaction
            $this->bonusTransactionRepository->create([
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'type' => BonusTransaction::TYPE_ACCRUAL,
                'amount' => $amount,
                'currency_code' => $currencyCode,
                'description' => trans('bonus::app.transactions.accrual_description', [
                    'order_id' => $order->increment_id,
                ]),
                'expires_at' => $expiresAt,
            ]);

            // Update customer balance
            $this->customerBonusRepository->updateBalance(
                $order->customer_id,
                $amount,
                $currencyCode
            );

            // Update order
            $order->base_bonus_amount_accrued = $amount;
            $order->bonus_amount_accrued = core()->convertPrice($amount, $order->base_currency_code, $order->order_currency_code);
            $order->save();
        });
    }

    /**
     * Deduct bonuses when order is created.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @param  float  $bonusAmount
     * @return void
     */
    public function deductBonuses(Order $order, float $bonusAmount): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        if (! $order->customer_id || $order->is_guest) {
            return;
        }

        if ($bonusAmount <= 0) {
            return;
        }

        $currencyCode = $order->order_currency_code ?? core()->getCurrentCurrencyCode();
        $availableBalance = $this->getAvailableBonuses($order->customer_id, $currencyCode);

        if ($availableBalance < $bonusAmount) {
            throw new \Exception(trans('bonus::app.errors.insufficient_balance'));
        }

        DB::transaction(function () use ($order, $bonusAmount, $currencyCode) {
            // Get available bonuses (FIFO - oldest first)
            $availableTransactions = $this->bonusTransactionRepository->model
                ->where('customer_id', $order->customer_id)
                ->where('currency_code', $currencyCode)
                ->where('type', BonusTransaction::TYPE_ACCRUAL)
                ->notExpired()
                ->where('amount', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            $remainingAmount = $bonusAmount;

            foreach ($availableTransactions as $transaction) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $deductAmount = min($transaction->amount, $remainingAmount);

                // Create deduction transaction
                $this->bonusTransactionRepository->create([
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'type' => BonusTransaction::TYPE_DEDUCTION,
                    'amount' => -$deductAmount,
                    'currency_code' => $currencyCode,
                    'description' => trans('bonus::app.transactions.deduction_description', [
                        'order_id' => $order->increment_id,
                    ]),
                ]);

                // Update transaction amount (reduce available balance)
                $transaction->amount -= $deductAmount;
                $transaction->save();

                $remainingAmount -= $deductAmount;
            }

            // Update customer balance
            $this->customerBonusRepository->updateBalance(
                $order->customer_id,
                -$bonusAmount,
                $currencyCode
            );

            // Update order
            $order->base_bonus_amount_used = $bonusAmount;
            $order->bonus_amount_used = core()->convertPrice($bonusAmount, $order->base_currency_code, $order->order_currency_code);
            $order->save();
        });
    }

    /**
     * Return bonuses when order is cancelled.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @return void
     */
    public function returnBonuses(Order $order): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        if (! $order->customer_id || $order->is_guest) {
            return;
        }

        $currencyCode = $order->order_currency_code ?? core()->getCurrentCurrencyCode();
        $returnAmount = $order->base_bonus_amount_used ?? 0;

        if ($returnAmount <= 0) {
            return;
        }

        DB::transaction(function () use ($order, $returnAmount, $currencyCode) {
            // Create return transaction
            $this->bonusTransactionRepository->create([
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'type' => BonusTransaction::TYPE_RETURN,
                'amount' => $returnAmount,
                'currency_code' => $currencyCode,
                'description' => trans('bonus::app.transactions.return_description', [
                    'order_id' => $order->increment_id,
                ]),
            ]);

            // Update customer balance
            $this->customerBonusRepository->updateBalance(
                $order->customer_id,
                $returnAmount,
                $currencyCode
            );

            // Cancel accrual if exists - найти и обновить исходную транзакцию начисления
            if ($order->base_bonus_amount_accrued > 0) {
                $accruedAmount = $order->base_bonus_amount_accrued;
                
                // Найти транзакцию начисления для этого заказа
                $accrualTransaction = $this->bonusTransactionRepository->model
                    ->where('customer_id', $order->customer_id)
                    ->where('order_id', $order->id)
                    ->where('type', BonusTransaction::TYPE_ACCRUAL)
                    ->where('amount', '>', 0)
                    ->first();
                
                if ($accrualTransaction) {
                    // Уменьшить amount исходной транзакции
                    $accrualTransaction->amount -= $accruedAmount;
                    $accrualTransaction->save();
                }
                
                // Создать транзакцию списания для истории
                $this->bonusTransactionRepository->create([
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'type' => BonusTransaction::TYPE_DEDUCTION,
                    'amount' => -$accruedAmount,
                    'currency_code' => $currencyCode,
                    'description' => trans('bonus::app.transactions.cancel_accrual_description', [
                        'order_id' => $order->increment_id,
                    ]),
                ]);

                // Обновить баланс клиента
                $this->customerBonusRepository->updateBalance(
                    $order->customer_id,
                    -$accruedAmount,
                    $currencyCode
                );
                
                // Обнулить начисленные бонусы в заказе
                $order->base_bonus_amount_accrued = 0;
                $order->bonus_amount_accrued = 0;
                $order->save();
            }
        });
    }

    /**
     * Get available bonuses for customer.
     *
     * @param  int  $customerId
     * @param  string|null  $currencyCode
     * @return float
     */
    public function getAvailableBonuses(int $customerId, ?string $currencyCode = null): float
    {
        if (! $this->isEnabled()) {
            return 0;
        }

        $currencyCode = $currencyCode ?? core()->getCurrentCurrencyCode();

        return $this->bonusTransactionRepository->getAvailableBonuses($customerId, $currencyCode);
    }

    /**
     * Get total bonus balance for customer (including expired).
     *
     * @param  int  $customerId
     * @param  string|null  $currencyCode
     * @return float
     */
    public function getTotalBalance(int $customerId, ?string $currencyCode = null): float
    {
        $currencyCode = $currencyCode ?? core()->getCurrentCurrencyCode();

        return $this->customerBonusRepository->getBalance($customerId, $currencyCode);
    }

    /**
     * Get maximum bonus amount that can be used for order.
     *
     * @param  \Webkul\Sales\Models\Order|\Webkul\Checkout\Models\Cart  $orderOrCart
     * @param  int  $customerId
     * @return float
     */
    public function getMaxUsableBonuses($orderOrCart, int $customerId): float
    {
        if (! $this->isEnabled()) {
            return 0;
        }

        $grandTotal = $orderOrCart->base_grand_total ?? $orderOrCart->grand_total ?? 0;
        $currencyCode = $orderOrCart->order_currency_code ?? $orderOrCart->cart_currency_code ?? core()->getCurrentCurrencyCode();
        $maxPercent = (float) $this->config('max_usage_percent', 100);
        $availableBalance = $this->getAvailableBonuses($customerId, $currencyCode);

        $maxByPercent = $grandTotal * ($maxPercent / 100);
        $maxByBalance = $availableBalance;

        return min($maxByPercent, $maxByBalance, $grandTotal);
    }

    /**
     * Manually accrue bonuses to customer (for admin panel).
     *
     * @param  int  $customerId
     * @param  float  $amount
     * @param  string|null  $description
     * @param  string|null  $currencyCode
     * @return \Webkul\Bonus\Models\BonusTransaction
     */
    public function manuallyAccrueBonuses(int $customerId, float $amount, ?string $description = null, ?string $currencyCode = null)
    {
        if (! $this->isEnabled()) {
            throw new \Exception('Bonus system is disabled');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Bonus amount must be greater than 0');
        }

        $currencyCode = $currencyCode ?? core()->getCurrentCurrencyCode();
        $expiryDays = (int) $this->config('expiry_days', 365);
        $expiresAt = $expiryDays > 0 ? now()->addDays($expiryDays) : null;

        return DB::transaction(function () use ($customerId, $amount, $description, $currencyCode, $expiresAt) {
            // Create transaction
            $transaction = $this->bonusTransactionRepository->create([
                'customer_id' => $customerId,
                'order_id' => null,
                'type' => BonusTransaction::TYPE_ACCRUAL,
                'amount' => $amount,
                'currency_code' => $currencyCode,
                'description' => $description ?? trans('bonus::app.transactions.manual_accrual_description', [], 'ru') ?? 'Ручное начисление бонусов администратором',
                'expires_at' => $expiresAt,
            ]);

            // Update customer balance
            $this->customerBonusRepository->updateBalance(
                $customerId,
                $amount,
                $currencyCode
            );

            return $transaction;
        });
    }

    /**
     * Manually deduct bonuses from customer (for admin panel).
     *
     * @param  int  $customerId
     * @param  float  $amount
     * @param  string|null  $description
     * @param  string|null  $currencyCode
     * @return void
     */
    public function manuallyDeductBonuses(int $customerId, float $amount, ?string $description = null, ?string $currencyCode = null): void
    {
        if (! $this->isEnabled()) {
            throw new \Exception('Bonus system is disabled');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Deduction amount must be greater than 0');
        }

        $currencyCode = $currencyCode ?? core()->getCurrentCurrencyCode();
        $availableBalance = $this->getAvailableBonuses($customerId, $currencyCode);

        if ($availableBalance < $amount) {
            throw new \Exception(trans('bonus::app.errors.insufficient_balance', [], 'ru') ?? 'Insufficient bonus balance');
        }

        DB::transaction(function () use ($customerId, $amount, $description, $currencyCode) {
            // Get available bonuses (FIFO - oldest first)
            $availableTransactions = $this->bonusTransactionRepository->model
                ->where('customer_id', $customerId)
                ->where('currency_code', $currencyCode)
                ->where('type', BonusTransaction::TYPE_ACCRUAL)
                ->notExpired()
                ->where('amount', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            $remainingAmount = $amount;

            foreach ($availableTransactions as $transaction) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $deductAmount = min($transaction->amount, $remainingAmount);

                // Create deduction transaction
                $this->bonusTransactionRepository->create([
                    'customer_id' => $customerId,
                    'order_id' => null,
                    'type' => BonusTransaction::TYPE_DEDUCTION,
                    'amount' => -$deductAmount,
                    'currency_code' => $currencyCode,
                    'description' => $description ?? trans('bonus::app.transactions.manual_deduction_description', [], 'ru') ?? 'Ручное списание бонусов администратором',
                ]);

                // Update transaction amount (reduce available balance)
                $transaction->amount -= $deductAmount;
                $transaction->save();

                $remainingAmount -= $deductAmount;
            }

            // Update customer balance
            $this->customerBonusRepository->updateBalance(
                $customerId,
                -$amount,
                $currencyCode
            );
        });
    }

    /**
     * Update customer bonus level based on current statistics.
     *
     * @param  \Webkul\Customer\Models\Customer  $customer
     * @return void
     */
    public function updateCustomerLevel(Customer $customer): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $calculationType = (string) $this->config('calculation_type', BonusLevel::CALCULATION_TYPE_TOTAL_SPENT);
        $level = $this->calculateCustomerLevel($customer, $calculationType);

        $customer->update([
            'bonus_level_id' => $level?->id,
        ]);
    }

    /**
     * Expire bonuses that have reached their expiration date.
     *
     * @return int Number of expired bonus transactions
     */
    public function expireBonuses(): int
    {
        if (! $this->isEnabled()) {
            return 0;
        }

        $expiredTransactions = $this->bonusTransactionRepository->model
            ->where('type', BonusTransaction::TYPE_ACCRUAL)
            ->where('amount', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $expiredCount = 0;

        foreach ($expiredTransactions as $transaction) {
            // Only expire if transaction hasn't been fully used
            if ($transaction->amount > 0) {
                $currencyCode = $transaction->currency_code ?? core()->getCurrentCurrencyCode();
                
                DB::transaction(function () use ($transaction, $currencyCode, &$expiredCount) {
                    // Create expiration deduction transaction
                    $this->bonusTransactionRepository->create([
                        'customer_id' => $transaction->customer_id,
                        'order_id' => $transaction->order_id,
                        'type' => BonusTransaction::TYPE_DEDUCTION,
                        'amount' => -$transaction->amount,
                        'currency_code' => $currencyCode,
                        'description' => trans('bonus::app.transactions.expiration_description', [], 'ru') ?? 'Истечение срока действия бонусов',
                    ]);

                    // Update customer balance
                    $this->customerBonusRepository->updateBalance(
                        $transaction->customer_id,
                        -$transaction->amount,
                        $currencyCode
                    );

                    // Mark transaction as expired by setting amount to 0
                    $transaction->amount = 0;
                    $transaction->save();

                    $expiredCount++;
                });
            }
        }

        return $expiredCount;
    }
}
