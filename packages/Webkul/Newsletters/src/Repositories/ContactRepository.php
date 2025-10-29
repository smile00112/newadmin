<?php

namespace Webkul\Newsletters\Repositories;

use Webkul\Core\Eloquent\Repository;

class ContactRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return 'Webkul\Newsletters\Models\NewslettersContact';
    }
}
