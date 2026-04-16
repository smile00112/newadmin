<?php

namespace Webkul\Bonus\Repositories;

use Webkul\Core\Eloquent\Repository;

class BonusTransactionRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return string
     */
    public function model(): string
    {
        return 'Webkul\Bonus\Contracts\BonusTransaction';
    }

    /**
     * Get customer transactions.
     *
     * @param  int  $customerId
     * @param  array  $filters
     * @return \Illuminate\Support\Collection
     */
    public function getCustomerTransactions(int $customerId, array $filters = [])
    {
        $query = $this->model->where('customer_id', $customerId);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get available bonuses for customer — returns actual balance from customer_bonuses table.
     *
     * @param  int  $customerId
     * @param  string|null  $currencyCode
     * @return float
     */
    public function getAvailableBonuses(int $customerId, ?string $currencyCode = null): float
    {
        $bonus = \Webkul\Bonus\Models\CustomerBonus::where('customer_id', $customerId)->first();

        return $bonus ? (float) $bonus->balance : 0.0;
    }
}
