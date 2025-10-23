<?php

namespace Webkul\Newsletters\Repositories;

use Webkul\Core\Eloquent\Repository;

class StopListRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'Webkul\Newsletters\Models\StopList';
    }

    /**
     * Check if a phone number is blocked.
     */
    public function isBlocked(string $phoneNumber): bool
    {
        return $this->model::isBlocked($phoneNumber);
    }
}










