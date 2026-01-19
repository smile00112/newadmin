<?php

namespace Webkul\IikoIntegration\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;
use Webkul\IikoIntegration\Models\IikoOrganization;

class IikoOrganizationRepository extends Repository
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
        return IikoOrganization::class;
    }

    /**
     * Find organization by iiko ID.
     */
    public function findByIikoId(string $iikoId): ?IikoOrganization
    {
        return $this->findWhere(['iiko_id' => $iikoId])->first();
    }

    /**
     * Create or update organization.
     */
    public function createOrUpdate(array $data, string $iikoId): IikoOrganization
    {
        return $this->updateOrCreate(
            ['iiko_id' => $iikoId],
            $data
        );
    }
}
