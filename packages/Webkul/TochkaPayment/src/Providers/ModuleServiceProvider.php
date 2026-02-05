<?php

namespace Webkul\TochkaPayment\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \Webkul\TochkaPayment\Models\TochkaPaymentHistory::class,
    ];
}
