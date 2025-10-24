<?php

namespace Webkul\Product\Repositories;

use Webkul\Core\Eloquent\Repository;

class ProductConstructorGroupTemplateRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    function model(): string
    {
        return 'Webkul\Product\Contracts\ProductConstructorGroupTemplate';
    }
}

