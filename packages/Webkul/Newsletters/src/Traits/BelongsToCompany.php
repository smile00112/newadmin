<?php

namespace Webkul\Newsletters\Traits;

trait BelongsToCompany
{
    /**
     * Get the current company ID from the authenticated admin.
     *
     * @return int|null
     */
    protected function getCurrentCompanyId(): ?int
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin) {
            return null;
        }
        
        return $admin->company_id;
    }

    /**
     * Scope a query to only include records for the current company.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCurrentCompany($query)
    {
        $companyId = $this->getCurrentCompanyId();
        
        if ($companyId === null) {
            // If no company is set, return empty result for security
            return $query->whereRaw('1 = 0');
        }
        
        return $query->where('company_id', $companyId);
    }

    /**
     * Automatically set company_id when creating a new record.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function setCompanyId($model): void
    {
        $companyId = $this->getCurrentCompanyId();
        
        if ($companyId !== null && !isset($model->company_id)) {
            $model->company_id = $companyId;
        }
    }

    /**
     * Ensure the model belongs to the current company.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function belongsToCurrentCompany($model): bool
    {
        $companyId = $this->getCurrentCompanyId();
        
        if ($companyId === null) {
            return false;
        }
        
        return $model->company_id === $companyId;
    }
}

