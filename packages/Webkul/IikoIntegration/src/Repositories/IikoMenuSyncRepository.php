<?php

namespace Webkul\IikoIntegration\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;
use Webkul\IikoIntegration\Models\IikoMenuSync;

class IikoMenuSyncRepository extends Repository
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return IikoMenuSync::class;
    }
}
