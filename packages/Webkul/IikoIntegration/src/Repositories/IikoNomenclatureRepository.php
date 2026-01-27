<?php

namespace Webkul\IikoIntegration\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;
use Webkul\IikoIntegration\Models\IikoNomenclature;

class IikoNomenclatureRepository extends Repository
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
        return IikoNomenclature::class;
    }

    /**
     * Find nomenclature by organization ID.
     */
    public function findByOrganizationId(string $organizationId): ?IikoNomenclature
    {
        return $this->findWhere(['organization_id' => $organizationId])->first();
    }

    /**
     * Create or update nomenclature.
     */
    public function createOrUpdate(array $data, string $organizationId): IikoNomenclature
    {
        return $this->updateOrCreate(
            ['organization_id' => $organizationId],
            $data
        );
    }
}
