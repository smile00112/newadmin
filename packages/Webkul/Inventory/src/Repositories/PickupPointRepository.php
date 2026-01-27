<?php

namespace Webkul\Inventory\Repositories;

use Webkul\Core\Eloquent\Repository;

class PickupPointRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Webkul\Inventory\Contracts\PickupPoint';
    }
}
