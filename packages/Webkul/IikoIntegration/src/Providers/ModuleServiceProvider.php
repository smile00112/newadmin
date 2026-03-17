<?php

namespace Webkul\IikoIntegration\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models to register.
     *
     * @var array
     */
    protected $models = [];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->register(IikoIntegrationServiceProvider::class);
    }
}

