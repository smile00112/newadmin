<?php

namespace Webkul\TochkaPayment\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\TochkaPayment\Models\TochkaPaymentHistoryProxy;

class TochkaPaymentHistoryRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return string
     */
    public function model(): string
    {
        return TochkaPaymentHistoryProxy::modelClass();
    }
}
