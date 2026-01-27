<?php

namespace Webkul\IikoIntegration\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;
use Webkul\IikoIntegration\Models\IikoTerminalGroup;

class IikoTerminalGroupRepository extends Repository
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
        return IikoTerminalGroup::class;
    }

    /**
     * Find terminal groups by organization ID.
     */
    public function findByOrganizationId(string $organizationId)
    {
        return $this->findWhere(['organization_id' => $organizationId]);
    }

    /**
     * Find terminal group by iiko ID.
     */
    public function findByIikoId(string $iikoId): ?IikoTerminalGroup
    {
        return $this->findWhere(['iiko_id' => $iikoId])->first();
    }

    /**
     * Create or update terminal group.
     */
    public function createOrUpdate(array $data, string $organizationId, string $iikoId): IikoTerminalGroup
    {
        return $this->updateOrCreate(
            [
                'organization_id' => $organizationId,
                'iiko_id'         => $iikoId,
            ],
            $data
        );
    }
}
