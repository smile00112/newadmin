<?php

namespace App\Repositories;

use Illuminate\Container\Container;
use Webkul\Bonus\Models\BonusHistory;
use Webkul\Core\Eloquent\Repository;

class BonusHistoryRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return BonusHistory::class;
    }

    /**
     * Get active bonuses for customer.
     *
     * @param  int  $customerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveBonuses(int $customerId)
    {
        return $this->model
            ->where('customer_id', $customerId)
            ->active()
            ->get();
    }

    /**
     * Get expired bonuses for customer.
     *
     * @param  int  $customerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExpiredBonuses(int $customerId)
    {
        return $this->model
            ->where('customer_id', $customerId)
            ->expired()
            ->get();
    }

    /**
     * Get available bonus balance for customer.
     *
     * @param  int  $customerId
     * @return float
     */
    public function getAvailableBalance(int $customerId): float
    {
        return (float) $this->model
            ->where('customer_id', $customerId)
            ->active()
            ->sum('amount');
    }
}
