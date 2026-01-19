<?php

namespace Webkul\IikoIntegration\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;
use Webkul\IikoIntegration\Models\IikoOrderSync;

class IikoOrderSyncRepository extends Repository
{
    /**
     * Create a new repository instance.
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
        return IikoOrderSync::class;
    }

    /**
     * Find sync by order ID.
     */
    public function findByOrderId(int $orderId): ?IikoOrderSync
    {
        return $this->findWhere(['order_id' => $orderId])->first();
    }

    /**
     * Find sync by iiko order ID.
     */
    public function findByIikoOrderId(string $iikoOrderId): ?IikoOrderSync
    {
        return $this->findWhere(['iiko_order_id' => $iikoOrderId])->first();
    }

    /**
     * Create or update sync record.
     */
    public function createOrUpdate(array $data, int $orderId): IikoOrderSync
    {
        return $this->updateOrCreate(
            ['order_id' => $orderId],
            $data
        );
    }
}
