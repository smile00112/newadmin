<?php

namespace Webkul\Newsletters\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Newsletters\Traits\BelongsToCompany;

class AccountWarmingRepository extends Repository
{
    use BelongsToCompany;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'Webkul\Newsletters\Models\AccountWarming';
    }

    /**
     * Retrieve all data of repository, automatically filtered by company.
     *
     * @param array $columns
     * @return mixed
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
     *
     * @param  int  $id
     * @param  array  $columns
     * @return mixed
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
     *
     * @param  int  $id
     * @param  array  $columns
     * @return mixed
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
     *
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        $companyId = $this->getCurrentCompanyId();
        if ($companyId !== null && !isset($attributes['company_id'])) {
            $attributes['company_id'] = $companyId;
        }

        $model = $this->model->newInstance($attributes);
        $model->save();

        return $this->parserResult($model);
    }

    /**
     * Update a entity in repository by id, automatically filtered by company.
     *
     * @param array $attributes
     * @param $id
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        $this->applyScope();

        $companyId = $this->getCurrentCompanyId();
        if ($companyId !== null) {
            $this->model = $this->model->where('company_id', $companyId);
        }

        $model = $this->model->findOrFail($id);
        $model->fill($attributes);
        $model->save();

        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Delete a entity in repository by id, automatically filtered by company.
     *
     * @param $id
     * @return int
     */
    public function delete($id)
    {
        $this->applyScope();

        $companyId = $this->getCurrentCompanyId();
        if ($companyId !== null) {
            $this->model = $this->model->where('company_id', $companyId);
        }

        $model = $this->findOrFail($id);
        $this->resetModel();

        return $model->delete();
    }

    /**
     * Get active account warmings.
     *
     * @return mixed
     */
    public function getActive()
    {
        $this->applyCriteria();
        $this->applyScope();

        $companyId = $this->getCurrentCompanyId();
        if ($companyId !== null) {
            $this->model = $this->model->where('company_id', $companyId);
        }

        $result = $this->model->where('active', true)->get();
        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($result);
    }
}


