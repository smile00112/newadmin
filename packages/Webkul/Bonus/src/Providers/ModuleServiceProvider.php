<?php

namespace Webkul\Bonus\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models to register.
     *
     * @var array
     */
    protected $models = [
        \Webkul\Bonus\Models\BonusLevel::class,
        \Webkul\Bonus\Models\CustomerBonus::class,
        \Webkul\Bonus\Models\BonusTransaction::class,
        \Webkul\Bonus\Models\BonusSetting::class,
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->register(BonusServiceProvider::class);
    }
}
