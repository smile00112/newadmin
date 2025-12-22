<?php

namespace Webkul\MobileApp\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models to register.
     */
    protected $models = [
        \Webkul\MobileApp\Models\MobileAppSetting::class,
    ];
}
