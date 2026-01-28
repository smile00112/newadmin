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
        return 'Webkul\Bonus\Contracts\BonusLevel';
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

    /**
     * Get levels by calculation type.
     *
     * @param  string  $type
     * @return \Illuminate\Support\Collection
     */
    public function getLevelsByCalculationType(string $type)
    {
        return $this->model
            ->where('calculation_type', $type)
            ->active()
            ->ordered()
            ->get();
    }
}
