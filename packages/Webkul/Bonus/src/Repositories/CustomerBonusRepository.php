<?php

namespace Webkul\Bonus\Repositories;

use Webkul\Core\Eloquent\Repository;

class CustomerBonusRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return string
     */
    public function model(): string
    {
        return 'Webkul\Bonus\Contracts\CustomerBonus';
    }

    /**
     * Get or create customer bonus balance.
     *
     * @param  int  $customerId
     * @param  string|null  $currencyCode
     * @return \Webkul\Bonus\Models\CustomerBonus
     */
    public function getOrCreateBalance(int $customerId, ?string $currencyCode = null)
    {
        $currencyCode = $currencyCode ?? core()->getCurrentCurrencyCode();

        return $this->model->firstOrCreate(
            [
                'customer_id' => $customerId,
                'currency_code' => $currencyCode,
            ],
            [
                'balance' => 0,
            ]
        );
    }

    /**
     * Get customer balance.
     *
     * @param  int  $customerId
     * @param  string|null  $currencyCode
     * @return float
     */
    public function getBalance(int $customerId, ?string $currencyCode = null): float
    {
        $currencyCode = $currencyCode ?? core()->getCurrentCurrencyCode();

        $bonus = $this->model
            ->where('customer_id', $customerId)
            ->where('currency_code', $currencyCode)
            ->first();

        return $bonus ? (float) $bonus->balance : 0;
    }

    /**
     * Update customer balance.
     *
     * @param  int  $customerId
     * @param  float  $amount
     * @param  string|null  $currencyCode
     * @return \Webkul\Bonus\Models\CustomerBonus
     */
    public function updateBalance(int $customerId, float $amount, ?string $currencyCode = null)
    {
        $currencyCode = $currencyCode ?? core()->getCurrentCurrencyCode();

        $bonus = $this->getOrCreateBalance($customerId, $currencyCode);
        $bonus->balance = round($bonus->balance + $amount);
        $bonus->save();

        return $bonus;
    }
}
