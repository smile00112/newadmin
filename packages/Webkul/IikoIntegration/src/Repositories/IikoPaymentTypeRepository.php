<?php

namespace Webkul\IikoIntegration\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;
use Webkul\IikoIntegration\Models\IikoPaymentType;

class IikoPaymentTypeRepository extends Repository
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
        return IikoPaymentType::class;
    }

    /**
     * Get payment types by organization ID.
     */
    public function getByOrganizationId(string $organizationId)
    {
        return $this->findWhere(['organization_id' => $organizationId]);
    }

    /**
     * Find payment type by iiko ID and organization ID.
     */
    public function findByIikoId(string $organizationId, string $iikoId): ?IikoPaymentType
    {
        return $this->findWhere([
            'organization_id' => $organizationId,
            'iiko_id' => $iikoId,
        ])->first();
    }

    /**
     * Create or update payment type.
     */
    public function createOrUpdate(array $data, string $organizationId, string $iikoId): IikoPaymentType
    {
        return $this->updateOrCreate(
            [
                'organization_id' => $organizationId,
                'iiko_id' => $iikoId,
            ],
            $data
        );
    }
}
