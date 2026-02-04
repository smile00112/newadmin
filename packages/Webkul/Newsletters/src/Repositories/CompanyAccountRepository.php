<?php

namespace Webkul\Newsletters\Repositories;

use Webkul\Core\Eloquent\Repository;

class CompanyAccountRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'Webkul\Newsletters\Models\CompanyAccount';
    }

    /**
     * Get or create account for company.
     *
     * @param  int  $companyId
     * @return \Webkul\Newsletters\Models\CompanyAccount
     */
    public function getOrCreateForCompany(int $companyId)
    {
        $account = $this->findWhere(['company_id' => $companyId])->first();

        if (!$account) {
            $account = $this->create([
                'company_id' => $companyId,
                'balance' => 0,
            ]);
        }

        return $account;
    }

    /**
     * Add balance to account.
     *
     * @param  int  $accountId
     * @param  float  $amount
     * @return \Webkul\Newsletters\Models\CompanyAccount
     */
    public function addBalance(int $accountId, float $amount)
    {
        $account = $this->findOrFail($accountId);
        
        $account->balance += $amount;
        $account->save();

        return $account;
    }

    /**
     * Deduct balance from account.
     *
     * @param  int  $accountId
     * @param  float  $amount
     * @return \Webkul\Newsletters\Models\CompanyAccount
     */
    public function deductBalance(int $accountId, float $amount)
    {
        $account = $this->findOrFail($accountId);
        
        $account->balance -= $amount;
        $account->save();

        return $account;
    }
}

