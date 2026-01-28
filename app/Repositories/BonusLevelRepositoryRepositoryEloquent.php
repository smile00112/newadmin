<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\BonusLevelRepositoryRepository;
use App\Entities\BonusLevelRepository;
use App\Validators\BonusLevelRepositoryValidator;

/**
 * Class BonusLevelRepositoryRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class BonusLevelRepositoryRepositoryEloquent extends BaseRepository implements BonusLevelRepositoryRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return BonusLevelRepository::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
