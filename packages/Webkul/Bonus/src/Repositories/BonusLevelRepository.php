<?php

namespace Webkul\Bonus\Repositories;

use Webkul\Core\Eloquent\Repository;

class BonusLevelRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return string
     */
    public function model(): string
    {
        return \Webkul\Bonus\Models\BonusLevel::class;
    }

    /**
     * Get active levels ordered by sort order.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveLevels()
    {
        return $this->model
            ->active()
            ->ordered()
            ->get();
    }
}
