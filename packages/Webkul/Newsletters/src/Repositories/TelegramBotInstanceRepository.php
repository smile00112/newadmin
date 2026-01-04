<?php

namespace Webkul\Newsletters\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Newsletters\Traits\BelongsToCompany;

class TelegramBotInstanceRepository extends Repository
{
    use BelongsToCompany;

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'Webkul\Newsletters\Models\TelegramBotInstance';
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

    /**
     * Get available telegram instances for company.
     */
    public function getAvailableForCompany($companyId = null)
    {
        $companyId = $companyId ?? $this->getCurrentCompanyId();
        
        return $this->model
            ->where('company_id', $companyId)
            ->where('active', true)
            ->get();
    }

    /**
     * Get all telegram instances for company.
     */
    public function getAllForCompany($companyId = null)
    {
        $companyId = $companyId ?? $this->getCurrentCompanyId();
        
        return $this->model
            ->where('company_id', $companyId)
            ->where('active', true)
            ->get();
    }
}

