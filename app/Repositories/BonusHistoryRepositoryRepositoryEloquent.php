<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\BonusHistoryRepositoryRepository;
use App\Entities\BonusHistoryRepository;
use App\Validators\BonusHistoryRepositoryValidator;

/**
 * Class BonusHistoryRepositoryRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class BonusHistoryRepositoryRepositoryEloquent extends BaseRepository implements BonusHistoryRepositoryRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return BonusHistoryRepository::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
