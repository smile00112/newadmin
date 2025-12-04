<?php

namespace Webkul\Newsletters\Observers;

use Webkul\Newsletters\Models\Company;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;

class CompanyObserver
{
    /**
     * Create a new observer instance.
     */
    public function __construct(
        protected CompanyAccountRepository $accountRepository
    ) {}

    /**
     * Handle the Company "created" event.
     */
    public function created(Company $company): void
    {
        // Automatically create account for new company
        $this->accountRepository->getOrCreateForCompany($company->id);
    }
}

