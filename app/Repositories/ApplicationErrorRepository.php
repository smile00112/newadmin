<?php

namespace App\Repositories;

use App\Models\ApplicationError;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Webkul\Core\Eloquent\Repository;

class ApplicationErrorRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
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
        return ApplicationError::class;
    }

    /**
     * Get recent application errors.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecent(int $limit = 20): Collection
    {
        return $this->model
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['id', 'message', 'code', 'source', 'created_at']);
    }

    /**
     * Paginate application errors.
     *
     * @param  int|null  $limit
     * @param  array  $columns
     * @param  string  $method
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($limit = null, $columns = ['*'], $method = 'paginate')
    {
        $this->model = $this->model->orderBy('created_at', 'desc');

        return parent::paginate($limit, $columns, $method);
    }
}
