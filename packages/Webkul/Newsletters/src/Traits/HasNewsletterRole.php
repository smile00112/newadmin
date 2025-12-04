<?php

namespace Webkul\Newsletters\Traits;

trait HasNewsletterRole
{
    /**
     * Check if admin is company owner.
     *
     * @return bool
     */
    public function isCompanyOwner(): bool
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || !$admin->company_id) {
            return false;
        }
        
        // Проверка: владелец имеет permission_type 'all' для newsletters
        // или имеет специальное разрешение 'newsletters.companies.manage'
        if ($admin->role->permission_type === 'all') {
            return true;
        }
        
        return $admin->hasPermission('newsletters.companies.manage')
            || $admin->hasPermission('newsletters.managers.create');
    }

    /**
     * Check if admin can manage managers in their company.
     *
     * @return bool
     */
    public function canManageManagers(): bool
    {
        return $this->isCompanyOwner();
    }

    /**
     * Check if admin has permission for newsletters action.
     *
     * @param  string  $permission
     * @return bool
     */
    public function hasNewsletterPermission(string $permission): bool
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin) {
            return false;
        }
        
        // Владелец имеет все права для своей компании
        if ($this->isCompanyOwner()) {
            return true;
        }
        
        // Проверка конкретного разрешения
        if ($admin->role->permission_type === 'all') {
            return true;
        }
        
        return $admin->hasPermission($permission);
    }

    /**
     * Ensure admin has newsletter permission.
     *
     * @param  string  $permission
     * @return void
     */
    public function requireNewsletterPermission(string $permission): void
    {
        if (!$this->hasNewsletterPermission($permission)) {
            abort(403, trans('newsletters::app.admin.errors.permission-denied', [
                'permission' => $permission
            ]));
        }
    }

    /**
     * Get current company ID from authenticated admin.
     *
     * @return int|null
     */
    protected function getCurrentCompanyId(): ?int
    {
        $admin = auth()->guard('admin')->user();
        
        return $admin ? $admin->company_id : null;
    }

    /**
     * Ensure admin belongs to the same company as the resource.
     *
     * @param  int|null  $resourceCompanyId
     * @return void
     */
    protected function ensureSameCompany(?int $resourceCompanyId): void
    {
        $adminCompanyId = $this->getCurrentCompanyId();
        
        if ($adminCompanyId === null) {
            abort(403, trans('newsletters::app.admin.errors.no-company-assigned'));
        }
        
        if ($resourceCompanyId !== null && $resourceCompanyId !== $adminCompanyId) {
            abort(403, trans('newsletters::app.admin.errors.different-company'));
        }
    }
}

