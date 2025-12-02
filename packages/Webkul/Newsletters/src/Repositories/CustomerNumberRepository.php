<?php

namespace Webkul\Newsletters\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Newsletters\Traits\BelongsToCompany;

class CustomerNumberRepository extends Repository
{
    use BelongsToCompany;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'Webkul\Newsletters\Models\CustomerNumber';
    }

    /**
     * Retrieve all data of repository, automatically filtered by company.
     */
    public function all($columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $companyId = $this->getCurrentCompanyId();
        if ($companyId !== null) {
            $this->model = $this->model->where('company_id', $companyId);
        }

        $result = $this->model->get($columns);
        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($result);
    }

    /**
     * Find data by id, automatically filtered by company.
     */
    public function find($id, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $companyId = $this->getCurrentCompanyId();
        if ($companyId !== null) {
            $this->model = $this->model->where('company_id', $companyId);
        }

        $model = $this->model->find($id, $columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Find data by id, automatically filtered by company.
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $companyId = $this->getCurrentCompanyId();
        if ($companyId !== null) {
            $this->model = $this->model->where('company_id', $companyId);
        }

        $model = $this->model->findOrFail($id, $columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Create a new instance in repository, automatically set company_id.
     */
    public function create(array $attributes)
    {
        $companyId = $this->getCurrentCompanyId();
        if ($companyId !== null && !isset($attributes['company_id'])) {
            $attributes['company_id'] = $companyId;
        }

        $model = $this->makeModel();
        $model->fill($attributes);
        $model->save();

        $this->resetModel();

        return $this->parserResult($model);
    }
}










